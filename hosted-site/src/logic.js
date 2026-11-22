export const categories = [
    'Founder of the Year', 'NGO', 'Tech', 'Creative Economy', 'Media and Press', 'Music & Arts', 'Events',
    'Agribusiness', 'FinTech', 'Health & Wellness', 'Retail & E-commerce', 'Sustainable Business'
];
export const votePrice = 10;
const nairobiDate = new Intl.DateTimeFormat('sv-SE', { timeZone: 'Africa/Nairobi' });
const dayNow = () => nairobiDate.format(new Date());
const random = () => [...crypto.getRandomValues(new Uint8Array(32))]
    .map(byte => byte.toString(16).padStart(2, '0')).join('');
const encode = new TextEncoder();
const hex = bytes => [...new Uint8Array(bytes)].map(byte => byte.toString(16).padStart(2, '0')).join('');
async function passwordHash(password, salt = random()) {
    const key = await crypto.subtle.importKey('raw', encode.encode(password), 'PBKDF2', false, ['deriveBits']);
    const bits = await crypto.subtle.deriveBits({ name: 'PBKDF2', hash: 'SHA-256',
        iterations: 100000, salt: encode.encode(salt) }, key, 256);
    return salt + ':' + hex(bits);
}
function equal(a, b) {
    if (a.length !== b.length) return false;
    let difference = 0;
    for (let i = 0; i < a.length; i++) difference |= a.charCodeAt(i) ^ b.charCodeAt(i);
    return difference === 0;
}
const statement = (db, sql, ...args) => db.prepare(sql).bind(...args);
const first = (db, sql, ...args) => statement(db, sql, ...args).first();
const write = (db, sql, ...args) => statement(db, sql, ...args).run();
const rows = async (db, sql, ...args) => (await statement(db, sql, ...args).all()).results;
const nominees = db => rows(db, "SELECT id,category,company,country,details FROM nominations WHERE status='approved' ORDER BY company");
async function creditsFor(db, voter, day = dayNow()) {
    const result = await first(db, `SELECT credits, NOT EXISTS (
        SELECT 1 FROM daily_vote_claims WHERE voter=voters.id AND day=?) AS free FROM voters WHERE id=?`, day, voter);
    return { free: result.free, paid: result.credits };
}
export function voteQuote(count, credits) {
    const free = Math.min(count, credits.free);
    const paid = Math.min(count - free, credits.paid);
    const checkout = count - free - paid;
    return { free, paid, checkout, total: checkout * votePrice };
}
async function selectPicks(db, values) {
    const found = new Map((await nominees(db)).map(row => [String(row.id), row]));
    const picks = [];
    for (const category of categories) {
        if (!values[category]) continue;
        const pick = found.get(String(values[category]));
        if (pick?.category !== category) return [];
        picks.push(pick);
    }
    return picks;
}
class InvalidForm extends Error {}
function input(values, name, max, required = true, trim = true) {
    const raw = values[name];
    const value = typeof raw === 'string' ? (trim ? raw.trim() : raw) : '';
    if ((required && !value) || value.length > max) throw new InvalidForm(`Check the ${name} field and try again.`);
    return value;
}
function email(values, name) {
    const value = input(values, name, 254);
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) throw new InvalidForm('Enter a valid email address.');
    return value;
}
async function attachment(file, proof = false) {
    if (!(file instanceof File) || file.size < 12 || file.size > 5 * 1024 * 1024) {
        throw new InvalidForm('Choose both required files. Each file can be up to 5 MB.');
    }
    const bytes = new Uint8Array(await file.slice(0,12).arrayBuffer());
    const ascii = (offset, end) => String.fromCharCode(...bytes.slice(offset,end));
    let type;
    if (bytes[0] === 255 && bytes[1] === 216 && bytes[2] === 255) type = 'image/jpeg';
    else if (hex(bytes).startsWith('89504e470d0a1a0a')) type = 'image/png';
    else if (['GIF87a','GIF89a'].includes(ascii(0,6))) type = 'image/gif';
    else if (ascii(0,4) === 'RIFF' && ascii(8,12) === 'WEBP') type = 'image/webp';
    else if (proof && ascii(0,5) === '%PDF-') type = 'application/pdf';
    if (!type) throw new InvalidForm('Upload a JPEG, PNG, GIF, or WebP image. Proof can also be a PDF.');
    return type;
}
async function saveNomination(db, bucket, values, voter) {
    const details = {
        Name: input(values, 'Name', 200), Email: email(values, 'Email'),
        CompanyDescription: input(values, 'CompanyDescription', 2000),
        BusinessStartedYear: input(values, 'BusinessStartedYear', 4), Employees: input(values, 'Employees', 8)
    };
    const category = input(values, 'Category', 30);
    const country = input(values, 'Country', 100);
    const company = input(values, 'CompanyName', 200);
    if (!categories.includes(category) || !/^\d{4}$/.test(details.BusinessStartedYear) ||
        +details.BusinessStartedYear < 1900 || +details.BusinessStartedYear > 2100 ||
        !/^\d+$/.test(details.Employees) || +details.Employees < 1 || +details.Employees > 10000000) {
        throw new InvalidForm('Check the category, founding year, and number of employees.');
    }
    for (const name of ['Facebook', 'Twitter', 'Instagram', 'Website']) {
        details[name] = input(values, name, 1000, false);
        if (details[name] && !/^https?:\/\//i.test(details[name])) throw new InvalidForm(`Enter an http or https URL for ${name}.`);
    }
    const photoType = await attachment(values.Image);
    const proofType = await attachment(values.OwnershipProof, true);
    const photo = 'photos/' + random();
    const proof = 'proofs/' + random();
    try {
        await bucket.put(photo, await values.Image.arrayBuffer(), { httpMetadata: { contentType: photoType } });
        await bucket.put(proof, await values.OwnershipProof.arrayBuffer(), { httpMetadata: { contentType: proofType } });
        await write(db, 'INSERT INTO nominations(category,company,country,details,photo,proof,submitted_by) VALUES(?,?,?,?,?,?,?)',
            category,company,country,JSON.stringify(details),photo,proof,voter);
    } catch (error) {
        await Promise.allSettled([bucket.delete(photo),bucket.delete(proof)]);
        throw error;
    }
}
