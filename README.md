# SBA Awards recovered PHP source

This review branch imports the newer local PHP application. It includes the M-PESA, voting, credits, events, ticketing, blog, admin and account workflows. Use Composer to install the declared PHPMailer dependency. Private uploads, runtime files, dependency copies, and the bundled tunneling executable are excluded.

Configure credentials through server environment variables. Database settings use `SBA_DB_HOST`, `SBA_DB_USER`, `SBA_DB_PASSWORD`, and `SBA_DB_NAME`. Mail uses `SBA_SMTP_HOST`, `SBA_SMTP_USER`, `SBA_SMTP_PASSWORD`, and `SBA_SMTP_FROM`. The payment endpoints use `SBA_MPESA_KEY`, `SBA_MPESA_SECRET`, `SBA_MPESA_SHORTCODE`, `SBA_MPESA_PASSKEY`, and `SBA_MPESA_CALLBACK_URL`. The legacy transaction status endpoint also uses `SBA_MPESA_INITIATOR_NAME`, `SBA_MPESA_INITIATOR_PASSWORD`, `SBA_MPESA_RESULTS_URL`, and `SBA_MPESA_TIMEOUT_URL`.

This archive did not include a database schema or a verified deployment configuration. Review the legacy app and validate it against a staging database and sandbox payment account before hosting it.
