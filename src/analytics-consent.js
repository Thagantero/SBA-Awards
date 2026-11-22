const analytics = document.querySelector('#analytics-consent');
const preferences = document.querySelector('[data-analytics-preferences]');

if (analytics) {
    const storageKey='sba_optional_analytics';
    const optedOut=navigator.globalPrivacyControl===true || navigator.doNotTrack==='1';
    let started=false;
    function readChoice() {
        try { return localStorage.getItem(storageKey); } catch { return null; }
    }
    function saveChoice(choice) {
        try { localStorage.setItem(storageKey,choice); } catch {}
    }
    function startAnalytics() {
        if (optedOut) return;
        const id=analytics.dataset.measurement;
        window['ga-disable-'+id]=false;
        if (started) return;
        started=true;
        let referrer='';
        try {
            const source=new URL(document.referrer);
            if (source.origin!==location.origin && ['http:','https:'].includes(source.protocol)) referrer=source.origin+'/';
        } catch {}
        window.dataLayer=window.dataLayer||[];
        function gtag(){window.dataLayer.push(arguments);}
        gtag('js',new Date());
        gtag('config',id,{
            send_page_view:false,allow_google_signals:false,allow_ad_personalization_signals:false,
            page_location:location.origin+location.pathname,page_referrer:referrer
        });
        gtag('event','page_view',{
            page_location:location.origin+location.pathname,page_referrer:referrer,page_title:document.title
        });
        const script=document.createElement('script');
        script.async=true;
        script.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(id);
        document.head.append(script);
    }
    if (!optedOut) {
        const initial=readChoice();
        analytics.hidden=Boolean(initial);
        if (initial==='allow') startAnalytics();
        if (preferences) {
            preferences.hidden=false;
            preferences.addEventListener('click',()=>{
                analytics.hidden=!analytics.hidden;
                if (!analytics.hidden) analytics.querySelector('button')?.focus();
            });
        }
        analytics.addEventListener('click',event=>{
            const choice=event.target.closest('[data-analytics]')?.dataset.analytics;
            if (!choice) return;
            saveChoice(choice);
            analytics.hidden=true;
            if (choice==='allow') startAnalytics();
            else window['ga-disable-'+analytics.dataset.measurement]=true;
        });
    }
}
