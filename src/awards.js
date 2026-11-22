const nextRound = db => first(db,'SELECT * FROM voting_rounds WHERE closed_at IS NULL');
const activeRound = db => first(db,'SELECT * FROM voting_rounds WHERE closed_at IS NULL AND opened_at<=unixepoch()');
const roundIsOpen = round => Boolean(round && round.opened_at<=Math.floor(Date.now()/1000));
const roundISO = round => new Date(round.opened_at*1000).toISOString();
const roundDate = round => new Intl.DateTimeFormat('en-KE',{dateStyle:'long',timeZone:'Africa/Nairobi'}).format(new Date(round.opened_at*1000));
const demoWinners = [
    ['Amina Mwangi · Mwangaza Ventures','KE'], ['Jirani Learning Trust','UG'], ['Orbit Foundry','NG'],
    ['Kifaru Design Collective','TZ'], ['Baobab Press','GH'], ['Ochre House Studio','SN'], ['Marula Gatherings','ZA'],
    ['Tawi Harvest','RW'], ['Imara Ledger','KE'], ['Afya Grove','ET'], ['Market Canopy','CI'], ['Kijani Loop','ZM']
];
const regionNames=new Intl.DisplayNames(['en'],{type:'region',fallback:'none'});
const countryOptions=Object.keys(assets.flags).map(code=>[code,regionNames.of(code)||code])
    .sort((a,b)=>a[1].localeCompare(b[1]));
const regionCodes=new Map(countryOptions.map(([code,name])=>[name.toLowerCase(),code]));
regionCodes.set('ivory coast','CI');
regionCodes.set('dr congo','CD');
regionCodes.set('democratic republic of the congo','CD');
regionCodes.set('tanzania','TZ');

function winnerCountry(value) {
    if (!value) return '';
    const country=String(value).trim();
    const code=Object.hasOwn(assets.flags,country.toUpperCase()) ? country.toUpperCase() : regionCodes.get(country.toLowerCase());
    const name=code ? regionNames.of(code) : country;
    return `<span class="winner-country">${code ? `<img src="/flag/${code}.webp" width="24" height="18" alt="">` : ''}${h(name)}</span>`;
}
function winnerImage(winner,size) {
    return `<img class="winner-portrait" src="${winner.image}" width="${size}" height="${size}" alt="" loading="lazy">`;
}

