const testAmount = 10;
const diagnosticFields = {
    SBA_MPESA_KEY: Boolean, SBA_MPESA_SECRET: Boolean, SBA_MPESA_PASSKEY: Boolean,
    SBA_MPESA_SHORTCODE: value => /^\d{5,10}$/.test(value),
    SBA_MPESA_TILL: value => /^\d{5,10}$/.test(value),
    SBA_MPESA_CALLBACK_TOKEN: value => /^[a-f0-9]{32,128}$/i.test(value),
    SBA_PUBLIC_ORIGIN: paymentOrigin,
    SBA_MPESA_ENV: value => ['sandbox','production'].includes(value)
};
function paymentSetupIssues(env) {
    return Object.entries(diagnosticFields).flatMap(([key, valid]) =>
        valid(paymentValue(env,key)) ? [] : [key+' '+(paymentValue(env,key) ? 'has an invalid format.' : 'is missing.')]);
}
function paymentFailureDetails(stage,code,status,reason='') {
    if (reason==='gateway-block') return 'The request was blocked by a gateway. Try a no-payment access-token request from your own device to compare.';
    if (reason==='redirect') return 'The access-token endpoint redirected unexpectedly. The site did not follow it or forward credentials.';
    if (reason==='non-json') return 'The endpoint returned a non-JSON error. Compare with an access-token test from your own device.';
    if (stage==='oauth' && (reason==='authentication-rejected'||code==='400.008.01')) {
        return 'The endpoint rejected Basic Auth. Compare this with the same live key pair in a direct access-token test.';
    }
    if (code==='404.001.03' || code==='404.001.04' || code==='invalid_token') {
        return 'Check that the app has M-PESA Express enabled and that the credentials match the selected environment.';
    }
    if (stage==='oauth' && (status===401 || code==='invalid_client' || code==='unauthorized_client')) {
        return 'Daraja rejected the Consumer Key or Consumer Secret for this environment.';
    }
    if (stage==='prompt' && code==='500.001.1001') {
        return 'Check that the M-PESA Express passkey is for the configured business shortcode, and that Safaricom enabled that shortcode for Buy Goods.';
    }
    if (code==='1032') return 'The request was canceled on the phone.';
    if (code==='1037') return 'The phone did not confirm the prompt in time.';
    if (status===429) return 'Daraja is rate limiting requests. Wait before checking again.';
    if (!status && !code) return 'No response was available. Check the connection first.';
    return '';
}
function providerDetails(stage,httpStatus,code,reason) {
    const context=[httpStatus ? 'HTTP '+httpStatus : '',code ? 'Daraja code '+code : ''].filter(Boolean).join(' · ');
    return [context,paymentFailureDetails(stage,code,httpStatus,reason)].filter(Boolean).join('. ');
}
function reportPaymentDiagnostic(stage,status,code,reason) {
    console.warn('M-PESA diagnostic',{stage,httpStatus:status||null,code:diagnosticCode(code),reason:reason||null});
}
const getPaymentTest = (db,id) => first(db,'SELECT * FROM mpesa_tests WHERE id=?',id);

async function startPaymentTest(db,config,phone) {
    const id=random();
    const reservation=await write(db,`INSERT INTO mpesa_tests(id,environment,status,stage)
        SELECT ?,?,'starting','oauth' WHERE NOT EXISTS (SELECT 1 FROM mpesa_tests WHERE created>unixepoch()-60)`,id,config.environment);
    if (!reservation.meta.changes) return null;
    let access;
    try { access=await accessToken(config); }
    catch (error) {
        reportPaymentDiagnostic('oauth',error.status,error.code,error.reason);
        await write(db,"UPDATE mpesa_tests SET status='failed',stage='oauth',http_status=?,code=? WHERE id=?",
            error.status??null,diagnosticCode(error.code),id);
        return id;
    }
    try {
        const result=await requestStk(config,access,testAmount,phone,'SBAT'+id.slice(0,8),'SBA payment test');
        const code=diagnosticCode(result.ResponseCode ?? result.errorCode);
        if (code==='0' && typeof result.CheckoutRequestID==='string' && typeof result.MerchantRequestID==='string' &&
            result.CheckoutRequestID && result.MerchantRequestID) {
            await write(db,"UPDATE mpesa_tests SET status='pending',stage='prompt',merchant=?,checkout=?,code=?,http_status=? WHERE id=?",
                result.MerchantRequestID,result.CheckoutRequestID,code,result.httpStatus,id);
        } else {
            const declined=Boolean(code && code!=='0');
            reportPaymentDiagnostic('prompt',result.httpStatus,code);
            await write(db,"UPDATE mpesa_tests SET status=?,stage='prompt',code=?,http_status=? WHERE id=?",
                declined ? 'failed' : 'unknown',code,result.httpStatus,id);
        }
    } catch (error) {
        reportPaymentDiagnostic('prompt',error.status,error.code);
        await write(db,"UPDATE mpesa_tests SET status='unknown',stage='prompt',http_status=?,code=? WHERE id=? AND status='starting'",
            error.status??null,diagnosticCode(error.code),id);
    }
    return id;
}
async function reconcilePaymentTest(db,config,test) {
    if (!config || test.status!=='pending' || test.environment!==config.environment) return;
    let result;
    try { result=await queryPayment(config,test); }
    catch (error) {
        reportPaymentDiagnostic('query',error.status,error.code);
        await write(db,"UPDATE mpesa_tests SET stage='query',http_status=?,code=? WHERE id=? AND status='pending'",
            error.status??null,diagnosticCode(error.code),test.id);
        return;
    }
    const matched=matchingPaymentResult(result,test);
    const code=diagnosticCode(matched ? result.ResultCode : result.errorCode ?? result.ResponseCode);
    const status=matched && code==='0' ? 'paid' : matched && rejected.has(code) ? 'failed' : 'pending';
    if (!matched) reportPaymentDiagnostic('query',result.httpStatus,code);
    await write(db,"UPDATE mpesa_tests SET status=?,stage='query',code=?,http_status=? WHERE id=? AND status='pending'",
        status,code,result.httpStatus,test.id);
}

