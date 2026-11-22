const aliases = new Map([
    ['/index.php','/login'], ['/login.html','/login'], ['/voting.php','/ballot'], ['/vote.html','/ballot'],
    ['/voting_website.html','/'], ['/about.html','/about'], ['/contact.html','/contact'],
    ['/nominate.php','/nominate'], ['/nominate.html','/nominate'], ['/signup.php','/signup'], ['/signup.html','/signup'],
    ['/help.php','/help'], ['/help2.php','/help'], ['/help3.php','/help'], ['/help4.php','/help'],
    ['/events.php','/events'], ['/blog.php','/journal'], ['/purchase_ticket.php','/events'],
    ['/buy_credits.php','/credits'], ['/profile.php','/account'], ['/thankyou.php','/thanks'], ['/admin/login','/login'], ['/admin/about.php','/admin']
]);
const confirmations = new Map([
    ['/thanks',['Thank you for voting','Your votes have been recorded.']],
    ['/nomination-received',['Nomination received','The awards team will review your submission.']],
    ['/message-received',['Message received','Your message is available to the awards team.']]
]);
const lifetime = 43200;
const cookie = (id, maxAge) => `sba=${id}; Path=/; Max-Age=${maxAge}; HttpOnly; Secure; SameSite=Lax`;
async function existingSession(db, request) {
    const id = request.headers.get('cookie')?.match(/(?:^|;\s*)sba=([a-f0-9]{64})(?:;|$)/)?.[1];
    return id ? first(db,`SELECT s.* FROM sessions s LEFT JOIN voters v ON v.id=s.voter
        WHERE s.id=? AND s.expires>unixepoch() AND (s.voter IS NULL OR v.email_verified IS NOT NULL)`,id) : null;
}
async function sessionFor(db, request) {
    return await existingSession(db, request) || newSession(db);
}
async function dashboardFor(db, session) {
    if (session.admin) return '/admin';
    if (!session.voter) return '/login';
    const account = await first(db,'SELECT role FROM voters WHERE id=?',session.voter);
    return account?.role === 'nominee' ? '/nominee/dashboard' : '/account';
}
async function newSession(db, voter = null, admin = 0, prior = null) {
    const session = { id: random(), csrf: random(), voter, admin, fresh: true };
    const insert = statement(db,'INSERT INTO sessions (id,csrf,expires,voter,admin) VALUES (?,?,unixepoch()+?,?,?)',
        session.id,session.csrf,lifetime,voter,admin);
    await db.batch([insert, statement(db,'DELETE FROM sessions WHERE expires<=unixepoch() OR id=?',prior)]);
    return session;
}
async function limitedBody(request, maximum) {
    const reader = request.body?.getReader();
    if (!reader) return new Uint8Array();
    const chunks = [];
    let size = 0;
    while (true) {
        const chunk = await reader.read();
        if (chunk.done) break;
        size += chunk.value.byteLength;
        if (size > maximum) {
            await reader.cancel();
            throw new InvalidForm('The submission is too large.');
        }
        chunks.push(chunk.value);
    }
    const bytes = new Uint8Array(size);
    let offset = 0;
    for (const chunk of chunks) {
        bytes.set(chunk,offset);
        offset += chunk.byteLength;
    }
    return bytes;
}
async function readForm(request, isNomination) {
    const format = request.headers.get('content-type') || '';
    if (!(isNomination ? format.startsWith('multipart/form-data;') : format.startsWith('application/x-www-form-urlencoded'))) {
        throw new InvalidForm('Unsupported form format.');
    }
    const bytes = await limitedBody(request,isNomination ? 11 * 1024 * 1024 : 32768);
    try {
        return Object.fromEntries(await new Response(bytes,{headers:{'Content-Type':format}}).formData());
    } catch { throw new InvalidForm('The submission could not be read.'); }
}
function contact(session, message) {
    return `<section class="narrow">${heading('Contact SBA Awards','Send a question to the awards team.')}
        ${flash(message)}<div class="card form-card">${form('/contact',session.csrf,
            field('name','Name','required maxlength="150"') + field('email','Email','required maxlength="254"','email') +
            area('message','Message',3000) + button('Send message'))}</div></section>`;
}

