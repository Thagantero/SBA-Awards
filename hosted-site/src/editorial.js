const awardDescriptions = [
    ['Recognises a founder whose leadership, vision and execution have shaped an outstanding business.',
        'Founders and co-founders of active African startups and businesses who can demonstrate their own role in building the venture.'],
    ['Recognises organisations using practical, lasting solutions to address social needs.',
        'Registered nonprofits, community organisations, foundations and mission-led social enterprises operating in Africa.'],
    ['Recognises businesses using technology to solve problems at scale or open new markets.',
        'Software, hardware, AI, cybersecurity, clean-tech and other product-led technology ventures.'],
    ['Recognises businesses creating jobs, exports and commercial value through African creativity.',
        'Design, fashion, architecture, film production, animation, games, crafts and other creative product or services businesses.'],
    ['Recognises businesses strengthening journalism, information access and public storytelling.',
        'Newsrooms, newspapers, magazines, digital publishers, media production houses and independent press or broadcast ventures.'],
    ['Recognises creative businesses developing talent and reaching new audiences through music and the arts.',
        'Music labels and platforms, arts organisations, galleries, performing arts businesses and artist-led ventures.'],
    ['Recognises teams producing distinctive live or hybrid experiences that bring audiences together.',
        'Event organisers, festivals, conference producers, venues and businesses whose main product is an event experience.'],
    ['Recognises businesses improving agriculture and the food value chain.',
        'Farm enterprises, agritech platforms, food processors, cold-chain operators and agriculture input, logistics or market-access ventures.'],
    ['Recognises innovations that make financial services more accessible, useful or trusted.',
        'Payments, banking technology, lending, insurance technology, savings, investment and financial infrastructure businesses.'],
    ['Recognises businesses improving access to care, health outcomes or everyday well-being.',
        'Health-tech businesses, providers, diagnostic ventures, medical device companies and evidence-informed wellness ventures.'],
    ['Recognises businesses making it easier to shop, sell and distribute goods.',
        'Retailers, e-commerce marketplaces, commerce technology platforms and ventures specialising in fulfilment or last-mile delivery.'],
    ['Recognises businesses that build commercial value alongside measurable environmental benefits.',
        'Circular economy, renewable energy, sustainable production, conservation and resource-efficiency ventures.']
];
const processSteps = [
    ['Nominate a business','Founders and supporters submit a nomination in a category that fits the business.'],
    ['Verify and list nominees','The awards team reviews nominations. Verified, approved nominees appear in the official nominee directory.'],
    ['Open public voting','The public votes once voting opens. Public votes account for up to 70 points out of 100 in each category.'],
    ['Independent panel review','After voting closes, a professional panel assesses the nominees and awards up to 30 points. The two scores are combined to determine the final ranking.'],
    ['Celebrate at the gala','The red-carpet gala honours the winners with trophies. First and second runners-up receive certificates. There are no cash prizes.']
];
function processPage(compact=false) {
    const title=compact ? '<div class="section-heading"><div><p class="eyebrow">From nomination to gala</p><h2>How the awards work</h2></div>'+
        link('/process','See the full process','button secondary')+'</div>' :
        heading('How the awards work','A clear path from nomination to the red carpet, with published voting and judging weights.');
    const list=`<ol class="process-list">${processSteps.map(([name,description])=>
        `<li><h3>${h(name)}</h3><p>${h(description)}</p></li>`).join('')}</ol>`;
    return compact ? `<section class="process-preview section">${title}${list}</section>` : `<section class="editorial-lead">
        ${title}${list}</section>
        <section class="scoring card form-card"><p class="eyebrow">A transparent score</p><h2>70% public vote. 30% panel review.</h2>
        <p>The nominee with the most public votes in a category earns 70 public-vote points. Other nominees receive a proportional score.
        Where there are no public votes, all nominees receive zero public-vote points. The panel’s agreed score out of 100 is converted to up to 30 points.</p>
        <p>The combined score determines the ranking. If totals tie, the higher panel score ranks first.
        A tie for a winning or runner-up place still outstanding must be resolved by the panel before results are published.</p>
        ${link('/categories','Explore award categories','button secondary')}</section>`;
}
function categoriesPage(results) {
    return `<section class="editorial-lead">${heading('Award categories','Twelve awards recognise the founders and businesses shaping Africa’s future.')}
        <div class="category-directory">${categories.map((category,index)=>{
            const placements=results.filter(result=>result.category===category);
            return `<article class="card category-card ${index===0?'category-prime':''}">
                ${index===0?'<p class="eyebrow">Prime award</p>':''}<h2>${h(category)}</h2>
                <p>${h(awardDescriptions[index][0])}</p>
                <div class="eligibility"><h3>Eligible businesses</h3><p>${h(awardDescriptions[index][1])}</p></div>
                ${placements.length ? `<dl class="placement-list">${placements.map(item=>`<dt>${['','Winner','First runner-up','Second runner-up'][item.place]}</dt>
                    <dd>${h(item.company)} <small>${item.total.toFixed(2)} points</small></dd>`).join('')}</dl>` : ''}</article>`;
        }).join('')}</div><div class="actions section">${link('/nominate','Submit a nomination','button')}${link('/process','How the awards work','button secondary')}</div>
    </section>`;
}
function campaignPoster(nominee) {
    const founder=JSON.parse(nominee.details).Name;
    const name=nominee.category==='Founder of the Year' && founder ? founder : nominee.company;
    const subtitle=nominee.category==='Founder of the Year' ? nominee.company : founder;
    return `<section class="poster-page">${heading('Campaign poster','Share your nomination and show supporters how to vote.')}
        <div class="poster-controls actions"><button id="download-poster" disabled>Download PNG</button>
        ${link('/process','How voting works','button secondary')}</div>
        <img id="poster-logo" src="/logo.png" width="144" height="144" alt="" hidden>
        <canvas id="campaign-poster" width="1080" height="1350" data-name="${h(name)}"
            data-category="${h(nominee.category)}" data-subtitle="${h(subtitle && subtitle!==name ? subtitle : '')}" role="img"
            aria-label="${h(name)} · ${h(nominee.category)} · Vote at sba.tsols.co.ke/ballot"></canvas>
        <p class="muted" id="poster-status" role="status"></p><script src="/poster.js" defer></script></section>`;
}
