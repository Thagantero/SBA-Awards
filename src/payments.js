const paymentValue = (env,key) => String(env[key] ?? '').trim();
function paymentOrigin(value) {
    try {
        const url=new URL(value);
        return url.protocol==='https:' && !url.username && !url.password &&
            url.pathname==='/' && !url.search && !url.hash ? url.origin : null;
    } catch { return null; }
}
function paymentConfig(env) {
    const key=paymentValue(env,'SBA_MPESA_KEY'), secret=paymentValue(env,'SBA_MPESA_SECRET');
    const shortcode=paymentValue(env,'SBA_MPESA_SHORTCODE'), till=paymentValue(env,'SBA_MPESA_TILL');
    const passkey=paymentValue(env,'SBA_MPESA_PASSKEY'), callback=paymentValue(env,'SBA_MPESA_CALLBACK_TOKEN');
    const origin=paymentOrigin(paymentValue(env,'SBA_PUBLIC_ORIGIN'));
    const environment=paymentValue(env,'SBA_MPESA_ENV') || 'sandbox';
    if (!(key && secret && passkey && /^\d{5,10}$/.test(shortcode) && /^\d{5,10}$/.test(till) &&
        /^[a-f0-9]{32,128}$/i.test(callback) && origin && ['production','sandbox'].includes(environment))) return null;
    return {key,secret,shortcode,till,passkey,callback,origin,environment,base:environment==='production'
        ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke'};
}
function phoneNumber(value) {
    if (typeof value !== 'string' || !/^[+0-9 ()-]{9,24}$/.test(value)) return null;
    const digits = value.replace(/\D/g, '').replace(/^0(?=[17])/, '254');
    return /^254[17]\d{8}$/.test(digits) ? digits : null;
}
const pendingOrder = (db, voter) => first(db,
    "SELECT * FROM orders WHERE voter=? AND status IN ('starting','pending','unknown')", voter);
const userOrder = (db, voter, id) => first(db, 'SELECT * FROM orders WHERE voter=? AND id=?', voter,id);

async function createOrder(db, voter, kind, units, picks, amount) {
    const day = dayNow();
    const quote = kind === 'topup' ? { checkout: units, total: units * votePrice, free: 0, paid: 0 } :
        voteQuote(picks.length, await creditsFor(db, voter, day));
    if (!quote.checkout || (kind === 'ballot' && quote.total !== amount)) return null;
    const id = random();
    const selected = JSON.stringify(picks.map(row => row.id));
    const result = await write(db, `INSERT INTO orders (id,voter,kind,units,amount,day,free,paid,picks)
        SELECT ?,v.id,?,?,?,?,?,?,? FROM voters v WHERE v.id=?
        AND NOT EXISTS (SELECT 1 FROM orders WHERE voter=v.id AND status IN ('starting','pending','unknown'))
        AND (?='topup' OR (EXISTS (SELECT 1 FROM voting_rounds WHERE closed_at IS NULL AND opened_at<=unixepoch()) AND v.credits>=?
            AND (?=0 OR NOT EXISTS (SELECT 1 FROM daily_vote_claims WHERE voter=v.id AND day=?))
            AND NOT EXISTS (SELECT 1 FROM json_each(?) j LEFT JOIN nominations n ON n.id=j.value
                WHERE n.id IS NULL OR n.status<>'approved')))`,
        id,kind,quote.checkout,quote.total,day,quote.free,quote.paid,selected,voter,
        kind,quote.paid,quote.free,day,selected);
    return result.meta.changes ? { id, amount: quote.total } : null;
}
function credentials(config) {
    const timestamp = new Date(Date.now() + 3 * 3600_000).toISOString().slice(0, 19).replace(/\D/g, '');
    return { BusinessShortCode: config.shortcode, Timestamp: timestamp,
        Password: btoa(config.shortcode + config.passkey + timestamp) };
}
function diagnosticCode(value) {
    const code=String(value ?? '');
    return /^(?:\d{1,8}(?:\.\d{1,8}){0,3}|invalid_client|invalid_token|unauthorized_client|invalid_request)$/i.test(code)
        ? code : null;
}
class DarajaFailure extends Error {
    constructor(message,status=null,code=null,reason='') {
        super(message);
        this.status=status;
        this.code=diagnosticCode(code);
        this.reason=reason;
    }
}
async function darajaResponse(response) {
    const body=await response.text();
    try { return JSON.parse(body); } catch {
        const reason=/invalid authentication|invalid client|invalid credentials/i.test(body) ? 'authentication-rejected' :
            /access denied|request blocked|web application firewall|cloudflare ray id|bad request - invalid hostname/i.test(body) ? 'gateway-block' :
                'non-json';
        throw new DarajaFailure('Daraja did not return JSON',response.status,null,reason);
    }
}
function darajaErrorCode(body) {
    return body?.errorCode ?? body?.error_code ?? body?.fault?.detail?.errorcode ??
        body?.fault?.detail?.errorCode ?? body?.error;
}
async function accessToken(config) {
    const url=new URL(config.base + '/oauth/v1/generate');
    url.searchParams.set('grant_type','client_credentials');
    const response=await fetch(url,{
        method:'GET',redirect:'manual',signal:AbortSignal.timeout(12000),
        headers:{Accept:'application/json',Authorization:'Basic '+btoa(config.key+':'+config.secret)}
    });
    if (response.status>=300 && response.status<400) {
        throw new DarajaFailure('Unexpected redirect from Daraja',response.status,null,'redirect');
    }
    const data=await darajaResponse(response);
    if (!response.ok || typeof data?.access_token!=='string' || !data.access_token) {
        const message=[data?.errorMessage,data?.error_description,data?.fault?.faultstring]
            .filter(text=>typeof text==='string').join(' ');
        const reason=/invalid authentication|invalid client|invalid credentials/i.test(message) ?
            'authentication-rejected' : 'json-error';
        throw new DarajaFailure('Daraja authorization failed',response.status,darajaErrorCode(data),reason);
    }
    return data.access_token;
}
async function daraja(config, path, access, payload) {
    const response = await fetch(config.base + path, { method: 'POST', signal: AbortSignal.timeout(12000),
        headers: { Authorization: 'Bearer ' + access, 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const result=await darajaResponse(response);
    return {...result,httpStatus:response.status};
}
function requestStk(config, access, amount, phone, reference, description) {
    return daraja(config,'/mpesa/stkpush/v1/processrequest',access,{
        ...credentials(config),TransactionType:'CustomerBuyGoodsOnline',Amount:amount,
        PartyA:phone,PartyB:config.till,PhoneNumber:phone,
        CallBackURL:config.origin+'/mpesa/callback/'+config.callback,
        AccountReference:reference,TransactionDesc:description
    });
}
async function promptPayment(db, config, order, phone) {
    let access;
    try { access = await accessToken(config); }
    catch {
        await write(db, "UPDATE orders SET status='failed' WHERE id=?", order.id);
        return;
    }
    try {
        const description = order.kind==='ticket' ? 'SBA Awards tickets' : 'SBA Awards votes';
        const result = await requestStk(config,access,order.amount,phone,'SBA'+order.id.slice(0,8),description);
        if (String(result.ResponseCode) === '0' && result.CheckoutRequestID && result.MerchantRequestID) {
            await write(db, "UPDATE orders SET status='pending', merchant=?, checkout=? WHERE id=?",
                result.MerchantRequestID,result.CheckoutRequestID,order.id);
        } else if ((result.ResponseCode !== undefined && String(result.ResponseCode) !== '0') ||
            (result.errorCode && /^400\./.test(String(result.errorCode)))) {
            await write(db, "UPDATE orders SET status='failed' WHERE id=?", order.id);
        } else await write(db, "UPDATE orders SET status='unknown' WHERE id=?", order.id);
    } catch {
        await write(db, "UPDATE orders SET status='unknown' WHERE id=? AND status='starting'", order.id);
    }
}
const rejected = new Set(['1','17','1032','1037','2001']);
async function queryPayment(config,order) {
    return daraja(config,'/mpesa/stkpushquery/v1/query',await accessToken(config),
        { ...credentials(config),CheckoutRequestID:order.checkout });
}
function matchingPaymentResult(result,order) {
    return result?.CheckoutRequestID===order.checkout && result.MerchantRequestID===order.merchant &&
        String(result.ResponseCode)==='0' && result.ResultCode!==undefined;
}
async function checkPayment(db, config, order) {
    if (!config || order?.status !== 'pending') return;
    let result;
    try { result=await queryPayment(config,order); } catch { return; }
    if (!matchingPaymentResult(result,order)) return;
    const code = String(result.ResultCode);
    if (code === '0' || rejected.has(code)) {
        await write(db, "UPDATE orders SET status=? WHERE id=? AND status='pending'", code === '0' ? 'paid' : 'failed', order.id);
    }
}