async function standings(db,round) {
    if (roundIsOpen(round)) {
        const leaders = await rows(db,`WITH totals AS (
            SELECT n.id,n.category,n.company,n.country,count(v.id) AS votes FROM votes v
            JOIN nominations n ON n.id=v.nominee AND n.status='approved'
            WHERE v.cast_at >= unixepoch()-86400 AND v.round_id=? GROUP BY n.id
        ), places AS (SELECT *,row_number() OVER (PARTITION BY category ORDER BY votes DESC,company,id) AS place FROM totals)
        SELECT * FROM places WHERE place<=3 ORDER BY category,place`,round.id);
        return { round, entries: leaders };
    }
    const previous = await rows(db,`WITH ranked AS (
        SELECT *, row_number() OVER (PARTITION BY category ORDER BY awarded_at DESC,id DESC) AS place
        FROM award_winners
    ) SELECT * FROM ranked WHERE place=1`);
    return {round:null,entries:previous};
}
function awardsGrid({round,entries}) {
    if (!round) {
        const winners=categories.map((category,index)=>{
            const official=entries.find(entry=>entry.category===category);
            if (official) return {category,name:official.company,edition:official.edition,country:official.country,
                image:official.image ? '/media/winner/'+official.id : '/logo.png',demo:false};
            return {category,name:demoWinners[index][0],country:demoWinners[index][1],
                edition:'Winner',image:'/demo-winner/'+(index+1)+'.webp',demo:true};
        });
        const [prime,...others]=winners;
        return `${winners.some(winner=>winner.demo) ? '<p class="muted">Entries without an award edition are illustrative.</p>' : ''}
            <div class="winners">
                <article class="winner-feature card">
                    <p class="eyebrow">Prime award</p><h3>${h(prime.category)}</h3>
                    <div class="winner-person winner-person-feature">${winnerImage(prime,112)}<div>
                    <p class="winner-name">${h(prime.name)}</p>${winnerCountry(prime.country)}
                    <span class="winner-label ${prime.demo ? 'demo-label' : ''}">${h(prime.edition)}</span></div></div>
                </article>
                <div class="winner-list card"><h3>Category winners</h3>
                    <dl>${others.map(winner=>`<div><dt>${h(winner.category)}</dt>
                    <dd><div class="winner-person">${winnerImage(winner,56)}<div><strong>${h(winner.name)}</strong>
                    ${winnerCountry(winner.country)}</div></div><span class="winner-label ${winner.demo ? 'demo-label' : ''}">${h(winner.edition)}</span></dd></div>`).join('')}</dl>
                </div>
            </div>`;
    }
    return `<div class="award-grid">${categories.map((category,index)=>{
        const items=entries.filter(entry=>entry.category===category);
        return `<article class="card award-result ${index===0 ? 'prime-award' : ''}">
            ${index===0 ? '<p class="eyebrow">Prime award</p>' : ''}
            <h3>${h(category)}</h3>
            ${items.length ? `<ol>${items.map(item=>`<li><span>${h(item.company)}</span>
                <strong>${item.votes} ${item.votes===1 ? 'vote' : 'votes'}</strong></li>`).join('')}</ol>` :
                '<p class="muted">No votes in the last 24 hours.</p>'}
            </article>`;
    }).join('')}</div>`;
}
function countdownParts(round) {
    const seconds=Math.max(0,Math.ceil((round.opened_at*1000-Date.now())/1000));
    return [Math.floor(seconds/86400),Math.floor(seconds/3600)%24,Math.floor(seconds/60)%60,seconds%60];
}
function countdownPanel(round,headingTag='h2') {
    const parts=countdownParts(round);
    return `<section class="countdown-panel" data-countdown-panel>
        <div class="countdown-intro"><p class="eyebrow">${h(round.title)}</p>
        <${headingTag}>Voting opens <time datetime="${roundISO(round)}">${roundDate(round)}</time></${headingTag}>
        <p>Midnight East Africa Time</p></div>
        <div class="countdown-clock" data-start="${roundISO(round)}" role="group" aria-label="Time until voting opens">
            ${['Days','Hours','Minutes','Seconds'].map((unit,index)=>`<div class="countdown-cell">
                <span data-countdown-digit>${String(parts[index]).padStart(2,'0')}</span><span>${unit}</span>
            </div>`).join('')}
        </div><p data-countdown-ready hidden>${link('/ballot','Vote now','button')}</p>
    </section>`;
}
function votingCountdown(round) {
    return `<section class="voting-schedule">${heading('Voting schedule',round ? '' : 'A date for the next round has not been announced.')}
        ${round ? countdownPanel(round) : ''}<div class="actions section">
        ${link('/nominate','Nominate a business','button')} ${link('/#categories','View previous winners','button secondary')}</div>
    </section>`;
}
async function closeRound(db,id) {
    const updates=await db.batch([
        statement(db,`UPDATE voting_rounds SET closed_at=unixepoch() WHERE id=? AND closed_at IS NULL AND opened_at<=unixepoch()
            AND NOT EXISTS (SELECT 1 FROM orders WHERE kind='ballot' AND status IN ('starting','pending','unknown'))`,id),
        statement(db,`INSERT INTO judging_entries(round_id,nominee,category,company,votes)
            SELECT ?,n.id,n.category,CASE WHEN n.category='Founder of the Year'
                THEN coalesce(nullif(json_extract(n.details,'$.Name'),''),n.company) ELSE n.company END,count(v.id)
            FROM nominations n LEFT JOIN votes v ON v.nominee=n.id AND v.round_id=?
            WHERE n.status='approved' AND changes()>0 GROUP BY n.id`,id,id)
    ]);
    return updates[0].meta.changes>0;
}

const rankedResults = `WITH points AS (
    SELECT *, coalesce(70.0*votes/nullif(max(votes) OVER (PARTITION BY category),0),0) AS public_points
    FROM judging_entries WHERE round_id=?
), totals AS (
    SELECT *,round(public_points+0.30*panel_score,4) AS total FROM points
), ranking AS (
    SELECT *,rank() OVER (PARTITION BY category ORDER BY total DESC,panel_score DESC) AS place,
        count(*) OVER (PARTITION BY category,total,panel_score) AS tied FROM totals
)`;

