# Adama Second-Hand Market

A Laravel application for buying and selling second-hand goods in Adama, Ethiopia.

## Stack

- Laravel 12 (Blade)
- MySQL
- Tailwind CSS
- Alpine.js
- Laravel Breeze (Blade stack), adapted for phone-only authentication with OTP verification

## Local setup

1. Copy `.env.example` to `.env` and set your database credentials.
2. Install dependencies: `composer install` and `npm install`.
3. Generate an application key: `php artisan key:generate`.
4. Create the MySQL database named in `DB_DATABASE`.
5. Run migrations: `php artisan migrate`.
6. Build assets: `npm run build`.
7. Serve the app: `php artisan serve`.

## Notes

- The default timezone is `Africa/Addis_Ababa` (`APP_TIMEZONE`).
- Sessions, cache, and queues use the `database` driver.
- Users are identified by a normalized, unique Ethiopian phone number (`09XXXXXXXX` / `07XXXXXXXX`). Email fields and email-based verification/password reset are not used.
- OTPs are cryptographically random six-digit codes. Only their hashes are stored in `phone_verifications`; codes expire after `OTP_EXPIRY_MINUTES` (default 5), allow a limited number of attempts, and are throttled on resend (`OTP_RESEND_SECONDS`). Verification and password-reset OTPs use separate purposes.
- SMS delivery is abstracted behind `App\Services\Contracts\SmsService`. `LogSmsService` is the local/test default and never writes OTPs to logs. `AfroSmsService` implements the documented AfroMessage `POST /api/send` API (Bearer token, JSON body, `acknowledge` check) and is opt-in via `SMS_PROVIDER=afrosms`; it requires `AFROSMS_TOKEN` and `AFROSMS_SENDER_NAME`.
- Password reset uses the same phone OTP flow (`/forgot-password` → `/reset-password`) with account-enumeration-safe responses.
- Profiles (`/profile`) allow name and avatar changes only; phone, verification state, status and role are protected. Run `php artisan storage:link` so uploaded avatars are publicly reachable.
- Account statuses are `active`, `suspended` and `banned`; suspended/banned accounts are blocked from protected routes. No admin status UI exists yet.
- Tests use a fake SMS service and MySQL. Create the `adama_market_test` database before running `php artisan test` (see `phpunit.xml`).
- Marketplace features are planned for later phases.
