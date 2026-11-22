const siteOrigin='https://sba.tsols.co.ke';
const publicPages={
    '/':['Home','SBA Awards honours the African founders and businesses creating lasting impact.'],
    '/about':['About','Meet SBA Awards and learn why we recognise African entrepreneurs.'],
    '/categories':['Award categories','Explore the 12 SBA Awards and the businesses eligible in each category.'],
    '/process':['Awards process','See the SBA Awards process from nomination and verification through voting, judging and the gala.'],
    '/nominees':['Nominees','Discover approved SBA Awards nominees and their businesses.'],
    '/nominate':['Nominate','Nominate an eligible founder or business for the SBA Awards.'],
    '/events':['Events','Explore SBA Awards events and book your tickets.'],
    '/journal':['Journal','Read stories and updates from the SBA Awards community.'],
    '/ballot':['Voting','See the voting schedule and how to vote in the SBA Awards.'],
    '/contact':['Contact','Contact the SBA Awards team.']
};
const adminSections=[
    ['/admin','Overview','▦'],['/admin/nominations','Nominees','◇'],['/admin/awards','Voting & results','◉'],
    ['/admin/program','Events & journal','▤'],['/admin/payments','M-PESA','◈'],
    ['/admin/analytics','Audience','▥'],['/admin/seo','Search & SEO','⌕'],['/admin/inbox','Inbox','▱']
];
const routeMatches=(path,route)=>path===route || (route!=='/admin' && path.startsWith(route+'/'));
const adminSection=(path)=>path==='/admin/nomination' ? adminSections[1] : adminSections.find(([route])=>routeMatches(path,route)) || adminSections[0];
const number = value => new Intl.NumberFormat('en-KE').format(value ?? 0);
const reportDay = (offset=0) => nairobiDate.format(new Date(Date.now()-offset*86400000));

function publicMetadata(path,title,status) {
    if (status!==200) return {index:false};
    if (Object.hasOwn(publicPages,path)) return {index:true,url:siteOrigin+path,description:publicPages[path][1]};
    if (/^\/(events|journal)\/\d{1,15}$/.test(path)) return {index:true,url:siteOrigin+path,description:title+' | SBA Awards'};
    return {index:false};
}
function publicMeta(path,title,status) {
    const meta=publicMetadata(path,title,status);
    if (!meta.index) return '<meta name="robots" content="noindex,follow">';
    return `<link rel="canonical" href="${h(meta.url)}"><meta name="description" content="${h(meta.description)}">
        <meta property="og:type" content="website"><meta property="og:title" content="${h(title)} | SBA Awards">
        <meta property="og:description" content="${h(meta.description)}"><meta property="og:url" content="${h(meta.url)}">
        <meta property="og:image" content="${siteOrigin}/logo.png">`;
}
function sitemapXml(entries) {
    const paths=[...Object.keys(publicPages),...entries.map(entry=>'/'+entry.kind+'/'+entry.id)];
    return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'+
        paths.map(path=>'<url><loc>'+h(siteOrigin+path)+'</loc></url>').join('')+'</urlset>';
}
function shouldMeasure(request,path) {
    return (Object.hasOwn(publicPages,path)||/^\/(events|journal)\/\d{1,15}$/.test(path)) &&
        request.headers.get('Sec-GPC')!=='1' && request.headers.get('DNT')!=='1' &&
        !/bot|crawler|spider|preview|curl|wget|headless|python/i.test(request.headers.get('User-Agent')||'');
}
const audienceCookie = (day, browser) => `sba_audience=${day}.${browser}; Path=/; Max-Age=86400; HttpOnly; Secure; SameSite=Lax`;
function audienceKey(request) {
    const day=reportDay();
    const match=request.headers.get('cookie')?.match(/(?:^|;\s*)sba_audience=(\d{4}-\d{2}-\d{2})\.([a-f0-9]{64})(?:;|$)/);
    return {day,browser:match?.[1]===day ? match[2] : random(),fresh:match?.[1]!==day};
}
async function recordAudience(db,path,day,browser,fresh) {
    const statements=[
        statement(db,'INSERT OR IGNORE INTO traffic_visitors(day,browser) VALUES(?,?)',day,browser),
        statement(db,`INSERT INTO traffic_days(day,views) VALUES(?,1)
            ON CONFLICT(day) DO UPDATE SET views=views+1`,day),
        statement(db,`INSERT INTO traffic_pages(day,path,views) VALUES(?,?,1)
            ON CONFLICT(day,path) DO UPDATE SET views=views+1`,day,path)
    ];
    if (fresh) statements.push(statement(db,'DELETE FROM traffic_visitors WHERE day<?',day));
    await db.batch(statements);
}

