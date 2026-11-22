import { categories, votePrice, voteQuote } from './store.js';

export function h(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);
}

export const link = (path, label, style = '') => `<a class="${style}" href="${path}">${label}</a>`;
export const button = label => `<button type="submit">${label}</button>`;
export const heading = (title, description = '') => `<header class="page-heading"><h1>${title}</h1>
    ${description ? `<p>${description}</p>` : ''}</header>`;
export const flash = message => message ? `<p class="notice" role="alert">${h(message)}</p>` : '';
export const form = (path, csrf, body, multipart = false) => `<form method="post" action="${path}" ${multipart ? 'enctype="multipart/form-data"' : ''}>
    <input type="hidden" name="csrf" value="${h(csrf)}">${body}</form>`;

export function field(name, label, attributes = 'required', type = 'text') {
    return `<label for="${name}">${label}</label><input id="${name}" name="${name}" type="${type}" ${attributes}>`;
}

function passwordField(name,label,autocomplete,minLength) {
    const minimum=minLength ? ` minlength="${minLength}"` : '';
    return `<label for="${name}">${label}</label><div class="password-field">
        <input id="${name}" name="${name}" type="password" required maxlength="256"${minimum} autocomplete="${autocomplete}">
        <button type="button" class="password-toggle" aria-controls="${name}" aria-label="Show ${label.toLowerCase()}"
            data-password-label="${label.toLowerCase()}" hidden><span class="password-eye" aria-hidden="true"></span></button>
    </div>`;
}
export const area = (name, label, max) => `<label for="${name}">${label}</label>
    <textarea id="${name}" name="${name}" maxlength="${max}" rows="4" required></textarea>`;

function menuToggle(target) {
    return `<button class="mobile-menu-toggle" type="button" aria-controls="${target}" aria-expanded="false"
        data-menu-toggle hidden><span class="menu-icon" aria-hidden="true"></span><span>Menu</span></button>`;
}
export function layout(title, session, body, round, meta, measurement) {
    const voting = roundIsOpen(round);
    return `<!doctype html><html lang="en"><head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"><title>${h(title)} | SBA Awards</title>
    ${meta}<link rel="icon" href="/favicon.ico" sizes="any"><link rel="stylesheet" href="/styles.css">
    <script src="/navigation.js" defer></script>
    ${!voting && round ? '<script src="/countdown.js" defer></script>' : ''}</head><body>
    <a class="skip" href="#main">Skip to content</a>
    <header class="site-header"><div class="shell nav">
        <a class="brand" href="/"><img src="/logo.png" width="42" height="42" alt="">SBA Awards</a>
        ${menuToggle('site-navigation')}
        <nav id="site-navigation" aria-label="Main navigation">
            ${link('/', 'Home')} ${voting ? link('/nominees','Nominees') : link('/nominate','Nominate')} ${link('/categories', 'Categories')}
            ${link('/events', 'Events')} ${link('/journal', 'Journal')}
            ${voting ? link('/ballot','Vote','button nav-vote') : `<a class="button nav-vote nav-countdown" href="/ballot">
                ${round ? `<span>Vote opens</span> <time datetime="${roundISO(round)}" data-countdown="short">${roundDate(round)}</time>` : 'Voting closed'}</a>`}
            ${session.voter || session.admin ? link('/dashboard', 'Dashboard', 'nav-action') : link('/login', 'Sign in', 'nav-action')}
            ${session.voter || session.admin ? form('/logout',session.csrf,button('Sign out')) : ''}
        </nav>
    </div></header>
    <main class="shell" id="main">${body}</main>
    <footer class="site-footer">
        <div class="shell footer-main">
            <div class="footer-brand"><a class="brand" href="/"><img src="/logo.png" width="42" height="42" alt="">SBA Awards</a>
            <p>Startups Businesses Africa Awards</p><p>Celebrating the founders and businesses shaping Africa’s future.</p></div>
            <nav aria-label="Explore SBA Awards"><h2>Explore</h2>
                ${link('/categories','Award categories')} ${link('/events','Events and gala')} ${link('/journal','Journal')} ${link('/about','About SBA Awards')}</nav>
            <nav aria-label="Take part in SBA Awards"><h2>Take part</h2>
                ${link('/process','How the awards work')} ${link('/nominate','Submit a nomination')}
                ${link('/ballot',voting ? 'Cast your vote' : 'Voting schedule')} ${link('/contact','Contact the awards team')}
                ${session.voter ? link('/tickets','Your tickets') : ''}</nav>
        </div>
        <div class="shell footer-bottom">
            <span>© ${new Date().getFullYear()} SBA Awards</span>
            <span class="footer-credit">Designed and developed by
                <a href="https://tsols.co.ke/?utm_source=sba_awards&amp;utm_medium=referral&amp;utm_campaign=website_credit"
                    referrerpolicy="origin">Thagana Solutions</a></span>
            ${measurement ? '<button class="analytics-preferences" type="button" data-analytics-preferences hidden>Analytics preferences</button>' : ''}
        </div></footer>${trackingControls(measurement)}</body></html>`;
}