function paymentTestPage(session,env,tests,notice='') {
    const issues=paymentSetupIssues(env);
    const production=paymentValue(env,'SBA_MPESA_ENV')==='production';
    return heading('M-PESA test console','Check your Buy Goods connection and test the payment prompt without opening voting.')+
        link('/admin','Awards administration','back-link')+flash(notice)+
        `<div class="cards"><section class="card form-card"><h2>Daraja connection</h2>
        <p>Mode <strong>${production ? 'Production · real payments' : 'Sandbox · test environment'}</strong></p>
        ${issues.length ? `<ul>${issues.map(issue=>`<li>${h(issue)}</li>`).join('')}</ul>` :
            form('/admin/payments/connection',session.csrf,button('Check connection'))}
        <p>This check requests a Daraja access token. It does not send a prompt or charge anyone.</p></section>
        <section class="card form-card"><h2>Test payment prompt</h2>
        <p>${production ? 'If approved on the phone, this sends a real KES 10 payment to the configured Till.' :
            'Use a phone number assigned to your Daraja sandbox. This test requests KES 10 in the sandbox.'}</p>
        <p>The test never issues voting credits, casts votes, or creates tickets.</p>
        ${issues.length ? '<p>Add or fix the settings in Sites before sending a prompt.</p>' :
            form('/admin/payments/start',session.csrf,
                field('phone','M-PESA mobile number','required maxlength="24" autocomplete="tel"','tel')+
                `<label class="payment-test-consent"><input type="checkbox" name="confirm" value="yes" required>
                ${production ? 'I understand this can charge KES 10 if I approve the prompt.' : 'Send a sandbox test prompt.'}</label>`+
                button(production ? 'Send KES 10 live test prompt' : 'Send sandbox test prompt'))}</section></div>
        <section class="section"><h2>Recent tests</h2>
        ${tests.length ? tests.map(test=>renderPaymentTest(session,test)).join('') : '<p class="empty">No payment tests yet.</p>'}</section>`;
}
function renderPaymentTest(session,test) {
    const descriptions={
        'starting':'Request in progress',
        'pending':'Awaiting confirmation',
        'paid':'Payment verified by Daraja',
        'failed':'Request failed or was declined',
        'unknown':'Prompt could not be confirmed'
    };
    const details={
        oauth:'Daraja authentication',
        prompt:'STK prompt',
        query:'Daraja transaction query'
    };
    const date=new Intl.DateTimeFormat('en-KE',{dateStyle:'medium',timeStyle:'short',timeZone:'Africa/Nairobi'})
        .format(new Date(test.created*1000));
    return `<article class="card admin-item" id="test-${test.id}"><strong>${descriptions[test.status]}</strong>
        <p>${h(date)} EAT · ${h(test.environment)} · Reference SBAT${test.id.slice(0,8)}</p>
        <p>${details[test.stage]} · Callback ${test.callback_at ? 'received' : 'not received yet'}</p>
        ${test.status!=='paid' && test.status!=='starting' && (test.status!=='pending' || test.stage==='query') ?
            `<p>${h(providerDetails(test.stage,test.http_status,test.code))}</p>` : ''}
        ${test.status==='pending' ? form('/admin/payments/check',session.csrf,
            `<input type="hidden" name="test" value="${test.id}">`+button('Check payment status')) : ''}</article>`;
}