async function publishResults(db,id) {
    const result=await db.batch([
        statement(db,rankedResults+`UPDATE voting_rounds SET results_published_at=unixepoch()
            WHERE id=? AND closed_at IS NOT NULL AND results_published_at IS NULL
            AND EXISTS (SELECT 1 FROM ranking)
            AND NOT EXISTS (SELECT 1 FROM ranking WHERE panel_score IS NULL OR (place<=3 AND tied>1))`,id,id),
        statement(db,rankedResults+`INSERT INTO award_results(round_id,category,place,company,votes,public_points,panel_score,total)
            SELECT round_id,category,place,company,votes,round(public_points,4),panel_score,total FROM ranking
            WHERE place<=3 AND changes()>0`,id),
        statement(db,rankedResults+`INSERT INTO award_winners(category,company,edition,awarded_at,nominee,votes,country,image)
            SELECT a.category,a.company,r.title,r.results_published_at,a.nominee,a.votes,n.country,n.photo
            FROM ranking a JOIN voting_rounds r ON r.id=a.round_id JOIN nominations n ON n.id=a.nominee
            WHERE a.place=1 AND changes()>0 ON CONFLICT(category,edition) DO UPDATE SET
                company=excluded.company,awarded_at=excluded.awarded_at,nominee=excluded.nominee,
                votes=excluded.votes,country=excluded.country,image=excluded.image`,id)
    ]);
    return result[0].meta.changes>0;
}
async function judgingRounds(db) {
    const entries=await rows(db,`SELECT j.*,r.title FROM judging_entries j JOIN voting_rounds r ON r.id=j.round_id
        WHERE r.closed_at IS NOT NULL AND r.results_published_at IS NULL ORDER BY r.id DESC,j.category,j.company`);
    return [...new Set(entries.map(entry=>entry.round_id))].map(id=>entries.filter(entry=>entry.round_id===id));
}
function judgingPanel(session,groups) {
    if (!groups.length) return '';
    return `<section class="section"><h2>Panel judging</h2>
        <p>Enter the panel’s agreed score out of 100 for every finalist. Publish after scores are complete and any ties for a medal place are resolved.</p>
        ${groups.map(entries=>`<article class="card form-card"><h3>${h(entries[0].title)}</h3>
        <div class="judging-grid">${entries.map(entry=>`<div>
        <p class="eyebrow">${h(entry.category)}</p><strong>${h(entry.company)}</strong><p>${entry.votes} public votes</p>
        ${form('/admin/awards/score',session.csrf,`<input type="hidden" name="round" value="${entry.round_id}">
        <input type="hidden" name="nominee" value="${entry.nominee}">
        ${field('panel-'+entry.round_id+'-'+entry.nominee,'Panel score',`required min="0" max="100" value="${entry.panel_score ?? ''}"`,'number')}
        ${button('Save score')}`)}</div>`).join('')}</div>
        ${form('/admin/awards/publish',session.csrf,`<input type="hidden" name="round" value="${entries[0].round_id}">`+
            button('Publish winners and runners-up'))}</article>`).join('')}</section>`;
}
function emailConfig(env) {
    if (!env.SBA_RESEND_API_KEY || !env.SBA_VERIFICATION_FROM) return null;
    return {key:env.SBA_RESEND_API_KEY,from:env.SBA_VERIFICATION_FROM,origin:'https://sba.tsols.co.ke'};
}
const hashToken = async value => hex(await crypto.subtle.digest('SHA-256',encode.encode(value)));
async function sendVerification(db,env,account) {
    const cfg=emailConfig(env);
    if (!cfg) return 'unavailable';
    const code=random(), hashed=await hashToken(code);
    const reserved=await write(db,`INSERT INTO email_verifications(voter,token_hash,expires,attempted)
        VALUES(?,?,unixepoch()+1800,unixepoch())
        ON CONFLICT(voter) DO UPDATE SET token_hash=excluded.token_hash,expires=excluded.expires,attempted=excluded.attempted
        WHERE email_verifications.attempted<=unixepoch()-120`,account.id,hashed);
    if (!reserved.meta.changes) return 'recent';
    try {
        const link=cfg.origin+'/verify-email?token='+code;
        const response=await fetch('https://api.resend.com/emails',{method:'POST',signal:AbortSignal.timeout(10000),
            headers:{Authorization:'Bearer '+cfg.key,'Content-Type':'application/json'},
            body:JSON.stringify({from:cfg.from,to:[account.email],subject:'Verify your SBA Awards account',
                text:'Open this link to verify your SBA Awards account. It expires in 30 minutes.\n\n'+link+
                    '\n\nIf you did not create this account, you can ignore this email.'})});
        if (response.ok) return 'sent';
    } catch {}
    await write(db,'DELETE FROM email_verifications WHERE voter=? AND token_hash=?',account.id,hashed);
    return 'unavailable';
}
async function confirmEmail(db,value,password) {
    if (typeof value !== 'string' || !/^[a-f0-9]{64}$/.test(value) ||
        typeof password !== 'string' || !password || password.length>256) return false;
    const hash=await hashToken(value);
    const account=await first(db,`SELECT v.password FROM email_verifications e JOIN voters v ON v.id=e.voter
        WHERE e.token_hash=? AND e.expires>unixepoch() AND v.email_verified IS NULL`,hash);
    if (!account || !equal(await passwordHash(password,account.password.split(':')[0]),account.password)) return false;
    const result=await db.batch([
        statement(db,`UPDATE voters SET email_verified=unixepoch() WHERE email_verified IS NULL AND id=(
            SELECT voter FROM email_verifications WHERE token_hash=? AND expires>unixepoch())`,hash),
        statement(db,'DELETE FROM email_verifications WHERE token_hash=? AND changes()>0',hash)
    ]);
    return result[0].meta.changes>0;
}
function emailHelp(session,state) {
    const status=state==='unavailable' ? 'Verification email is temporarily unavailable. Your account stays inactive until verified.' :
        state==='recent' ? 'A verification message was requested recently. Check your inbox or try again in two minutes.' :
        'Check your inbox for the verification link. If the account is eligible, you can request a new one below.';
    return `<section class="narrow">${heading('Verify your email',status)}<div class="card form-card">
        ${form('/verify-email/resend',session.csrf,field('email','Email address','required maxlength="254" autocomplete="email"','email')+
            button('Send verification email'))}</div></section>`;
}
function emailConfirmation(session,token) {
    return `<section class="narrow">${heading('Confirm your email address','Enter your account password to activate your SBA Awards account.')}
        <div class="card form-card">${form('/verify-email',session.csrf,
            `<input type="hidden" name="token" value="${h(token)}">`+
            field('password','Account password','required maxlength="256" autocomplete="current-password"','password')+
            button('Verify email'))}</div></section>`;
}
function awardsAdmin(session,round,pairs,message='') {
    const categoryOptions=categories.map(category=>`<option>${h(category)}</option>`).join('');
    return heading('Voting and nominee profiles')+link('/admin','Awards administration','back-link')+flash(message)+
        `<div class="cards"><section class="card form-card"><h2>Voting round</h2>
        ${round && !roundIsOpen(round) ? `<p><strong>${h(round.title)}</strong> opens ${roundDate(round)}.</p>` :
        round ? `<p>Voting open for <strong>${h(round.title)}</strong>.</p>
        ${form('/admin/awards/close',session.csrf,`<input type="hidden" name="id" value="${round.id}">`+
            button('Close voting and send to judging'))}<small>Unresolved ballot payments must settle before closing.</small>` :
        form('/admin/awards/open',session.csrf,field('title','Award edition','required maxlength="100"')+button('Open voting'))}</section>
        <section class="card form-card"><h2>Record a previous winner</h2>
        ${form('/admin/awards/winner',session.csrf,`<label for="category">Category</label><select id="category" name="category">${categoryOptions}</select>`+
            field('company','Winner name','required maxlength="200"')+field('edition','Award edition','required maxlength="100"')+
            field('date','Award date','required','date')+
            `<label for="country">Country</label><select id="country" name="country" required><option value="">Choose a country</option>
                ${countryOptions.map(([code,name])=>`<option value="${code}">${h(name)}</option>`).join('')}</select>`+
            field('image','Winner photo','accept="image/jpeg,image/png,image/webp,image/gif"','file')+button('Save winner'),true)}</section></div>
        <section class="section"><h2>Nominee pairing requests</h2>
        ${pairs.length ? pairs.map(pair=>`<article class="card admin-item">
            <h3>${h(pair.company)}</h3><p>${h(pair.username)} · ${h(pair.email)}</p>
            ${form('/admin/awards/pair',session.csrf,`<input type="hidden" name="voter" value="${pair.voter}">
                <input type="hidden" name="nomination" value="${pair.nomination}">
                <button name="decision" value="approved">Approve pairing</button>
                <button name="decision" value="rejected" class="secondary">Decline</button>`)}</article>`).join('')
            : '<p class="empty">No verified-email matches are waiting for review.</p>'}</section>`;
}
async function pairedEntries(db,voter) {
    return rows(db,`SELECT n.id,n.company,n.category,n.country,n.status,p.status AS pairing,
        CASE WHEN p.status='approved' THEN (SELECT count(*) FROM votes WHERE nominee=n.id) END AS received
        FROM nominations n LEFT JOIN nominee_pairings p ON p.nomination=n.id AND p.voter=?
        WHERE n.submitted_by=? OR p.voter=? ORDER BY n.id DESC`,voter,voter,voter);
}
async function replaceNomineePhoto(db,bucket,voter,values) {
    const nominee=Number(values.nomination);
    if (!Number.isSafeInteger(nominee)||nominee<1) throw new InvalidForm('Choose a nominee profile.');
    const linked=await first(db,`SELECT 1 FROM nominee_pairings WHERE nomination=? AND voter=? AND status='approved'`,nominee,voter);
    if (!linked) throw new InvalidForm('This profile is not linked to your account.');
    const type=await attachment(values.Image);
    const key='photos/'+random();
    await bucket.put(key,await values.Image.arrayBuffer(),{httpMetadata:{contentType:type}});
    try {
        const change=await write(db,`UPDATE nominations SET photo=? WHERE id=? AND EXISTS
            (SELECT 1 FROM nominee_pairings WHERE nomination=? AND voter=? AND status='approved')`,key,nominee,nominee,voter);
        if (!change.meta.changes) throw new InvalidForm('This profile is not linked to your account.');
    } catch (error) { await bucket.delete(key); throw error; }
}
