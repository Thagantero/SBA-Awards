const eventDate = (day, time) => new Intl.DateTimeFormat('en-KE', {
    dateStyle:'long', timeStyle:time ? 'short' : undefined, timeZone:'Africa/Nairobi'
}).format(new Date(day + 'T' + (time || '12:00') + ':00+03:00'));
const priceLabel = price => price ? 'KES ' + Number(price).toLocaleString('en-KE') : 'Free admission';
const lines = value => h(value).split(/\n\s*\n/).map(part => `<p>${part.replaceAll('\n','<br>')}</p>`).join('');
const programImage = (type, row) => row.image ? `<img class="story-image" src="/media/${type}/${row.id}" alt="" loading="lazy">` : '';

function eventsPage(events) {
    return heading('Events','Join founders, industry leaders, and the wider SBA Awards community.') +
        (events.length ? `<div class="cards">${events.map(event => `<article class="card story">
        ${programImage('event',event)}<div class="story-body"><p class="eyebrow">${eventDate(event.day)}</p>
        <h2>${link('/events/' + event.id,h(event.title))}</h2><p>${h(event.location)}</p>
        <div class="actions"><span class="price">${priceLabel(event.price)}</span>${link('/events/' + event.id,'Event details','button')}</div>
        </div></article>`).join('')}</div>` : `<div class="empty">Upcoming events will appear here once announced.</div>`);
}
function eventPage(session,event,configured) {
    const ticketForm = !session.voter ? link('/login','Sign in to get tickets','button') :
        event.price && !configured ? '<p class="notice">Online ticket payments are unavailable.</p>' :
        form('/tickets/book',session.csrf,`<input type="hidden" name="event" value="${event.id}">
        ${field('guest','Guest name','required maxlength="150"')}
        ${field('email','Email','required maxlength="254"','email')}
        ${field('quantity',event.price ? 'Tickets at ' + priceLabel(event.price) + ' each' : 'Number of tickets',
            'required min="1" max="10" value="1"','number')}
        ${event.price ? field('phone','M-PESA mobile number','required maxlength="24" autocomplete="tel"','tel') : ''}
        ${button(event.price ? 'Pay with M-PESA' : 'Reserve tickets')}`);
    return `${link('/events','All events','back-link')}${heading(h(event.title))}
        ${programImage('event',event)}<div class="split story-split"><article>
        <p class="eyebrow">${eventDate(event.day,event.time)} EAT</p><p>${h(event.location)}
        ${event.map_url ? ` · ${link(h(event.map_url),'Map and directions')}` : ''}</p>
        <dl>${event.theme ? `<dt>Theme</dt><dd>${h(event.theme)}</dd>` : ''}
        ${event.dress_code ? `<dt>Dress code</dt><dd>${h(event.dress_code)}</dd>` : ''}</dl>
        ${lines(event.description)}</article><section class="card form-card"><h2>Tickets</h2>
        <p class="price">${priceLabel(event.price)}</p>${ticketForm}</section></div>`;
}
function journalPage(posts) {
    return heading('Journal','News and stories from the SBA Awards community.') +
        (posts.length ? `<div class="cards">${posts.map(post => `<article class="card story">
        ${programImage('post',post)}<div class="story-body"><p class="eyebrow">${h(post.category)}</p>
        <h2>${link('/journal/' + post.id,h(post.title))}</h2><p>${h(post.excerpt)}</p></div></article>`).join('')}</div>`
        : '<p class="empty">Published stories will appear here.</p>');
}
function articlePage(post) {
    return `<article class="narrow wide">${link('/journal','All stories','back-link')}
        <p class="eyebrow">${h(post.category)}</p>${heading(h(post.title),h(post.excerpt))}
        ${programImage('post',post)}${lines(post.body)}
        ${post.tags ? `<p class="muted">${h(post.tags)}</p>` : ''}</article>`;
}
function ticketsPage(tickets) {
    return heading('Your tickets','Show the ticket reference at check-in.') + (tickets.length
        ? tickets.map(ticket => `<article class="card admin-item">
            <h2>${h(ticket.title)}</h2><p>${eventDate(ticket.day,ticket.time)} EAT · ${h(ticket.location)}</p>
            <p>${ticket.quantity} ${ticket.quantity===1 ? 'ticket' : 'tickets'} · ${h(ticket.guest)}</p>
            ${ticket.status==='confirmed' ? `<p>Reference <strong class="ticket-ref">${h(ticket.id.toUpperCase())}</strong></p>
            <p>${ticket.checked_in ? 'Checked in' : 'Confirmed'}</p>`
            : ticket.order_status==='failed' ? '<p>Payment unsuccessful.</p>' : link('/payments/' + ticket.order_id,'Check payment status')}
            </article>`).join('')
        : '<p class="empty">Your confirmed and pending reservations will appear here.</p>');
}
function programAdmin(session,events,posts,tickets, message='') {
    const eventFields = field('title','Event name','required maxlength="150"') +
        area('description','Description',5000) + `<div class="pair"><div>${field('day','Date','required','date')}</div>
        <div>${field('time','Time (EAT)','required','time')}</div></div>` +
        field('location','Venue','required maxlength="150"') + field('map_url','Map URL','maxlength="700"','url') +
        field('dress_code','Dress code','maxlength="100"') + field('theme','Event theme','maxlength="100"') +
        field('price','Ticket price in KES','required min="0" max="100000" value="0"','number') +
        field('image','Event image','accept="image/jpeg,image/png,image/webp"','file') + button('Publish event');
    const postFields = field('title','Article title','required maxlength="150"') +
        field('category','Category','required maxlength="75"') + field('tags','Tags','maxlength="150"') +
        area('excerpt','Excerpt',350) + area('body','Article text',20000) +
        field('image','Featured image','accept="image/jpeg,image/png,image/webp"','file') + button('Save draft');
    return heading('Events, journal and tickets') + link('/admin','Awards administration','back-link') + flash(message) +
        `<div class="cards"><section class="card form-card"><h2>Create an event</h2>
        ${form('/admin/event',session.csrf,eventFields,true)}</section>
        <section class="card form-card"><h2>Write a story</h2>${form('/admin/post',session.csrf,postFields,true)}</section></div>
        <section class="section"><h2>Events</h2>${contentList(session,events,'event')}</section>
        <section class="section"><h2>Stories</h2>${contentList(session,posts,'post')}</section>
        <section class="section card form-card"><h2>Verify and check in tickets</h2>
        ${form('/admin/ticket/check',session.csrf,field('reference','Ticket reference','required minlength="16" maxlength="16"') + button('Find ticket'))}
        ${tickets.map(ticket => `<article class="admin-item"><h3>${h(ticket.title)}</h3><p>${h(ticket.guest)} · ${ticket.quantity} tickets</p>
        ${ticket.checked_in ? '<p>Already checked in.</p>' : form('/admin/ticket/checkin',session.csrf,
            `<input type="hidden" name="reference" value="${h(ticket.id)}">` + button('Confirm attendance'))}</article>`).join('')}</section>`;
}
function contentList(session,items,type) {
    return items.length ? items.map(item => `<article class="card admin-item"><strong>${h(item.title)}</strong> ·
        ${item.published ? 'Published' : type==='post' ? 'Draft' : 'Hidden'}
        ${form('/admin/content/status',session.csrf,`<input type="hidden" name="type" value="${type}">
        <input type="hidden" name="id" value="${item.id}"><button name="published" value="${item.published ? 0 : 1}">
        ${item.published ? 'Hide' : 'Publish'}</button>`)}</article>`).join('') : '<p class="empty">Nothing added yet.</p>';
}