export function home(ranking, nextEvent, round) {
    return `<section class="hero">
        <div class="hero-art" aria-hidden="true">
            <img class="hero-trophy" src="/hero-trophy.webp" alt="" width="620" height="930" fetchpriority="high">
            <div class="hero-plinth"></div>
        </div>
        <script src="/hero-motion.js" defer></script>
        <div class="hero-content"><p class="eyebrow">Startups Businesses Africa Awards</p>
        <h1>For the founders<br> shaping <em>Africa.</em></h1>
        <p>Honouring the businesses turning African ambition into lasting impact.</p>
        <div class="actions">${link('/events','Explore events','button')} ${link('/nominate','Nominate a business','button secondary')}</div></div>
    </section>
    ${round && !roundIsOpen(round) ? countdownPanel(round) : ''}
    ${nextEvent ? `<section class="event-spotlight">
        <div><p class="eyebrow">Upcoming event</p><h2>${h(nextEvent.title)}</h2>
        <p>${eventDate(nextEvent.day)} · ${h(nextEvent.location)}</p></div>
        ${link('/events/' + nextEvent.id,'Event details and tickets','button')}
    </section>` : ''}
    <section class="awards-section" id="categories">
        <div class="section-heading"><div><p class="eyebrow">${ranking.round ? h(ranking.round.title) : 'The awards'}</p>
        <h2>${ranking.round ? 'Top nominees in the last 24 hours' : 'Most recent winners'}</h2></div>
        ${ranking.round ? link('/ballot','Vote in the awards','button secondary') : link('/nominate','Nominate a business','button secondary')}</div>
        ${awardsGrid(ranking)}
    </section>
    ${processPage(true)}`;
}

export function directory(rows) {
    if (!rows.length) return '<p class="empty">Approved nominees will appear here.</p>';
    return `<div class="cards">${rows.map(row => `<article class="card nominee">
        <img src="/photo/${row.id}" alt="" loading="lazy" width="72" height="72">
        <div><p class="eyebrow">${h(row.category)}</p><h3>${h(row.company)}</h3>
        <p>${h(JSON.parse(row.details).Name)} · ${h(row.country)}</p>
        ${link('/nominees/'+row.id+'/poster','Campaign poster')}</div></article>`).join('')}</div>`;
}

export function login(session, message, next = '') {
    return `<div class="split auth-layout">${heading('Sign in to SBA Awards',
        'Access your ballot, nominations, tickets, or awards administration.')}
        <section class="card form-card"><h2>Sign in</h2>${flash(message)}
        ${form('/login', session.csrf,
            field('email','Email address','required autocomplete="username" maxlength="254"','email') +
            passwordField('password','Password','current-password') +
            button('Sign in'))}
        <p><a href="/verify-email">Resend verification email</a></p><p>New to SBA Awards? <a href="/signup${next === '/nominate' ? '?role=nominee' : ''}">Create an account</a>.</p></section>
        <script src="/password-fields.js" defer></script></div>`;
}

export function signup(session, message, role = 'voter') {
    return `<section class="narrow">${heading('Create an SBA Awards account','Verify your email to activate your account.')}${flash(message)}<div class="card form-card">
        ${form('/signup',session.csrf,`<div class="pair">${['First name','Last name'].map((label,i) =>
            '<div>' + field(i ? 'last' : 'first',label,'required maxlength="150"') + '</div>').join('')}</div>` +
            `<label for="role">I’m joining as</label><select id="role" name="role">
                <option value="voter" ${role === 'voter' ? 'selected' : ''}>Voter</option>
                <option value="nominee" ${role === 'nominee' ? 'selected' : ''}>Nominee</option></select>` +
            field('username','Username','required autocomplete="username" maxlength="100"') +
            field('email','Email','required autocomplete="email" maxlength="254"','email') +
            passwordField('password','Password','new-password',8) +
            '<small>Use at least 8 characters.</small>' +
            passwordField('confirm_password','Confirm password','new-password',8) + button('Create account'))}
    </div><script src="/password-fields.js" defer></script></section>`;
}

