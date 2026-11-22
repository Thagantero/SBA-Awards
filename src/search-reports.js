const googleTokenUrl='https://oauth2.googleapis.com/token';
const googleScopes={
    ga:'https://www.googleapis.com/auth/analytics.readonly',
    gsc:'https://www.googleapis.com/auth/webmasters.readonly'
};
const googleMeasurement=env=>/^G-[A-Z0-9]{4,20}$/.test(String(env.SBA_GA4_MEASUREMENT_ID||'').trim())
    ? String(env.SBA_GA4_MEASUREMENT_ID).trim() : '';
function verificationToken(value) {
    const token=String(value||'').trim();
    return /^[A-Za-z0-9_-]{8,180}$/.test(token) ? token : '';
}
function verificationMeta(env) {
    const google=verificationToken(env.SBA_GOOGLE_SITE_VERIFICATION),bing=verificationToken(env.SBA_BING_SITE_VERIFICATION);
    return (google ? `<meta name="google-site-verification" content="${h(google)}">` : '') +
        (bing ? `<meta name="msvalidate.01" content="${h(bing)}">` : '');
}
function trackingControls(id) {
    return id ? `<section class="analytics-consent" id="analytics-consent" data-measurement="${id}"
        aria-label="Analytics preferences" hidden><p>Allow optional Google Analytics to help us understand how people use SBA Awards.</p>
        <div class="actions"><button type="button" data-analytics="allow">Allow analytics</button>
        <button type="button" data-analytics="decline" class="secondary">Decline</button></div></section>
        <script src="/analytics-consent.js" defer></script>` : '';
}
const base64url=bytes=>btoa(String.fromCharCode(...bytes)).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'');
class ReportFailure extends Error {}
async function reportJson(url,options={},provider) {
    let response;
    try { response=await fetch(url,{...options,redirect:'error',signal:AbortSignal.timeout(8000)}); }
    catch { throw new ReportFailure(provider+' did not respond. Try again shortly.'); }
    if (!response.ok) {
        const status=response.status;
        const detail=status===401||status===403 ? ' Check credentials, property access and whether the API is enabled.' :
            status===404 ? ' Check the property or verified site URL.' : status===429 ? ' The provider is rate limiting requests.' : '';
        throw new ReportFailure(provider+' returned HTTP '+status+'.'+detail);
    }
    try { return await response.json(); }
    catch { throw new ReportFailure(provider+' returned an unreadable response.'); }
}
async function googleReportToken(env,scope) {
    let credentials;
    try { credentials=JSON.parse(env.SBA_GOOGLE_SERVICE_ACCOUNT_JSON); }
    catch { throw new ReportFailure('The Google service-account secret is not valid JSON.'); }
    const pem=credentials?.private_key?.match(/^-----BEGIN PRIVATE KEY-----([\s\S]+?)-----END PRIVATE KEY-----\s*$/)?.[1];
    if (!pem || !/^[^@\s]+@[^@\s]+\.gserviceaccount\.com$/.test(credentials?.client_email||'')) {
        throw new ReportFailure('Use the JSON key for a Google Cloud service account.');
    }
    let signature, assertion;
    try {
        const der=Uint8Array.from(atob(pem.replace(/\s+/g,'')),c=>c.charCodeAt(0));
        const key=await crypto.subtle.importKey('pkcs8',der,{name:'RSASSA-PKCS1-v1_5',hash:'SHA-256'},false,['sign']);
        const iat=Math.floor(Date.now()/1000);
        assertion=base64url(encode.encode(JSON.stringify({alg:'RS256',typ:'JWT'})))+'.'+base64url(encode.encode(JSON.stringify({
            iss:credentials.client_email,scope,aud:googleTokenUrl,iat,exp:iat+300
        })));
        signature=await crypto.subtle.sign('RSASSA-PKCS1-v1_5',key,encode.encode(assertion));
    } catch { throw new ReportFailure('The Google service-account key could not be read.'); }
    const token=await reportJson(googleTokenUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({grant_type:'urn:ietf:params:oauth:grant-type:jwt-bearer',
            assertion:assertion+'.'+base64url(new Uint8Array(signature))})},'Google authentication');
    if (typeof token?.access_token!=='string' || !token.access_token) throw new ReportFailure('Google did not return an access token.');
    return token.access_token;
}
const authorizedReport = (url,token,body,provider) => reportJson(url,{
    method:'POST',headers:{Authorization:'Bearer '+token,'Content-Type':'application/json'},body:JSON.stringify(body)
},provider);
const metric=value=>Number.isFinite(Number(value))?Number(value):0;
const ratio=(clicks,impressions)=>impressions ? clicks/impressions : 0;
const percentage=value=>new Intl.NumberFormat('en-KE',{style:'percent',maximumFractionDigits:1}).format(metric(value));
const average=value=>new Intl.NumberFormat('en-KE',{maximumFractionDigits:1}).format(metric(value));
const reportTime=(days)=>new Date(Date.now()-days*86400000).toISOString().slice(0,10);
const reportCache=new Map();
async function reportState(key,setup,load) {
    if (setup) return {setup};
    const saved=reportCache.get(key);
    if (saved && saved.expires>Date.now()) return {data:saved.data};
    try {
        const data=await load();
        if (reportCache.size>=12) reportCache.clear();
        reportCache.set(key,{data,expires:Date.now()+300000});
        return {data};
    } catch (error) { return {error:error instanceof ReportFailure ? error.message : 'The report could not be loaded. Try again shortly.'}; }
}
async function analyticsReport(env) {
    const property=String(env.SBA_GA4_PROPERTY_ID||'').trim();
    const setup=!/^\d{1,20}$/.test(property) ? 'Set SBA_GA4_PROPERTY_ID to your numeric GA4 property ID.' :
        !env.SBA_GOOGLE_SERVICE_ACCOUNT_JSON ? 'Add SBA_GOOGLE_SERVICE_ACCOUNT_JSON and grant that service account Viewer access in GA4.' : '';
    return reportState('ga:'+property,setup,async()=>{
        const token=await googleReportToken(env,googleScopes.ga);
        const endpoint='https://analyticsdata.googleapis.com/v1beta/properties/'+property+':runReport';
        const dateRanges=[{startDate:'28daysAgo',endDate:'yesterday'}];
        const query=(metrics,dimension)=>authorizedReport(endpoint,token,{
            dateRanges,metrics:metrics.map(name=>({name})),
            dimensionFilter:{filter:{fieldName:'hostName',stringFilter:{matchType:'EXACT',value:new URL(siteOrigin).hostname}}},
            ...(dimension ? {dimensions:[{name:dimension}],limit:10,orderBys:[{metric:{metricName:metrics[0]},desc:true}]} : {})
        },'Google Analytics');
        const [summary,channels,pages]=await Promise.all([
            query(['activeUsers','sessions','screenPageViews','engagementRate']),
            query(['sessions','activeUsers'],'sessionDefaultChannelGroup'),
            query(['screenPageViews','activeUsers'],'pagePath')
        ]);
        const values=row=>(row?.metricValues||[]).map(item=>metric(item.value));
        const breakdown=report=>(report.rows||[]).map(row=>({name:row.dimensionValues?.[0]?.value||'Unknown',values:values(row)}));
        return {totals:values(summary.rows?.[0]),channels:breakdown(channels),pages:breakdown(pages)};
    });
}
async function searchConsoleReport(env) {
    const site=String(env.SBA_GSC_PROPERTY||siteOrigin+'/').trim();
    const setup=!env.SBA_GOOGLE_SERVICE_ACCOUNT_JSON ?
        'Add SBA_GOOGLE_SERVICE_ACCOUNT_JSON and give that service account access to the verified Search Console property.' :
        !(/^(https:\/\/[^\s]+\/|sc-domain:[a-z0-9.-]+)$/i.test(site)) ? 'Use the exact Search Console property name in SBA_GSC_PROPERTY.' : '';
    return reportState('gsc:'+site,setup,async()=>{
        const token=await googleReportToken(env,googleScopes.gsc);
        const url='https://www.googleapis.com/webmasters/v3/sites/'+encodeURIComponent(site)+'/searchAnalytics/query';
        const dates={startDate:reportTime(30),endDate:reportTime(3)};
        const query=dimension=>authorizedReport(url,token,{...dates,type:'web',dataState:'final',
            dimensions:dimension?[dimension]:[],rowLimit:dimension?15:1,
            ...(site.startsWith('sc-domain:') ? {dimensionFilterGroups:[{filters:[{dimension:'page',
                operator:'includingRegex',expression:'^https://sba[.]tsols[.]co[.]ke/'}]}]} : {})},'Google Search Console');
        const [summary,keywords,pages]=await Promise.all([query(),query('query'),query('page')]);
        const shape=row=>({name:row?.keys?.[0]||'',clicks:metric(row?.clicks),impressions:metric(row?.impressions),
            ctr:metric(row?.ctr),position:metric(row?.position)});
        return {dates,totals:shape(summary.rows?.[0]),keywords:(keywords.rows||[]).map(shape),pages:(pages.rows||[]).map(shape)};
    });
}
function bingDate(value) {
    const epoch=String(value||'').match(/^\/Date\((\d+)(?:[+-]\d+)?\)\/$/)?.[1];
    const date=new Date(epoch ? Number(epoch) : value);
    return Number.isFinite(+date) ? date.toISOString().slice(0,10) : '';
}
async function bingReport(env) {
    const site=String(env.SBA_BING_SITE_URL||siteOrigin+'/').trim();
    const key=String(env.SBA_BING_WEBMASTER_KEY||'').trim();
    const setup=!key ? 'Add SBA_BING_WEBMASTER_KEY for the verified Bing Webmaster Tools site.' :
        !/^https:\/\/[^\s]+\/$/.test(site) ? 'Use the exact verified site URL in SBA_BING_SITE_URL.' : '';
    return reportState('bing:'+site,setup,async()=>{
        async function call(method) {
            const url=new URL('https://ssl.bing.com/webmaster/api.svc/json/'+method);
            url.search=new URLSearchParams({siteUrl:site,apikey:key});
            const data=await reportJson(url,{headers:{Accept:'application/json'}},'Bing Webmaster Tools');
            if (!Array.isArray(data?.d)) throw new ReportFailure('Bing did not return report rows. Check the verified site URL and API key.');
            return data.d;
        }
        const [traffic,queries]=await Promise.all([call('GetRankAndTrafficStats'),call('GetQueryStats')]);
        const dates={startDate:reportTime(28),endDate:reportTime(1)};
        const within=row=>{const day=bingDate(row.Date);return day>=dates.startDate&&day<=dates.endDate;};
        const totals=traffic.filter(within).reduce((sum,row)=>({clicks:sum.clicks+metric(row.Clicks),
            impressions:sum.impressions+metric(row.Impressions)}),{clicks:0,impressions:0});
        const terms=new Map();
        for (const row of queries.filter(within)) {
            if (!row.Query) continue;
            const saved=terms.get(row.Query)||{name:row.Query,clicks:0,impressions:0,weightedPosition:0};
            const impressions=metric(row.Impressions);
            saved.clicks+=metric(row.Clicks);
            saved.impressions+=impressions;
            saved.weightedPosition+=impressions*metric(row.AvgImpressionPosition);
            terms.set(row.Query,saved);
        }
        const keywords=[...terms.values()].sort((a,b)=>b.clicks-a.clicks||b.impressions-a.impressions).slice(0,15)
            .map(row=>({...row,ctr:ratio(row.clicks,row.impressions),position:ratio(row.weightedPosition,row.impressions)}));
        return {dates,totals:{...totals,ctr:ratio(totals.clicks,totals.impressions)},keywords};
    });
}
function reportBanner(name,state) {
    return state.setup||state.error ? `<section class="admin-panel"><h2>${h(name)}</h2>
        <p class="admin-empty">${h(state.setup||state.error)}</p></section>` : '';
}
function reportMetric(label,value) {
    return `<div class="admin-kpi"><span>${h(label)}</span><strong>${value}</strong></div>`;
}
function googleAnalyticsPanels(state,tracked) {
    const note=tracked ? 'Analytics tagging is configured for consenting visitors.' : 'Set SBA_GA4_MEASUREMENT_ID to enable Google Analytics collection.';
    const title=`<section class="section"><h2>Google Analytics 4</h2><p class="muted">${note}</p></section>`;
    if (!state.data) return title+reportBanner('GA4 reporting',state);
    const {totals,channels,pages}=state.data;
    return title+`<p class="muted">Last 28 completed days in your GA4 property.</p><div class="admin-kpi-grid">
        ${reportMetric('Active users',number(totals[0]))}${reportMetric('Sessions',number(totals[1]))}
        ${reportMetric('Page views',number(totals[2]))}${reportMetric('Engagement rate',percentage(totals[3]))}</div>
        <div class="admin-grid">${adminTable('Acquisition channels',['Channel','Sessions','Active users'],
            channels.map(row=>[h(row.name),number(row.values[0]),number(row.values[1])]),'No acquisition data for this period.')}
        ${adminTable('Most viewed pages',['Path','Views','Active users'],
            pages.map(row=>[h(row.name),number(row.values[0]),number(row.values[1])]),'No page data for this period.')}</div>`;
}
function searchReportPanels(provider,state) {
    if (!state.data) return reportBanner(provider,state);
    const {dates,totals,keywords,pages}=state.data;
    const dataRows=items=>items.map(row=>[h(row.name),number(row.clicks),number(row.impressions),percentage(row.ctr),
        row.impressions ? average(row.position) : '—']);
    return `<section class="section"><h2>${provider}</h2><p class="muted">${dates.startDate} to ${dates.endDate}
        ${provider==='Bing Webmaster Tools' ? ' · Keyword figures follow Bing’s reporting buckets.' : ' · Google web search'}</p>
        <div class="admin-kpi-grid">${reportMetric('Clicks',number(totals.clicks))}
        ${reportMetric('Search impressions',number(totals.impressions))}${reportMetric('Click-through rate',percentage(totals.ctr))}
        ${totals.position!==undefined ? reportMetric('Average position',totals.impressions?average(totals.position):'—') : ''}</div>
        ${adminTable('Top search queries',['Query','Clicks','Impressions','CTR','Avg. position'],
            dataRows(keywords),'No queries are available for this period.')}
        ${pages ? '<div class="section">'+adminTable('Top search pages',['Page','Clicks','Impressions','CTR','Avg. position'],
            dataRows(pages),'No search pages are available for this period.')+'</div>' : ''}</section>`;
}