async function optionalImage(bucket,file,folder,save) {
    if (!(file instanceof File) || !file.size) return save(null);
    const contentType = await attachment(file);
    const key = folder + '/' + random();
    await bucket.put(key, await file.arrayBuffer(), {httpMetadata:{contentType}});
    try { return await save(key); }
    catch (error) { await bucket.delete(key); throw error; }
}
async function saveEvent(db,bucket,values) {
    const day=input(values,'day',10), time=input(values,'time',5);
    const when=new Date(day + 'T' + time + ':00Z');
    if (!/^\d{4}-\d{2}-\d{2}$/.test(day) || !/^([01]\d|2[0-3]):[0-5]\d$/.test(time) ||
        !Number.isFinite(+when) || when.toISOString().slice(0,10)!==day) throw new InvalidForm('Enter a valid event date and time.');
    const amount=input(values,'price',6);
    if (!/^\d+$/.test(amount) || Number(amount)>100000) throw new InvalidForm('Enter a valid ticket price in KES.');
    const map=input(values,'map_url',700,false);
    if (map && !/^https?:\/\/[^\s]+$/i.test(map)) throw new InvalidForm('Enter a valid map URL.');
    const fields=[input(values,'title',150),input(values,'description',5000),day,time,
        input(values,'location',150),map,input(values,'dress_code',100,false),input(values,'theme',100,false),Number(amount)];
    return optionalImage(bucket,values.image,'event',key => write(db,`INSERT INTO events
        (title,description,day,time,location,map_url,dress_code,theme,price,image) VALUES(?,?,?,?,?,?,?,?,?,?)`,...fields,key));
}
async function savePost(db,bucket,values) {
    const fields=[input(values,'title',150),input(values,'excerpt',350),input(values,'body',20000),
        input(values,'category',75),input(values,'tags',150,false)];
    return optionalImage(bucket,values.image,'post',key => write(db,`INSERT INTO posts(title,excerpt,body,category,tags,image)
        VALUES(?,?,?,?,?,?)`,...fields,key));
}
async function bookTickets(db,config,voter,values) {
    const id=Number(values.event), quantity=Number(values.quantity);
    if (!Number.isSafeInteger(id) || !Number.isInteger(quantity) || quantity<1 || quantity>10) throw new InvalidForm('Choose between 1 and 10 tickets.');
    const event=await first(db,'SELECT * FROM events WHERE id=? AND published=1',id);
    if (!event || new Date(event.day+'T'+event.time+':00+03:00') <= new Date()) throw new InvalidForm('This event is unavailable for booking.');
    const guest=input(values,'guest',150), address=email(values,'email');
    const reference=random().slice(0,16), order=random(), amount=event.price*quantity;
    if (!amount) {
        await write(db,`INSERT INTO ticket_bookings(id,voter,event,quantity,guest,email,status)
            VALUES(?,?,?,?,?,?,'confirmed')`,reference,voter,id,quantity,guest,address);
        return '/tickets';
    }
    if (!config) throw new InvalidForm('Online ticket payments are unavailable.');
    const phone=phoneNumber(values.phone);
    if (!phone) throw new InvalidForm('Enter a valid Kenyan mobile number.');
    const active=await pendingOrder(db,voter);
    if (active) return '/payments/'+active.id;
    const result=await db.batch([
        statement(db,`INSERT INTO orders(id,voter,kind,units,amount,day,free,paid,picks)
            SELECT ?,?,'ticket',?,?,?,0,0,'[]' WHERE NOT EXISTS
            (SELECT 1 FROM orders WHERE voter=? AND status IN ('starting','pending','unknown'))`,
            order,voter,quantity,amount,dayNow(),voter),
        statement(db,`INSERT INTO ticket_bookings(id,voter,event,quantity,guest,email,order_id)
            SELECT ?,?,?,?,?,?,id FROM orders WHERE id=?`,reference,voter,id,quantity,guest,address,order)
    ]);
    if (!result[0].meta.changes) return '/events/'+id;
    await promptPayment(db,config,{id:order,amount,kind:'ticket'},phone);
    return '/payments/'+order;
}