export function nomination(session, message) {
    return `<section class="narrow wide">${heading('Nominate a business','Submit the founder and company details for review.')}
        ${flash(message)}<div class="card form-card">${form('/nominate',session.csrf,`
        <h2>Founder</h2>
        ${field('Name','Full name','required maxlength="200"')}
        ${field('Email','Email','required maxlength="254"','email')}
        <h2>Business</h2>
        <label for="Category">Category</label><select id="Category" name="Category" required>
        ${categories.map(category => `<option>${h(category)}</option>`).join('')}</select>
        ${field('Country','Country','required maxlength="100"')}
        ${field('CompanyName','Company name','required maxlength="200"')}
        ${area('CompanyDescription','Company description', 2000)}
        <div class="pair"><div>${field('BusinessStartedYear','Year founded','required min="1900" max="2100"','number')}</div>
        <div>${field('Employees','Number of employees','required min="1" max="10000000"','number')}</div></div>
        <h2>Online presence</h2>
        ${['Facebook','Twitter','Instagram','Website'].map(name => field(name, name + ' (optional)','maxlength="1000"','url')).join('')}
        <h2>Documents</h2>
        ${field('Image','Nominee photo','required accept="image/jpeg,image/png,image/webp,image/gif"','file')}
        ${field('OwnershipProof','Proof of company ownership','required accept="image/jpeg,image/png,image/webp,image/gif,application/pdf"','file')}
        <small>Upload a registration certificate, receipt, invoice, or contract. Each file can be up to 5 MB.</small>
        ${button('Submit nomination')}`,true)}</div></section>`;
}

export function ballot(session, rows, credits, message) {
    const draft = JSON.parse(session.draft || '{}');
    return heading('Your ballot','Choose up to one nominee in each category. You can leave categories blank.') +
        `<p>${credits.free} free today · ${credits.paid} purchased · ${link('/credits','Voting credits')}</p>` +
        flash(message) + (rows.length ? form('/review',session.csrf,`
        <div class="ballot">${categories.map(category => {
            const options = rows.filter(row => row.category === category);
            return `<fieldset class="card"><legend>${h(category)}</legend>${options.length ? 
                `<label class="choice"><input type="radio" name="${h(category)}" value="" ${draft[category] ? '' : 'checked'}> Skip this category</label>
                ${options.map(row => `<label class="choice"><input type="radio" name="${h(category)}" value="${row.id}" ${draft[category] === String(row.id) ? 'checked' : ''}>
                <img src="/photo/${row.id}" alt="" width="44" height="44"><span><strong>${h(row.company)}</strong><small>${h(row.country)}</small></span></label>`).join('')}`
                : '<p class="muted">No nominees in this category yet.</p>'}</fieldset>`;
        }).join('')}</div><div class="actions">${button('Review ballot')} ${link('/help','Voting help')}</div>`)
        : '<p class="empty">Voting opens once nominees are approved.</p>');
}

export function review(session, picks, credits, enabled) {
    const quote = voteQuote(picks.length, credits);
    const checkout = quote.checkout
        ? (enabled ? paymentForm(session, 'ballot', quote.total) :
            '<p class="notice">Online payment is unavailable. Edit your choices to fit your available credits.</p>')
        : form('/vote',session.csrf,`<input type="hidden" name="draft" value="${h(session.draft)}">` + button('Confirm votes'));
    return `<section class="narrow">${heading('Review your ballot','Check your choices and the credits required before submitting.')}
        <div class="card form-card"><dl>${picks.map(row => `<dt>${h(row.category)}</dt><dd>${h(row.company)}</dd>`).join('')}</dl>
        <dl><dt>Daily free credits used</dt><dd>${quote.free}</dd>
        <dt>Purchased credits used</dt><dd>${quote.paid}</dd>
        <dt>Extra votes at KES ${votePrice} each</dt><dd>${quote.checkout}</dd>
        <dt>Total due</dt><dd>KES ${quote.total}</dd></dl>
        <div class="actions">${checkout} ${link('/ballot','Edit choices')}</div></div></section>`;
}

export function creditShop(session, credits, enabled) {
    return `<section class="narrow">${heading('Voting credits','Each credit covers one vote for a nominee.')}
        <div class="card form-card"><p>Free credit available today: <strong>${credits.free}</strong></p>
        <p>Purchased credit balance: <strong>${credits.paid}</strong></p>
        <p>Your free credit resets at midnight in Nairobi. Extra votes cost KES ${votePrice} each.</p>
        ${enabled ? paymentForm(session, 'topup') : '<p class="notice">Credit purchases are currently unavailable.</p>'}
        ${link('/ballot','Back to ballot','button')}</div></section>`;
}