async function dashboardData(db) {
    const today=reportDay(), starting=reportDay(13);
    const [stats,traffic,dailyVotes,leaders,pending]=await Promise.all([
        first(db,`SELECT (SELECT count(*) FROM votes) AS votes,
            (SELECT count(*) FROM votes WHERE cast_at>=unixepoch()-86400) AS votes_recent,
            (SELECT count(*) FROM nominations) AS nominees,
            (SELECT count(*) FROM nominations WHERE status='approved') AS approved,
            (SELECT count(*) FROM nominations WHERE status='pending') AS pending,
            (SELECT count(*) FROM voters WHERE email_verified IS NOT NULL) AS accounts`),
        rows(db,'SELECT day,visitors,views FROM traffic_days WHERE day>=? ORDER BY day',starting),
        rows(db,`SELECT date(cast_at+10800,'unixepoch') AS day,count(*) AS votes FROM votes
            WHERE cast_at>=unixepoch(?)-10800 GROUP BY day ORDER BY day`,starting),
        rows(db,`SELECT n.company,n.category,count(v.id) AS votes FROM votes v
            JOIN nominations n ON n.id=v.nominee WHERE n.status='approved' GROUP BY n.id ORDER BY votes DESC,n.company LIMIT 5`),
        rows(db,`SELECT id,company,category,country FROM nominations WHERE status='pending' ORDER BY id DESC LIMIT 5`)
    ]);
    const days=Array.from({length:14},(_,index)=>reportDay(13-index));
    const trafficByDay=new Map(traffic.map(entry=>[entry.day,entry]));
    const votesByDay=new Map(dailyVotes.map(entry=>[entry.day,entry.votes]));
    const timeline=days.map(day=>({day,visitors:trafficByDay.get(day)?.visitors||0,views:trafficByDay.get(day)?.views||0,votes:votesByDay.get(day)||0}));
    return {stats,today:trafficByDay.get(today),timeline,leaders,pending};
}
function adminLayout(title,body,session,path,round) {
    const section=adminSection(path);
    const status=roundIsOpen(round) ? 'Voting open' : round ? 'Voting scheduled' : 'Voting closed';
    return `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="robots" content="noindex,nofollow"><title>${h(title)} | SBA Awards Admin</title>
        <link rel="icon" href="/favicon.ico"><link rel="stylesheet" href="/styles.css"><script src="/navigation.js" defer></script></head>
        <body class="admin-ui"><a class="skip" href="#admin-main">Skip to content</a>
        <aside class="admin-sidebar"><a class="admin-brand" href="/admin"><img src="/logo.png" width="42" height="42" alt=""><span>SBA Awards<small>Administration</small></span></a>
        ${menuToggle('admin-navigation')}<nav id="admin-navigation" aria-label="Admin navigation">${adminSections.map(([url,label,mark])=>`<a href="${url}" ${url===section[0]?'aria-current="page"':''}>
            <span class="admin-nav-mark" aria-hidden="true">${mark}</span>${label}</a>`).join('')}</nav>
        <div class="admin-sidebar-bottom">${link('/','View public site')}${form('/logout',session.csrf,button('Sign out'))}</div></aside>
        <div class="admin-workspace"><header class="admin-topbar"><span>Administration <span class="muted">/ ${section[1]}</span></span>
        <span class="admin-round ${roundIsOpen(round) ? 'is-open' : ''}">${status}</span></header>
        <main id="admin-main" class="admin-main">${body}</main></div></body></html>`;
}
const adminHeading=(title,detail)=>`<header class="admin-heading"><h1>${h(title)}</h1><p>${h(detail)}</p></header>`;
const kpi=(label,value,detail,url)=>`<a href="${url}" class="admin-kpi"><span>${h(label)}</span><strong>${number(value)}</strong><small>${h(detail)}</small></a>`;