export default {
    async fetch(request, env, ctx) {
        const path = new URL(request.url).pathname;
        const get = request.method === 'GET';
        const post = request.method === 'POST';
        const db = env.DB;
        const config = paymentConfig(env);
        const adminEmail = (env.SBA_ADMIN_EMAIL || 'thaganakenya@gmail.com').trim().toLowerCase();
        let session;
        let votingRound;
        let signupRole;
        let sessionCookie;
        let analyticsCookie;
        let analyticsEnabled=false;
        function response(body, status, headers) {
            const output = new Headers(headers);
            const googleScript = analyticsEnabled ? ' https://www.googletagmanager.com' : '';
            const googleEndpoints = analyticsEnabled ? ' https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com' : '';
            output.set('Content-Security-Policy',`default-src 'none'; style-src 'self'; script-src 'self'${googleScript};
                img-src 'self'${googleEndpoints}; connect-src 'self'${googleEndpoints}; form-action 'self'; frame-ancestors 'none'; base-uri 'none'`.replace(/\s+/g,' '));
            output.set('X-Content-Type-Options','nosniff');
            output.set('Referrer-Policy','same-origin');
            output.set('Cache-Control','no-store');
            if (sessionCookie) output.set('Set-Cookie',sessionCookie);
            else if (session?.fresh) output.set('Set-Cookie',cookie(session.id,lifetime));
            if (analyticsCookie) output.append('Set-Cookie',analyticsCookie);
            return new Response(body,{status,headers:output});
        }
        const page = (title, body, status=200) => {
            const adminRoute=path==='/admin'||path.startsWith('/admin/');
            if (get && status===200 && !session?.admin && ctx?.waitUntil && shouldMeasure(request,path)) {
                const {day,browser,fresh}=audienceKey(request);
                if (fresh) analyticsCookie=audienceCookie(day,browser);
                ctx.waitUntil(recordAudience(db,path,day,browser,fresh).catch(()=>console.warn('Audience count could not be recorded')));
            }
            const measurement = get && !session?.admin && publicMetadata(path,title,status).index ? googleMeasurement(env) : '';
            analyticsEnabled=Boolean(measurement);
            const html=adminRoute && session?.admin ? adminLayout(title,body,session,path,votingRound) :
                layout(title,session || {},body,votingRound,publicMeta(path,title,status)+
                    (path==='/' && status===200 ? verificationMeta(env) : ''),measurement);
            return response(html,status,{'Content-Type':'text/html; charset=utf-8'});
        };
        const redirect = location => response(null,303,{Location:location});
        const missing = () => page('Page unavailable',heading('Page unavailable') + link('/','Back to home'),404);
        const orderPath = order => '/payments/' + order.id;
        try {
            if (get && path === '/styles.css') return response(assets.css,200,{'Content-Type':'text/css; charset=utf-8'});
            if (get && path === '/hero-trophy.webp') return response(Uint8Array.from(atob(assets.heroTrophy),c=>c.charCodeAt(0)),200,{'Content-Type':'image/webp'});
            if (get && path === '/analytics-consent.js') return response(assets.analyticsConsent,200,{'Content-Type':'text/javascript; charset=utf-8'});
            if (get && path === '/navigation.js') return response(assets.navigation,200,{'Content-Type':'text/javascript; charset=utf-8'});
            if (get && path === '/password-fields.js') return response(assets.passwordFields,200,{'Content-Type':'text/javascript; charset=utf-8'});
            if (get && path === '/poster.js') return response(assets.poster,200,{'Content-Type':'text/javascript; charset=utf-8'});
            if (get && path === '/countdown.js') return response(assets.countdown,200,{'Content-Type':'text/javascript; charset=utf-8'});
            const flag = path.match(/^\/flag\/([A-Z]{2})\.webp$/);
            if (get && flag && Object.hasOwn(assets.flags,flag[1])) return response(Uint8Array.from(atob(assets.flags[flag[1]]),c=>c.charCodeAt(0)),200,{'Content-Type':'image/webp'});
            const demoPortrait = path.match(/^\/demo-winner\/(1[0-2]|[1-9])\.webp$/);
            if (get && demoPortrait) return response(Uint8Array.from(atob(assets.demoWinners[Number(demoPortrait[1])-1]),c=>c.charCodeAt(0)),200,{'Content-Type':'image/webp'});
            if (get && path === '/marble.webp') return response(Uint8Array.from(atob(assets.marble),c=>c.charCodeAt(0)),200,{'Content-Type':'image/webp'});
            if (get && path === '/hero-motion.js') return response(assets.heroMotion,200,{'Content-Type':'text/javascript; charset=utf-8'});
            if (get && path === '/favicon.ico') return response(Uint8Array.from(atob(assets.favicon),c=>c.charCodeAt(0)),200,{'Content-Type':'image/x-icon'});
            if (get && path === '/logo.png') return response(Uint8Array.from(atob(assets.logo),c=>c.charCodeAt(0)),200,{'Content-Type':'image/png'});
            const indexableContent=()=>rows(db,`SELECT 'events' AS kind,id FROM events WHERE published=1
                UNION ALL SELECT 'journal' AS kind,id FROM posts WHERE published=1`);
            if (get && path==='/sitemap.xml') return response(sitemapXml(await indexableContent()),200,{'Content-Type':'application/xml; charset=utf-8'});
            if (get && path==='/robots.txt') return response('User-agent: *\nSitemap: '+siteOrigin+'/sitemap.xml\n',
                200,{'Content-Type':'text/plain; charset=utf-8'});
            if (get && aliases.has(path)) return redirect(aliases.get(path));
            if (post && config && path === '/mpesa/callback/' + config.callback) {
                let event;
                try { event = JSON.parse(new TextDecoder().decode(await limitedBody(request,32768))).Body?.stkCallback; }
                catch { return response(null,400); }
                if (typeof event?.CheckoutRequestID === 'string') {
                    const order = await first(db,'SELECT * FROM orders WHERE checkout=?',event.CheckoutRequestID);
                    if (order && order.merchant === event.MerchantRequestID) ctx.waitUntil(checkPayment(db,config,order));
                    if (!order) {
                        const test=await first(db,'SELECT * FROM mpesa_tests WHERE checkout=?',event.CheckoutRequestID);
                        if (test && test.merchant===event.MerchantRequestID && test.environment===config.environment) {
                            await write(db,'UPDATE mpesa_tests SET callback_at=coalesce(callback_at,unixepoch()) WHERE id=?',test.id);
                            ctx.waitUntil(reconcilePaymentTest(db,config,test));
                        }
                    }
                }
                return response('{"ResultCode":0,"ResultDesc":"Accepted"}',200,{'Content-Type':'application/json'});
            }
            votingRound = await nextRound(db);
            if (get) session = await existingSession(db,request);
            if (get && path === '/') {
                const [ranking, next] = await Promise.all([standings(db,votingRound), first(db,
                    'SELECT * FROM events WHERE published=1 AND day>=? ORDER BY day,time LIMIT 1',dayNow())]);
                return page('Home',home(ranking,next,votingRound));
            }
            if (get && path === '/about') return page('About',`<section class="narrow wide">${heading('About SBA Awards')}${assets.about}</section>`);
            if (get && path === '/process') return page('How the awards work',processPage());
            if (get && path === '/categories') return page('Award categories',categoriesPage(await rows(db,`WITH editions AS (
                SELECT a.category,max(r.results_published_at) AS latest FROM award_results a JOIN voting_rounds r ON r.id=a.round_id
                GROUP BY a.category
            ) SELECT a.* FROM award_results a JOIN voting_rounds r ON r.id=a.round_id
              JOIN editions e ON e.category=a.category AND e.latest=r.results_published_at
              WHERE a.round_id=(SELECT max(b.round_id) FROM award_results b JOIN voting_rounds v ON v.id=b.round_id
                  WHERE b.category=a.category AND v.results_published_at=e.latest)
              ORDER BY a.place`)));
            const poster = path.match(/^\/nominees\/(\d{1,15})\/poster$/);
            if (get && poster) {
                const nominee=await first(db,"SELECT company,category,details FROM nominations WHERE id=? AND status='approved'",Number(poster[1]));
                return nominee ? page('Campaign poster',campaignPoster(nominee)) : missing();
            }
            if (get && path === '/nominees') return page('Nominees',heading('Meet the nominees') + directory(await nominees(db)));
            if (get && path === '/events') return page('Events',eventsPage(await rows(db,
                'SELECT * FROM events WHERE published=1 ORDER BY day,time')));
            if (get && path === '/journal') return page('Journal',journalPage(await rows(db,
                'SELECT * FROM posts WHERE published=1 ORDER BY created DESC,id DESC')));
            const article = path.match(/^\/journal\/(\d{1,15})$/);
            if (get && article) {
                const post = await first(db,'SELECT * FROM posts WHERE id=? AND published=1',Number(article[1]));
                return post ? page(post.title,articlePage(post)) : missing();
            }
            const winnerMedia = path.match(/^\/media\/winner\/(\d{1,15})$/);
            if (get && winnerMedia) {
                const winner=await first(db,'SELECT image FROM award_winners WHERE id=?',Number(winnerMedia[1]));
                const asset=winner?.image && await env.BUCKET.get(winner.image);
                if (!asset) return missing();
                const headers=new Headers();
                asset.writeHttpMetadata(headers);
                return response(asset.body,200,headers);
            }
            const programMedia = path.match(/^\/media\/(event|post)\/(\d{1,15})$/);
            if (get && programMedia) {
                const table = programMedia[1]==='event' ? 'events' : 'posts';
                const row = await first(db,`SELECT image FROM ${table} WHERE id=? AND published=1`,Number(programMedia[2]));
                const asset = row?.image && await env.BUCKET.get(row.image);
                if (!asset) return missing();
                const headers = new Headers();
                asset.writeHttpMetadata(headers);
                return response(asset.body,200,headers);
            }
            session ||= await sessionFor(db,request);
            const voter = session.voter;
            const showBallot = async message => await activeRound(db)
                ? page('Ballot',ballot(session,await nominees(db),await creditsFor(db,voter),message),message ? 422 : 200)
                : page('Voting countdown',votingCountdown(votingRound));
            const eventDetail = path.match(/^\/events\/(\d{1,15})$/);
            if (get && eventDetail) {
                const event = await first(db,'SELECT * FROM events WHERE id=? AND published=1',Number(eventDetail[1]));
                return event ? page(event.title,eventPage(session,event,Boolean(config))) : missing();
            }
            const programDashboard = async (tickets=[], message='', status=200) => page('Program administration',
                programAdmin(session,await rows(db,'SELECT * FROM events ORDER BY day DESC'),
                    await rows(db,'SELECT * FROM posts ORDER BY id DESC'),tickets,message),status);
            const awardsDashboard = async (message='',status=200) => {
                const [round,pairs,judging] = await Promise.all([nextRound(db),
                    rows(db,`SELECT p.nomination,p.voter,n.company,v.username,v.email
                        FROM nominee_pairings p JOIN nominations n ON n.id=p.nomination JOIN voters v ON v.id=p.voter
                        WHERE p.status='pending' AND v.email_verified IS NOT NULL
                        AND NOT EXISTS (SELECT 1 FROM nominee_pairings x WHERE x.nomination=p.nomination AND x.status='approved')
                        ORDER BY p.nomination`),judgingRounds(db)]);
                return page('Voting and nominee profiles',awardsAdmin(session,round,pairs,message)+judgingPanel(session,judging),status);
            };
            const paymentConsole=async (notice='',status=200) => page('M-PESA test console',
                paymentTestPage(session,env,await rows(db,'SELECT * FROM mpesa_tests ORDER BY created DESC,id DESC LIMIT 8'),notice),status);
            const media = path.match(/^\/(photo|proof)\/(\d{1,15})$/);
            if (get && media) {
                const proof = media[1] === 'proof';
                if (proof && !session.admin) return page('Sign-in required',heading('Admin sign-in required'),403);
                const row = await first(db, `SELECT ${proof ? 'proof' : 'photo'} AS key FROM nominations
                    WHERE id=? AND (status='approved' OR ? OR EXISTS (
                        SELECT 1 FROM nominee_pairings p WHERE p.nomination=nominations.id AND p.voter=? AND p.status='approved'))`,
                    Number(media[2]),session.admin,session.voter ?? null);
                const object = row && await env.BUCKET.get(row.key);
                if (!object) return missing();
                const headers = new Headers();
                object.writeHttpMetadata(headers);
                if (proof) headers.set('Content-Disposition','attachment; filename="ownership-proof"');
                return response(object.body,200,headers);
            }
            const payment = path.match(/^\/payments\/([a-f0-9]{64})$/);
            if (get && payment) {
                if (!voter) return redirect('/login');
                const order = await userOrder(db,voter,payment[1]);
                return order ? page('M-PESA status',paymentStatus(session,{...order,checkout_id:order.checkout})) : missing();
            }
            if (get) {
                switch (path) {
                    case '/login': {
                        const next = new URL(request.url).searchParams.get('next') === '/nominate' ? '/nominate' : '';
                        return voter || session.admin ? redirect(await dashboardFor(db,session)) : page('Sign in',login(session,'',next));
                    }
                    case '/verify-email': {
                        const params=new URL(request.url).searchParams;
                        const token=params.get('token') || '';
                        return /^[a-f0-9]{64}$/.test(token) ? page('Confirm email',emailConfirmation(session,token)) :
                            page('Verify your email',emailHelp(session,params.get('state') || (emailConfig(env) ? '' : 'unavailable')));
                    }
                    case '/dashboard': return redirect(await dashboardFor(db,session));
                    case '/signup': return page('Create an account',signup(session,'',
                        new URL(request.url).searchParams.get('role') === 'nominee' ? 'nominee' : 'voter'));
                    case '/nominate': return page('Nominate',nomination(session));
                    case '/nominee/dashboard':
                        return voter ? page('Nominee dashboard',nomineeDashboard(session,await pairedEntries(db,voter))) : redirect('/login');
                    case '/contact': return page('Contact',contact(session));
                    case '/help': return page('Voting help',`<section class="narrow">${heading('How to vote')}
                        <ol><li>Create a voter account or sign in.</li><li>Choose up to one nominee per category.</li>
                        <li>Review your choices. Your daily free credit covers one vote. Each extra vote costs KES 10.</li></ol>
                        ${link('/ballot','Go to your ballot','button')}</section>`);
                    case '/ballot':
                        if (!roundIsOpen(votingRound)) return page('Voting countdown',votingCountdown(votingRound));
                        return voter ? showBallot() : redirect('/login');
                    case '/credits': {
                        if (!voter) return redirect('/login');
                        const active = await pendingOrder(db,voter);
                        return active ? redirect(orderPath(active)) : page('Voting credits',creditShop(session,await creditsFor(db,voter),Boolean(config)));
                    }
                    case '/review': {
                        if (!voter) return redirect('/login');
                        if (!await activeRound(db)) return redirect('/ballot');
                        const picks = await selectPicks(db,JSON.parse(session.draft || '{}'));
                        return picks.length ? page('Review ballot',review(session,picks,await creditsFor(db,voter),Boolean(config))) : redirect('/ballot');
                    }
                    case '/account': {
                        if (!voter) return redirect('/login');
                        const account = await first(db,'SELECT first,last,username,email,role FROM voters WHERE id=?',voter);
                        const updated = new URL(request.url).searchParams.get('updated')==='1';
                        return page('Your account',accountPage(session,account,await creditsFor(db,voter),updated ? 'Password updated.' : '',votingRound));
                    }
                    case '/tickets':
                        return voter ? page('Your tickets',ticketsPage(await rows(db,`SELECT t.*,e.title,e.day,e.time,e.location,
                            o.status AS order_status FROM ticket_bookings t JOIN events e ON e.id=t.event
                            LEFT JOIN orders o ON o.id=t.order_id WHERE t.voter=? ORDER BY e.day DESC`,voter))) : redirect('/login');
                    case '/admin/payments': return session.admin ? paymentConsole() : redirect('/login');
                    case '/admin/program': return session.admin ? programDashboard() : redirect('/admin');
                    case '/admin/awards': return session.admin ? awardsDashboard() : redirect('/login');
                    case '/admin':
                        return session.admin ? page('Overview',adminOverview(await dashboardData(db),votingRound)) : redirect('/login');
                    case '/admin/analytics': {
                        if (!session.admin) return redirect('/login');
                        return page('Audience',await adminAudience(db,await analyticsReport(env),googleMeasurement(env)));
                    }
                    case '/admin/seo': {
                        if (!session.admin) return redirect('/login');
                        const [content,google,bing]=await Promise.all([indexableContent(),searchConsoleReport(env),bingReport(env)]);
                        return page('Search & SEO',adminSeo(Object.keys(publicPages).length+content.length,google,bing,{
                            google:Boolean(verificationToken(env.SBA_GOOGLE_SITE_VERIFICATION)),
                            bing:Boolean(verificationToken(env.SBA_BING_SITE_VERIFICATION))
                        }));
                    }
                    case '/admin/inbox':
                        return session.admin ? page('Inbox',messageAdmin(await rows(db,'SELECT * FROM messages ORDER BY id DESC'))) : redirect('/login');
                    case '/admin/nominations': {
                        if (!session.admin) return redirect('/login');
                        const params=new URL(request.url).searchParams;
                        const status=['pending','approved','rejected'].includes(params.get('status')) ? params.get('status') : 'all';
                        const query=(params.get('q')||'').trim().slice(0,80);
                        const select=`WHERE (?='all' OR n.status=?) AND
                            (?='' OR instr(lower(n.company),lower(?))>0 OR instr(lower(n.country),lower(?))>0)`;
                        const criteria=[status,status,query,query,query];
                        const {total}=await first(db,'SELECT count(*) AS total FROM nominations n '+select,...criteria);
                        const requested=Number(params.get('page'));
                        const current=Number.isSafeInteger(requested) ? Math.min(Math.max(1,requested),Math.max(1,Math.ceil(total/25))) : 1;
                        const entries=await rows(db,`SELECT n.id,n.category,n.company,n.country,n.status,n.details,
                            (SELECT count(*) FROM votes WHERE nominee=n.id) AS total FROM nominations n ${select}
                            ORDER BY n.status='pending' DESC,n.id DESC LIMIT 25 OFFSET ?`,...criteria,(current-1)*25);
                        return page('Nominees',nomineeAdmin(session,entries,query,status,current,total));
                    }
                }
                const info = confirmations.get(path);
                if (info) return page(info[0],heading(...info) + (path === '/nomination-received' && session.voter ?
                    link('/nominee/dashboard','View your nominations','button') : link('/','Back to home','button')));
            }
            if (!post) return missing();
            if (path.startsWith('/admin/') && !session.admin) return redirect('/admin');
            const values = await readForm(request,['/nominate','/nominee/photo','/admin/event','/admin/post','/admin/awards/winner'].includes(path));
            if (values.csrf !== session.csrf) return page('Form expired',heading('This form has expired','Reload the page and try again.'),403);
            switch (path) {
                case '/signup': {
                    signupRole = values.role === 'nominee' ? 'nominee' : 'voter';
                    const password = input(values,'password',256,true,false);
                    const confirmation = input(values,'confirm_password',256,true,false);
                    if (password !== confirmation) throw new InvalidForm('Passwords do not match.');
                    if (password.length < 8) throw new InvalidForm('Use at least 8 characters for your password.');
                    const username = input(values,'username',100);
                    const address=email(values,'email');
                    if (address.toLowerCase() === adminEmail) throw new InvalidForm('Use a different email address to create an account.');
                    const result = await first(db,`INSERT INTO voters(first,last,username,email,password,role) VALUES (?,?,?,?,?,?)
                        ON CONFLICT DO NOTHING RETURNING id,email`,input(values,'first',150),input(values,'last',150),
                        username,address,await passwordHash(password),signupRole);
                    if (!result) throw new InvalidForm('That username or email is already registered.');
                    const state=await sendVerification(db,env,result);
                    return redirect('/verify-email?state='+state);
                }
                case '/verify-email/resend': {
                    if (!emailConfig(env)) return redirect('/verify-email?state=unavailable');
                    const address=email(values,'email');
                    const account=await first(db,'SELECT id,email FROM voters WHERE email=? AND email_verified IS NULL',address);
                    if (account) await sendVerification(db,env,account);
                    return redirect('/verify-email');
                }
                case '/verify-email': {
                    if (!await confirmEmail(db,values.token,values.password)) return page('Verification unsuccessful',
                        `<p class="notice" role="alert">Check your account password. If the link expired,
                            <a href="/verify-email">request a new one</a>.</p>`+
                            (/^[a-f0-9]{64}$/.test(values.token) ? emailConfirmation(session,values.token) : ''),422);
                    return page('Email verified',heading('Email verified','Your account is active.')+link('/login','Sign in','button'));
                }
                case '/login': {
                    const address = email(values,'email');
                    const password = input(values,'password',256,true,false);
                    if (address.toLowerCase() === adminEmail) {
                        if (!env.SBA_ADMIN_PASSWORD || !equal(await passwordHash(password,'admin'),
                            await passwordHash(env.SBA_ADMIN_PASSWORD,'admin'))) {
                            throw new InvalidForm('Check your email address and password and try again.');
                        }
                        session = await newSession(db,null,1,session.id);
                        return redirect('/admin');
                    }
                    const found = await first(db,'SELECT id,email,password,email_verified FROM voters WHERE email=?',address);
                    const hash = await passwordHash(password,found?.password.split(':')[0] || '0'.repeat(64));
                    if (!found || !equal(hash,found.password)) throw new InvalidForm('Check your email address and password and try again.');
                    if (!found.email_verified) {
                        const state=await sendVerification(db,env,found);
                        return redirect('/verify-email?state='+state);
                    }
                    session = await newSession(db,found.id,0,session.id);
                    return redirect(await dashboardFor(db,session));
                }
                case '/logout':
                    await write(db,'DELETE FROM sessions WHERE id=?',session.id);
                    sessionCookie = cookie('',0);
                    return redirect('/');
                case '/nominate':
                    await saveNomination(db,env.BUCKET,values,voter ?? null);
                    return redirect('/nomination-received');
                case '/contact':
                    await write(db,'INSERT INTO messages(name,email,message) VALUES (?,?,?)',
                        input(values,'name',150),email(values,'email'),input(values,'message',3000));
                    return redirect('/message-received');
                case '/review': {
                    if (!voter) return redirect('/login');
                    if (!await activeRound(db)) return redirect('/ballot');
                    const picks = await selectPicks(db,values);
                    if (!picks.length) return showBallot('Choose at least one available nominee.');
                    const draft = JSON.stringify(Object.fromEntries(picks.map(row=>[row.category,String(row.id)])));
                    await write(db,'UPDATE sessions SET draft=? WHERE id=?',draft,session.id);
                    return redirect('/review');
                }
                case '/vote': {
                    if (!voter) return redirect('/login');
                    const active = await pendingOrder(db,voter);
                    if (active) return redirect(orderPath(active));
                    const draft = session.draft;
                    const picks = draft && draft === values.draft ? await selectPicks(db,JSON.parse(draft)) : [];
                    if (!picks.length) return showBallot('Your choices changed. Review your ballot again.');
                    const day = dayNow();
                    const quote = voteQuote(picks.length,await creditsFor(db,voter,day));
                    if (quote.checkout) return redirect('/review');
                    const picked = JSON.stringify(picks.map(row=>row.id));
                    const result = await db.batch([
                        statement(db,`INSERT INTO submissions (id,voter,day,free,paid,picks)
                            SELECT ?,v.id,?,?,?,? FROM voters v WHERE v.id=? AND v.credits>=?
                            AND EXISTS (SELECT 1 FROM voting_rounds WHERE closed_at IS NULL AND opened_at<=unixepoch())
                            AND EXISTS (SELECT 1 FROM sessions WHERE id=? AND draft=?)
                            AND NOT EXISTS (SELECT 1 FROM orders WHERE voter=v.id AND status IN ('starting','pending','unknown'))
                            AND (?=0 OR NOT EXISTS (SELECT 1 FROM daily_vote_claims WHERE voter=v.id AND day=?))
                            AND NOT EXISTS (SELECT 1 FROM json_each(?) j LEFT JOIN nominations n ON n.id=j.value
                                WHERE n.id IS NULL OR n.status<>'approved')`,
                            random(),day,quote.free,quote.paid,picked,voter,quote.paid,session.id,draft,quote.free,day,picked),
                        statement(db,'UPDATE sessions SET draft=NULL WHERE id=? AND changes()>0',session.id)
                    ]);
                    return result[0].meta.changes ? redirect('/thanks') : showBallot('Your available credits changed. Review your ballot again.');
                }
                case '/payments/start': {
                    if (!voter) return redirect('/login');
                    if (!config) return page('M-PESA unavailable',heading('M-PESA is unavailable'),503);
                    const active = await pendingOrder(db,voter);
                    if (active) return redirect(orderPath(active));
                    const phone = phoneNumber(values.phone);
                    const kind = values.kind;
                    const units = Number(values.units);
                    if (!phone || !['topup','ballot'].includes(kind)) throw new InvalidForm('Enter a valid Kenyan mobile number.');
                    const picks = kind === 'ballot' && session.draft === values.draft
                        ? await selectPicks(db,JSON.parse(session.draft || '{}')) : [];
                    if (kind==='topup' ? !Number.isInteger(units) || units<1 || units>100 : !picks.length) {
                        return redirect(kind==='ballot' ? '/review' : '/credits');
                    }
                    const order = await createOrder(db,voter,kind,units,picks,Number(values.amount));
                    if (!order) return redirect(kind==='ballot' ? '/ballot' : '/credits');
                    await promptPayment(db,config,order,phone);
                    return redirect(orderPath(order));
                }
                case '/payments/check': {
                    if (!voter) return redirect('/login');
                    const order = typeof values.id==='string' && await userOrder(db,voter,values.id);
                    if (!order) return missing();
                    await checkPayment(db,config,order);
                    return redirect(orderPath(order));
                }
                case '/account/password': {
                    if (!voter) return redirect('/login');
                    const account = await first(db,'SELECT password FROM voters WHERE id=?',voter);
                    const current = input(values,'current',256,true,false);
                    const replacement = input(values,'replacement',256,true,false);
                    if (!equal(await passwordHash(current,account.password.split(':')[0]),account.password)) {
                        throw new InvalidForm('The current password is incorrect.');
                    }
                    if (replacement.length<8) throw new InvalidForm('Use at least 8 characters for your new password.');
                    const result = await db.batch([
                        statement(db,'UPDATE voters SET password=? WHERE id=? AND password=?',await passwordHash(replacement),voter,account.password),
                        statement(db,'DELETE FROM sessions WHERE voter=? AND changes()>0',voter)
                    ]);
                    if (!result[0].meta.changes) throw new InvalidForm('The password changed. Sign in and try again.');
                    session = await newSession(db,voter);
                    return redirect('/account?updated=1');
                }
                case '/tickets/book': return voter ? redirect(await bookTickets(db,config,voter,values)) : redirect('/login');
                case '/nominee/photo':
                    if (!voter) return redirect('/login');
                    await replaceNomineePhoto(db,env.BUCKET,voter,values);
                    return redirect('/nominee/dashboard');
                case '/admin/payments/connection':
                    if (!config || paymentSetupIssues(env).length) return paymentConsole('Check the configuration first.',422);
                    try { await accessToken(config); }
                    catch (error) {
                        reportPaymentDiagnostic('oauth',error.status,error.code,error.reason);
                        return paymentConsole('Daraja connection failed. '+(providerDetails('oauth',error.status,error.code,error.reason) ||
                            'Check the Consumer Key, Consumer Secret and environment.'),502);
                    }
                    return paymentConsole('Daraja accepted the key and secret. The Till and passkey are checked by the prompt test.');
                case '/admin/payments/start': {
                    if (!config || paymentSetupIssues(env).length) return paymentConsole('Check the configuration first.',422);
                    const phone=phoneNumber(values.phone);
                    if (!phone || values.confirm!=='yes') return paymentConsole('Enter a Kenyan mobile number and confirm the test.',422);
                    const id=await startPaymentTest(db,config,phone);
                    return id ? redirect('/admin/payments#test-'+id) :
                        paymentConsole('Wait one minute between test prompts. Check the previous result before retrying.',429);
                }
                case '/admin/payments/check': {
                    const id=values.test;
                    if (typeof id!=='string' || !/^[a-f0-9]{64}$/.test(id)) return paymentConsole('Choose a test to check.',422);
                    const test=await getPaymentTest(db,id);
                    if (!test) return paymentConsole('That test could not be found.',404);
                    await reconcilePaymentTest(db,config,test);
                    return redirect('/admin/payments#test-'+id);
                }
                case '/admin/awards/open': {
                    const title=input(values,'title',100);
                    const opened=await write(db,`INSERT INTO voting_rounds(title) SELECT ? WHERE NOT EXISTS
                        (SELECT 1 FROM voting_rounds WHERE closed_at IS NULL OR title=? COLLATE NOCASE)`,title,title);
                    return awardsDashboard(opened.meta.changes ? 'Voting is open.' : 'A round is already open or that edition already exists.');
                }
                case '/admin/awards/close': {
                    const id=Number(values.id);
                    if (!Number.isSafeInteger(id) || id<1) throw new InvalidForm('Choose a voting round.');
                    return awardsDashboard(await closeRound(db,id) ? 'Voting closed. Finalists are ready for panel scoring.' :
                        'The round could not close. Check pending ballot payments.',200);
                }
                case '/admin/awards/score': {
                    const round=Number(values.round), nominee=Number(values.nominee);
                    const score=values['panel-'+round+'-'+nominee];
                    if (!Number.isSafeInteger(round)||!Number.isSafeInteger(nominee)||typeof score!=='string'||
                        !/^\d{1,3}$/.test(score)||Number(score)>100) throw new InvalidForm('Enter a panel score from 0 to 100.');
                    const saved=await write(db,`UPDATE judging_entries SET panel_score=? WHERE round_id=? AND nominee=?
                        AND EXISTS (SELECT 1 FROM voting_rounds WHERE id=? AND closed_at IS NOT NULL AND results_published_at IS NULL)`,
                        Number(score),round,nominee,round);
                    return awardsDashboard(saved.meta.changes ? 'Panel score saved.' : 'This score can no longer be edited.');
                }
                case '/admin/awards/publish': {
                    const round=Number(values.round);
                    if (!Number.isSafeInteger(round)||round<1) throw new InvalidForm('Choose a round.');
                    return awardsDashboard(await publishResults(db,round) ? 'Winners and runners-up published.' :
                        'The results could not be published. Complete all scores and resolve ties for the top three places.');
                }
                case '/admin/awards/winner': {
                    const date=input(values,'date',10), day=new Date(date+'T00:00:00+03:00');
                    const category=input(values,'category',50);
                    if (!categories.includes(category)||!/^\d{4}-\d{2}-\d{2}$/.test(date)||
                        !Number.isFinite(+day)||nairobiDate.format(day)!==date||day>new Date()) throw new InvalidForm('Check the category and award date.');
                    const country=input(values,'country',2);
                    if (!Object.hasOwn(assets.flags,country)) throw new InvalidForm('Choose a country.');
                    const name=input(values,'company',200),edition=input(values,'edition',100);
                    await optionalImage(env.BUCKET,values.image,'winner',image=>write(db,`INSERT INTO award_winners
                        (category,company,edition,awarded_at,country,image) VALUES(?,?,?,?,?,?)
                        ON CONFLICT(category,edition) DO UPDATE SET company=excluded.company,awarded_at=excluded.awarded_at,
                            country=excluded.country, image=coalesce(excluded.image,award_winners.image),nominee=NULL,votes=NULL`,
                        category,name,edition,Math.floor(+day/1000),country,image));
                    return awardsDashboard('Winner saved.');
                }
                case '/admin/awards/pair': {
                    const candidate=Number(values.nomination), account=Number(values.voter), decision=values.decision;
                    if (!Number.isSafeInteger(candidate)||!Number.isSafeInteger(account)||!['approved','rejected'].includes(decision)) {
                        throw new InvalidForm('Choose a valid pairing request.');
                    }
                    const result=await write(db,`UPDATE nominee_pairings SET status=? WHERE nomination=? AND voter=? AND status='pending'
                        AND EXISTS (SELECT 1 FROM voters v JOIN nominations n ON n.id=?
                            WHERE v.id=? AND v.email_verified IS NOT NULL AND lower(trim(v.email))=lower(trim(json_extract(n.details,'$.Email'))))
                        AND NOT EXISTS (SELECT 1 FROM nominee_pairings p WHERE p.nomination=? AND p.status='approved')`,
                        decision,candidate,account,candidate,account,candidate);
                    return awardsDashboard(result.meta.changes ? 'Pairing request updated.' : 'That pairing request is no longer available.');
                }
                case '/admin/event':
                    await saveEvent(db,env.BUCKET,values);
                    return redirect('/admin/program');
                case '/admin/post':
                    await savePost(db,env.BUCKET,values);
                    return redirect('/admin/program');
                case '/admin/content/status': {
                    if (!['event','post'].includes(values.type) || !['0','1'].includes(values.published) ||
                        !/^\d{1,15}$/.test(values.id)) throw new InvalidForm('Choose valid content.');
                    const table = values.type==='event' ? 'events' : 'posts';
                    await write(db,`UPDATE ${table} SET published=? WHERE id=?`,Number(values.published),Number(values.id));
                    return redirect('/admin/program');
                }
                case '/admin/ticket/check':
                case '/admin/ticket/checkin': {
                    const reference = input(values,'reference',16).toLowerCase();
                    if (!/^[a-f0-9]{16}$/.test(reference)) throw new InvalidForm('Enter a valid ticket reference.');
                    const ticket = await first(db,`SELECT t.*,e.title FROM ticket_bookings t JOIN events e ON e.id=t.event
                        WHERE t.id=? AND t.status='confirmed'`,reference);
                    if (!ticket) return programDashboard([],'No confirmed booking matches this reference.',404);
                    if (path.endsWith('/check')) return programDashboard([ticket]);
                    const checked=await write(db,'UPDATE ticket_bookings SET checked_in=unixepoch() WHERE id=? AND checked_in IS NULL',reference);
                    return programDashboard([],checked.meta.changes ? 'Attendance confirmed.' : 'This booking is already checked in.');
                }
                case '/admin/nomination':
                    if (!session.admin) return page('Sign-in required',heading('Admin sign-in required'),403);
                    if (!['approved','rejected'].includes(values.status) || !/^\d{1,15}$/.test(values.id)) throw new InvalidForm('Choose a valid nomination.');
                    await write(db,'UPDATE nominations SET status=? WHERE id=?',values.status,Number(values.id));
                    return redirect('/admin/nominations');
                default: return missing();
            }
        } catch (error) {
            if (error instanceof InvalidForm) {
                const forms = new Map([['/verify-email/resend',(s,m)=>flash(m)+emailHelp(s,'')],['/signup',(s,m)=>signup(s,m,signupRole)],['/login',login],['/nominate',nomination],
                    ['/contact',contact]]);
                const render = forms.get(path);
                return page('Check your submission',render ? render(session,error.message) : flash(error.message) + (path.startsWith('/admin/awards/') ? link('/admin/awards','Back to voting and nominee profiles') :
                    path.startsWith('/admin/') ? link('/admin/program','Back to program administration')
                    : path.startsWith('/tickets/') ? link('/events','Back to events') : path.startsWith('/account/') ? link('/account','Back to your account') :
                    path.startsWith('/nominee/') ? link('/nominee/dashboard','Back to nominee dashboard') : link('/ballot','Back to ballot')),422);
            }
            console.error('SBA Awards request failed',error?.name,error?.message);
            return page('Something went wrong',heading('Something went wrong','Please try again.'),500);
        }
    }
};