function paymentForm(session, kind, amount) {
    const details = kind === 'topup'
        ? field('units','Number of credits at KES ' + votePrice + ' each','required min="1" max="100" value="1"','number')
        : `<input type="hidden" name="draft" value="${h(session.draft)}"><input type="hidden" name="amount" value="${amount}">`;
    return form('/payments/start', session.csrf,
        `<input type="hidden" name="kind" value="${kind}">` + details +
        field('phone','M-PESA mobile number','required maxlength="24" autocomplete="tel" placeholder="07XX XXX XXX"','tel') +
        button('Pay with M-PESA'));
}

export function paymentStatus(session, order) {
    const paid = order.status === 'paid';
    const failed = order.status === 'failed';
    const title = paid ? 'Payment confirmed' : failed ? 'Payment unsuccessful' : 'Payment awaiting confirmation';
    const description = paid ? (order.kind === 'topup' ? 'Your credits are available.' : order.kind === 'ticket' ? 'Your tickets are confirmed.' : 'Your votes have been recorded.')
        : failed ? 'You can return and try again.'
        : order.checkout_id ? 'Approve the prompt on your phone, then check the payment status.'
        : 'We could not confirm that a prompt was sent. Contact the awards team before trying another payment.';
    return `<section class="narrow">${heading(title,description)}<div class="card form-card">
        <p>Amount: <strong>KES ${order.amount}</strong></p><p>Reference: <strong>SBA${h(order.id.slice(0,8))}</strong></p>
        ${order.status === 'pending' ? form('/payments/check',session.csrf,
            `<input type="hidden" name="id" value="${h(order.id)}">` + button('Check payment status')) : ''}
        ${order.kind === 'ticket' ? link(failed ? '/events' : '/tickets',failed ? 'Back to events' : 'View your tickets') :
        link(order.kind === 'topup' && failed ? '/credits' : '/ballot','Go to ballot')}</div></section>`;
}

function nomineeAdmin(session,entries,query,status,current,total) {
    const filterUrl=(page=1)=>'/admin/nominations?'+new URLSearchParams({q:query,status,page:String(page)});
    return adminHeading('Nominees','Review nominations, check supporting documents, and see vote totals.')+
        `<section class="admin-panel"><form class="admin-filters" action="/admin/nominations" method="get">
            <div><label for="q">Search nominations</label><input id="q" name="q" value="${h(query)}" placeholder="Business or country" maxlength="80"></div>
            <div><label for="status">Status</label><select id="status" name="status">${[
                ['all','All statuses'],['pending','Pending'],['approved','Approved'],['rejected','Rejected']
            ].map(([key,label])=>`<option value="${key}" ${key===status?'selected':''}>${label}</option>`).join('')}</select></div>
            ${button('Filter')}</form><div class="admin-panel-title"><h2>Nomination records</h2><span>${number(total)} found</span></div>
            ${entries.map(row=>{
                const details=JSON.parse(row.details);
                return `<details class="admin-nomination" id="nomination-${row.id}"><summary>
                    <strong>${h(row.company)}</strong><span>${h(row.category)} · ${h(row.country)}</span>
                    <span class="admin-status status-${row.status}">${h(row.status)}</span><span>${number(row.total)} votes</span></summary>
                    <dl>${Object.entries(details).filter(([key,value])=>value&&key!=='Dob').map(([key,value])=>
                        `<dt>${h(key)}</dt><dd>${h(value)}</dd>`).join('')}</dl>
                    <div class="actions">${link('/photo/'+row.id,'View photo')}${link('/proof/'+row.id,'Download proof')}</div>
                    ${form('/admin/nomination',session.csrf,`<input type="hidden" name="id" value="${row.id}">
                    <button name="status" value="approved">Approve</button><button class="secondary" name="status" value="rejected">Reject</button>`)}</details>`;
            }).join('')||'<p class="admin-empty">No nominations match this view.</p>'}
            ${total>25?`<nav class="admin-pages" aria-label="Nomination pages">${current>1?link(filterUrl(current-1),'Previous page'):''}
                <span>Page ${current} of ${Math.ceil(total/25)}</span>${current*25<total?link(filterUrl(current+1),'Next page'):''}</nav>`:''}</section>`;
}
function messageAdmin(messages) {
    return adminHeading('Inbox','Messages submitted through the public contact form.')+
        `<section class="admin-panel">${messages.map(row=>
        `<article class="admin-message"><strong>${h(row.name)}</strong><p>${h(row.email)}</p><p>${h(row.message)}</p></article>`
        ).join('')||'<p class="admin-empty">No messages yet.</p>'}</section>`;
}