function activityChart(title,subtitle,timeline,key) {
    const max=Math.max(1,...timeline.map(item=>item[key]));
    const ticks=[max,Math.round(max*.5),0];
    return `<section class="admin-panel"><div class="admin-panel-title"><h2>${h(title)}</h2><p>${h(subtitle)}</p></div>
        <div class="activity-chart"><div class="chart-axis" aria-hidden="true">${ticks.map(value=>`<span>${number(value)}</span>`).join('')}</div>
        <div class="chart-plot"><div class="chart-bars">${timeline.map(item=>{
            const label=new Intl.DateTimeFormat('en-KE',{day:'numeric',month:'short',timeZone:'UTC'}).format(new Date(item.day+'T00:00:00Z'));
            const height=Math.max(item[key] ? 2 : 0,Math.round(item[key]/max*20));
            return `<div class="chart-column" title="${h(label)} · ${number(item[key])}" aria-label="${h(label)}: ${number(item[key])}">
                <span class="chart-bar bar-${height}"></span></div>`;
        }).join('')}</div><div class="chart-dates" aria-hidden="true">${[0,3,6,9,13].map(i=>`<span>${new Intl.DateTimeFormat('en-KE',{day:'numeric',month:'short',timeZone:'UTC'}).format(new Date(timeline[i].day+'T00:00:00Z'))}</span>`).join('')}</div></div></div>
        <details class="chart-data"><summary>View daily figures</summary><table class="admin-table"><thead><tr><th>Date (EAT)</th><th>${h(title)}</th></tr></thead>
            <tbody>${timeline.map(item=>`<tr><th scope="row">${item.day}</th><td>${number(item[key])}</td></tr>`).join('')}</tbody></table></details></section>`;
}
function adminTable(title,headers,items,empty,action='') {
    return `<section class="admin-panel"><div class="admin-panel-title"><h2>${title}</h2>${action}</div>
        ${items.length ? `<div class="admin-table-wrap"><table class="admin-table"><thead><tr>
        ${headers.map(header=>`<th scope="col">${header}</th>`).join('')}</tr></thead><tbody>
        ${items.map(item=>`<tr>${item.map((value,index)=>index===0?`<th scope="row">${value}</th>`:`<td>${value}</td>`).join('')}</tr>`).join('')}
        </tbody></table></div>` : `<p class="admin-empty">${empty}</p>`}</section>`;
}
function adminOverview({stats,today,timeline,leaders,pending},round) {
    const roundText=round ? roundIsOpen(round) ? round.title+' is open for voting.' : round.title+' opens '+roundDate(round)+'.' : 'There is no open or scheduled voting round.';
    return adminHeading('Overview',roundText)+`<div class="admin-kpi-grid">
        ${kpi('Total votes',stats.votes,number(stats.votes_recent)+' in the last 24 hours','/admin/awards')}
        ${kpi('Nominations',stats.nominees,number(stats.pending)+' awaiting review','/admin/nominations')}
        ${kpi('Visitors today',today?.visitors,number(today?.views)+' page views today','/admin/analytics')}
        ${kpi('Verified accounts',stats.accounts,number(stats.approved)+' approved nominees','/admin/nominations')}
        </div><div class="admin-grid">${activityChart('Page views','Last 14 days · East Africa Time',timeline,'views')}
        ${activityChart('Votes cast','Last 14 days · East Africa Time',timeline,'votes')}</div>
        <div class="admin-grid">${adminTable('Nomination queue',['Business','Category','Country'],
        pending.map(item=>[link('/admin/nominations#nomination-'+item.id,h(item.company)),h(item.category),h(item.country)]),
        'No nominations are waiting for approval.',link('/admin/nominations','Review all'))}
        ${adminTable('Leading nominees',['Nominee','Category','Total votes'],
        leaders.map(item=>[h(item.company),h(item.category),number(item.votes)]),'Vote totals will appear after the first votes are recorded.',
        link('/admin/awards','Voting controls'))}</div>`;
}
async function adminAudience(db,ga,measurement) {
    const {today,timeline}=await dashboardData(db);
    const pages=await rows(db,`SELECT path,sum(views) AS views FROM traffic_pages WHERE day>=?
        GROUP BY path ORDER BY views DESC,path LIMIT 10`,reportDay(13));
    const total=timeline.reduce((sum,item)=>sum+item.views,0);
    return adminHeading('Audience','First-party traffic recorded since analytics was enabled.')+
        `<div class="admin-kpi-grid">${kpi('Visitors today',today?.visitors,'Unique browsers today','/admin/analytics')}
        ${kpi('Page views today',today?.views,'Public page requests today','/admin/analytics')}
        ${kpi('14-day page views',total,'Includes return visits','/admin/analytics')}</div>
        <div class="admin-grid">${activityChart('Daily visitors','A browser is counted once per East Africa calendar day.',timeline,'visitors')}
        ${activityChart('Page views','Public pages · last 14 days',timeline,'views')}</div>`+
        adminTable('Most visited pages',['Page','Views · last 14 days'],pages.map(item=>[link(item.path,h(item.path)),number(item.views)]),
            'No qualifying page views have been recorded.')+googleAnalyticsPanels(ga,measurement);
}
function adminSeo(count,google,bing,verification) {
    const checks=[
        ['Canonical URLs','Public pages use sba.tsols.co.ke as the canonical host.'],
        ['Page descriptions','Each core public page has a title and description.'],
        ['Social sharing','Public pages include Open Graph title, description, URL and image tags.'],
        ['Private pages','Admin and account pages are marked noindex.']
    ];
    return adminHeading('Search & SEO','Technical search visibility checks for the public site.')+
        `<div class="admin-kpi-grid">${kpi('Pages in sitemap',count,'Core pages and published content','/sitemap.xml')}</div>
        <div class="admin-grid"><section class="admin-panel"><div class="admin-panel-title"><h2>Technical checks</h2></div>
        <ul class="seo-checks">${checks.map(([title,description])=>`<li><span>Enabled</span><div><strong>${title}</strong><p>${description}</p></div></li>`).join('')}</ul>
        <div class="actions">${link('/sitemap.xml','Open sitemap','button secondary')}${link('/robots.txt','Open robots.txt','button secondary')}</div></section>
        <section class="admin-panel"><div class="admin-panel-title"><h2>Site verification</h2></div>
        <p>Google HTML tag ${verification.google ? 'published' : 'not configured'}</p>
        <p>Bing HTML tag ${verification.bing ? 'published' : 'not configured'}</p>
        <p class="muted">Verify ownership in each provider’s dashboard. Tags are optional if the property is already verified.</p></section></div>
        ${searchReportPanels('Google Search Console',google)}${searchReportPanels('Bing Webmaster Tools',bing)}`;
}
