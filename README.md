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
6. Optionally set the `SUPERADMIN_*` environment variables and run `php artisan db:seed --class=SuperadminSeeder`.
7. Build assets: `npm run build`.
8. Serve the app: `php artisan serve`.

## Notes

- The default timezone is `Africa/Addis_Ababa` (`APP_TIMEZONE`).
- Sessions, cache, and queues use the `database` driver.
- Users are identified by a normalized, unique Ethiopian phone number (`09XXXXXXXX` / `07XXXXXXXX`). Email fields and email-based verification/password reset are not used.
- OTPs are cryptographically random six-digit codes. Only their hashes are stored in `phone_verifications`; codes expire after `OTP_EXPIRY_MINUTES` (default 1), allow a limited number of attempts, and are throttled on resend (`OTP_RESEND_SECONDS`). Verification and password-reset OTPs use separate purposes.
- SMS delivery is abstracted behind `App\Services\Contracts\SmsService`. `LogSmsService` is the local/test default and never writes OTPs to logs. `AfroSmsService` implements the documented AfroMessage `POST /api/send` API (Bearer token, JSON body, `acknowledge` check) and is opt-in via `SMS_PROVIDER=afrosms`; it requires `AFROSMS_TOKEN` and `AFROSMS_SENDER_NAME`.
- Roles are stored in `users.role`: `user` (public registration), `admin` (promoted by a superadmin) and `superadmin` (created only by `php artisan db:seed --class=SuperadminSeeder`). Role changes are never accepted by public forms. Future admin routes use the `admin` middleware and superadmin-only routes use the `superadmin` middleware; authorization is enforced by the `access-superadmin` Gate and `UserPolicy`.
- To create the initial superadmin, set `SUPERADMIN_NAME`, `SUPERADMIN_PHONE` and `SUPERADMIN_PASSWORD` in the environment, then run the seeder. Missing credentials skip seeding; partial/invalid credentials fail loudly. Existing accounts are never promoted or overwritten.
- UI languages are English (`en`, default), Amharic (`am`) and Afaan Oromoo (`om`). The session-backed switcher is available to guests and signed-in users. Translation files live in `lang/{en,am,om}/`; English is the fallback. Noto Sans Ethiopic is loaded for Ethiopic script while Figtree remains the primary Latin font. Translations are functional drafts, not professionally reviewed.