function accountPage(session,voter,credits,message='',round) {
    return `<section class="narrow">${heading(voter.role === 'nominee' ? 'Account settings' : 'Voter dashboard')}${flash(message)}
        <div class="card form-card"><h2>${h(voter.first)} ${h(voter.last)}</h2>
        <p>${h(voter.username)} · ${h(voter.email)}</p><p>${credits.free} free today · ${credits.paid} purchased</p>
        <div class="actions">${link('/ballot',roundIsOpen(round) ? 'Open ballot' : 'Voting schedule','button')} ${link('/tickets','Your tickets','button secondary')} ${link('/credits','Voting credits','button secondary')} ${link('/nominee/dashboard','Your nominations','button secondary')}</div>
        </div><section class="section card form-card"><h2>Change password</h2>
        ${form('/account/password',session.csrf,
            field('current','Current password','required maxlength="256" autocomplete="current-password"','password') +
            field('replacement','New password','required minlength="8" maxlength="256" autocomplete="new-password"','password') +
            button('Update password'))}</section></section>`;
}

function nomineeDashboard(session,entries) {
    return `<section class="dashboard">${heading('Nominee dashboard','Submit a business and follow the status of your nominations.')}
        <div class="actions">${link('/nominate','New nomination','button')} ${link('/account','Account settings','button secondary')}</div>
        <section class="section"><h2>Your nominations</h2>${entries.length
            ? `<div class="cards">${entries.map(entry => `<article class="card form-card">
                <p class="eyebrow">${h(entry.category)}</p><h3>${h(entry.company)}</h3>
                <p>${h(entry.country)}</p><p class="status-label">${h(entry.status)}</p>
                ${entry.pairing==='approved' ? `<p><strong>${entry.received} ${entry.received===1 ? 'vote' : 'votes'} received</strong></p>
                    ${entry.status==='approved' ? link('/nominees/'+entry.id+'/poster','Generate campaign poster','button secondary') : ''}
                    <img class="nominee-photo" src="/photo/${entry.id}" alt="Nominee profile photo" width="96" height="96">
                    ${form('/nominee/photo',session.csrf,`<input type="hidden" name="nomination" value="${entry.id}">`+
                        field('Image','Update profile photo','required accept="image/jpeg,image/png,image/webp,image/gif"','file')+button('Save photo'),true)}` :
                    `<p>${entry.pairing==='pending' ? 'Pairing request awaiting admin review.' : entry.pairing==='rejected' ? 'Pairing request declined.' :
                        'A verified account with the nominee email can request access.'}</p>`}</article>`).join('')}</div>`
            : '<p class="empty">You haven’t submitted a nomination from this account yet.</p>'}</section></section>`;
}
