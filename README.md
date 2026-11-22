# SBA Awards on Sites

This Worker uses D1 for voter accounts, nominations, votes, credits, sessions, messages and payment orders. Private R2 objects store photos and ownership proofs. The original Node app is a separate checkout. The hosted app starts with an empty database.

Run `npm run build` to generate `dist/server/index.js`. The first migration lives in `drizzle/0000_awards.sql`.

Configure `SBA_ADMIN_PASSWORD` as a runtime secret before using the awards administration page. Configure the following runtime values before testing M-PESA Express:

- `SBA_MPESA_ENV`: defaults to sandbox. Set `production` only after confirming the approved Buy Goods Till, testing its credentials and verifying the public HTTPS callback.
- `SBA_MPESA_KEY`, `SBA_MPESA_SECRET`, `SBA_MPESA_PASSKEY`: Daraja credentials.
- `SBA_MPESA_SHORTCODE`, `SBA_MPESA_TILL`: the M-PESA Express shortcode and Buy Goods recipient Till supplied by Safaricom.
- `SBA_PUBLIC_ORIGIN`: the public HTTPS origin.
- `SBA_MPESA_CALLBACK_TOKEN`: a random hexadecimal secret of at least 32 characters. Safaricom posts results to `/mpesa/callback/<token>`.

A private Site does not expose callbacks to anonymous callers. The payment status page can request confirmation directly from Daraja. An initiation without a checkout ID stays blocked for manual reconciliation against merchant records.

The hosted port includes event listings, free reservations, M-PESA ticket payments, account tickets, venue check-in, and the journal. Administrators create events, write and publish stories, and verify bookings at `/admin/program`. A voter can see their credits and tickets and change their password at `/account`. Email OTP, password-reset email, and emailed ticket receipts require an email service and are not enabled.

Sign-in uses an email address for voters, nominees and administrators. The administrator email defaults to `thaganakenya@gmail.com`; use the optional `SBA_ADMIN_EMAIL` setting to override it. Use `SBA_ADMIN_PASSWORD` for the administrator password. Voter accounts created before role-based sign-in retain the voter role. New nominations are linked to the signed-in account; pre-existing nominations need an explicit ownership migration before they can appear on an account's dashboard.

## Voting rounds and verified accounts

Use **Voting and nominee pairing** in administration to open a named voting round. The homepage ranks up to three nominees per category from votes recorded in the current round during the last 24 hours. Closing a round freezes the finalist roster and full-round vote totals for judging. The admin records an agreed panel score for each finalist and publishes the winners and runners-up after scoring is complete and medal-place ties are resolved. The public vote contributes up to 70 points relative to the category leader; the panel score contributes up to 30 points. Historical winners can still be entered separately. The site does not infer winners or timestamps from legacy votes.

Set `SBA_RESEND_API_KEY` and `SBA_VERIFICATION_FROM` (a Resend-verified sender) in the site's runtime settings before inviting voters or nominees. Verification emails go to the recorded address, expire after 30 minutes and require the account password to confirm. Existing accounts must verify because they previously had no verification record. Administrator access continues to use the separately configured admin credentials.

Verification creates a pairing request for matching nominee email addresses. The administrator must approve the match before the account can see the profile's vote total or replace its photo. Nominee photos uploaded through that dashboard become visible in the public nominee directory when the nomination is approved.

The next voting round opens automatically on 1 June 2027 at midnight East Africa Time. While voting is closed, the homepage uses labeled demo winners only where no official winner is recorded. Demo results are presentation data and are never saved as official winners.

Approved nominees receive a generated campaign poster page, with a downloadable PNG accessible from their paired nominee dashboard. The poster includes the public voting URL and instructions.

Administrators can open **M-PESA test console** at `/admin/payments` even while voting is closed. The connection check validates Daraja OAuth without starting an STK prompt. A test prompt requests KES 10 in the configured environment, requires explicit confirmation, and creates no voting credits, votes, or tickets. In production, accepting it on the phone is a real Till payment. The console can query the payment status, display Daraja response codes, and indicate whether the callback reached the site. Prompt tests are stored separately from public payment orders.

## Google Analytics, Search Console and Bing

- **GA4 collection:** set `SBA_GA4_MEASUREMENT_ID` to the web stream ID starting with `G-`. The tag loads on public pages after visitors opt in. The existing first-party traffic counts remain separate.
- **Google reporting:** enable the Google Analytics Data and Search Console APIs in the Google Cloud project that owns the service account. Add the service-account email as Viewer on the GA4 property and give it read access to the verified Search Console property. Store the service-account JSON privately as `SBA_GOOGLE_SERVICE_ACCOUNT_JSON`. Set the numeric `SBA_GA4_PROPERTY_ID` and, if needed, the exact `SBA_GSC_PROPERTY` such as `https://sba.tsols.co.ke/` or `sc-domain:sba.tsols.co.ke`.
- **Bing reporting:** verify the site in Bing Webmaster Tools and store the API key privately as `SBA_BING_WEBMASTER_KEY`. Set `SBA_BING_SITE_URL` only if the verified URL differs from `https://sba.tsols.co.ke/`.
- **Optional verification tags:** use only the supplied tag content in `SBA_GOOGLE_SITE_VERIFICATION` for a Google URL-prefix property and `SBA_BING_SITE_VERIFICATION` for Bing. Submit `https://sba.tsols.co.ke/sitemap.xml` from the provider dashboards.

Republish after setting runtime values. Reports appear under Audience and Search & SEO. The Google and Bing tables show the site's search query performance, not market-wide keyword volumes.
