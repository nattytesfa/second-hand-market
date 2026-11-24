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
- Users are identified by a unique phone number; email fields and email-based verification/password reset were removed.
- OTP codes are generated locally, stored hashed in the cache, expire after `OTP_EXPIRY_MINUTES`, and are logged in development. Replace the log step with an SMS gateway when available.
- Password reset also uses a phone OTP (`/forgot-password` → `/reset-password`); it uses a separate OTP cache namespace from signup/login verification.
- Marketplace features are planned for later phases.
