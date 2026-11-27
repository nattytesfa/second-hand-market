# Phase 1.2 — Authentication & User Accounts: Completion Report

## 0. Inspection of the existing implementation

The repository already contained Sub-phase 1.1: Laravel 12 + Breeze-derived Blade auth, phone-only registration/login, a cache-based OTP service, a `phone.verified` middleware, and the two initial migrations. No separate 1.1 completion-report file exists in the repository; `README.md` was the available 1.1 summary and was reviewed together with all auth controllers, requests, models, services, routes, views and migrations. Existing 1.1 code was extended rather than replaced wholesale, and no tables or data were dropped.

## 1. Features implemented

- Phone-only registration with full name, Ethiopian phone, password and password confirmation; no email anywhere.
- Canonical phone normalization (`09XXXXXXXX` / `07XXXXXXXX`) for local, `251…` and `+251…` inputs, enforced in form requests, the `User` model mutator and an additive data-normalization migration.
- Duplicate-account prevention using the normalized value and the existing unique phone index.
- Phone login with server-side validation, generic credential errors, 5-attempt rate limiting, session regeneration and protected routes.
- Database-backed SMS OTP verification with `phone_verifications`, six-digit cryptographic codes, hash-only storage, 5-minute expiry, attempt limits, resend cooldown, purpose separation, consumption/invalidation and `phone_verified_at`.
- SMS abstraction: `SmsService` interface, `LogSmsService` (local/test default, redacted logging), and `AfroSmsService` implementing the official AfroMessage `POST /api/send` contract from https://www.afromessage.com/developers#dev-getsend (opt-in, credentials required).
- Phone-OTP password reset with enumeration-safe responses, expiry/attempt/resend controls and single-use codes.
- Basic profile: view, name update, phone/verification status display, validated JPEG/PNG/WebP avatar upload (max 2 MB) with old-file cleanup.
- Account statuses `active`, `suspended`, `banned`; suspended/banned users are rejected at login and blocked by `account.active` middleware on protected routes. No admin UI added.

## 2. Files created and modified

### Created

- `app/Support/EthiopianPhone.php`
- `app/Rules/EthiopianPhoneRule.php`
- `app/Exceptions/OtpResendThrottledException.php`
- `app/Models/PhoneVerification.php`
- `app/Services/Contracts/SmsService.php`
- `app/Services/LogSmsService.php`
- `app/Services/AfroSmsService.php`
- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Requests/Auth/RequestPasswordResetRequest.php`
- `app/Http/Requests/Auth/VerifyPhoneRequest.php`
- `app/Http/Requests/Auth/ResetPasswordRequest.php`
- `app/Http/Requests/Profile/UpdateProfileRequest.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Middleware/EnsureAccountIsActive.php`
- `database/migrations/2026_11_24_100000_add_status_and_avatar_to_users_table.php`
- `database/migrations/2026_11_24_100001_create_phone_verifications_table.php`
- `resources/views/profile/show.blade.php`
- `tests/Support/FakeSmsService.php`
- `tests/Feature/ProfileTest.php`
- `tests/Feature/AccountStatusTest.php`
- `tests/Feature/SmsServiceTest.php`
- `tests/Feature/AfroSmsServiceTest.php`
- `PHASE_1_2_COMPLETION_REPORT.md`

### Modified

- `app/Services/PhoneVerificationService.php` (cache-based → database/SMS-based)
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Models/User.php`
- `app/Http/Controllers/Auth/{RegisteredUser,AuthenticatedSession,PhoneVerification,PasswordReset}Controller.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `routes/web.php`, `routes/auth.php`
- `config/otp.php`, `config/services.php`
- `.env`, `.env.example`, `phpunit.xml`, `README.md`
- `resources/views/layouts/navigation.blade.php`
- `database/factories/UserFactory.php`
- `tests/TestCase.php`, `tests/Feature/Auth/{Registration,Authentication,PhoneVerification,PasswordReset}Test.php`

## 3. Database changes

Only additive migrations were introduced:

1. `2026_11_24_100000_add_status_and_avatar_to_users_table`
   - `users.avatar` (nullable string), `users.status` (string, default `active`, indexed).
   - Normalizes existing valid phone values in place; skips and logs a warning for a legacy value whose canonical form is already taken.
   - Adds a unique index on `phone` only if one is not already present (the 1.1 migration already created `users_phone_unique`).
2. `2026_11_24_100001_create_phone_verifications_table`
   - `id`, `phone` (indexed), `purpose` (default `verify`), `code_hash`, `expires_at` (indexed), `attempts` (default 0), `consumed_at`, `timestamps`, plus composite `(phone, purpose, consumed_at)` index.

`php artisan migrate --force` was executed successfully against `adama_market`; both new migrations show as Ran. No existing tables were dropped and no rows were deleted.

## 4. Authentication flow explanation

1. **Register:** Form Request validates name, normalized phone (unique) and confirmed password. User is created with `status=active` and `phone_verified_at=null`. A `verify` OTP is dispatched via the SMS service and the user is logged in to `/verify-phone`.
2. **Login:** The phone is normalized and validated; Laravel `Auth::attempt` checks the password. Inactive accounts fail with the same generic message as bad credentials. Five failures trigger lockout. On success the session ID is regenerated; unverified users go to OTP verification, verified users go to their intended page/dashboard.
3. **Verify:** A six-digit OTP is checked against the latest unconsumed row for the phone and `verify` purpose. Success stamps `phone_verified_at`; the code is consumed and cannot be used again.
4. **Protected access:** `auth` + `account.active` guard dashboard/profile/verification; `phone.verified` additionally guards the dashboard and future marketplace routes.
5. **Password reset:** `/forgot-password` always returns the same message regardless of whether the number exists. If it exists, a `reset`-purpose OTP is issued; `/reset-password` requires that code plus a confirmed new password, then consumes the OTP and clears the reset session key.

## 5. OTP lifecycle explanation

- **Issue:** `random_int` generates six digits; `Hash::make` produces `code_hash`. A row is created with `expires_at = now() + OTP_EXPIRY_MINUTES` (5), `attempts = 0`, `consumed_at = null`. Any previous active row for the same phone/purpose is marked consumed first.
- **Delivery:** `SmsService::send()` receives the message. `LogSmsService` logs only a redacted dispatch notice; tests bind `FakeSmsService`. With `SMS_PROVIDER=afrosms`, `AfroSmsService` POSTs to `https://api.afromessage.com/api/send` with `Authorization: Bearer <token>` and JSON `from`/`sender`/`to`/`message`/optional `callback`, converts `09…` to `2519…`, and requires HTTP 200 plus `acknowledge === "success"`. API response bodies are never logged because they echo the OTP.
- **Verify:** The latest usable row is locked and checked for expiry and attempt count. Each wrong code increments `attempts`; at the limit the row is consumed. A correct code is checked with `Hash::check`, then the row is consumed (single use).
- **Resend:** A resend within `OTP_RESEND_SECONDS` (60) throws `OtpResendThrottledException`, converted to a form error or an enumeration-safe reset status. Routes also apply `throttle:3,1`.
- **Local preview only:** A short-lived local-environment cache entry supplies the code shown on Blade forms for manual development. No plaintext code is stored in production, returned in API responses, or written to application logs.

## 6. Security controls implemented

Secure bcrypt password hashing via Laravel's `hashed` cast; hashed OTP storage; secure `random_int` generation; OTP expiry, attempt limits and resend cooldown; login rate limiting with generic errors; session regeneration on login and token regeneration after verification/reset; Form Requests with server-side validation; CSRF-protected web routes/forms; normalized unique phone index; model/controller allow-lists that exclude `status`, `role`, `phone` and `phone_verified_at` from profile updates; avatar MIME/size validation and non-executable private storage path; account-status middleware that terminates stale sessions; no OTPs in production responses or application logs; enumeration-safe password reset responses.

## 7. Tests executed and actual results

- `php artisan test` → **59 passed, 220 assertions** (0 failures).
- Coverage includes registration, normalization, duplicate rejection, email-free registration, login (local/international), wrong password, rate limiting, session regeneration, unverified redirect, OTP hashing/generation, invalid/expired OTP, attempt limits, resend throttling, OTP reuse, purpose isolation, password reset (success/wrong/expired/limited/throttled/enumeration-safe), profile view/update, protected-field rejection, avatar valid/invalid/oversize, suspended/banned login and route restrictions, SMS provider/log-redaction behavior, and the documented AfroMessage request/response contract (including no OTP leakage on API errors).
- All tests use `FakeSmsService`; no real SMS is sent.
- PHPUnit was pointed at MySQL (`adama_market_test`) because this environment's PHP CLI has no `pdo_sqlite` extension; the default SQLite test config could not run here.
- `php artisan migrate --force` on `adama_market` completed successfully.

## 8. External credentials still required

- AfroMessage credentials: `AFROSMS_TOKEN` (Bearer token) and `AFROSMS_SENDER_NAME` are mandatory; `AFROSMS_IDENTIFIER_ID`, `AFROSMS_ENDPOINT` (defaults to the official URL) and `AFROSMS_CALLBACK_URL` are optional. The documented request format is implemented, but no live credentials were available to verify a real send. Keep `SMS_PROVIDER=log` until credentials are supplied and a live test is performed.
- Production database credentials and `APP_KEY` must be configured in the deployment `.env`.
- `php artisan storage:link` must be run in deployment for avatar URLs to resolve.

## 9. Known issues or incomplete work

- AfroMessage delivery is implemented against the official documented contract but has not been exercised with a live account; only `Http::fake()` tests were run. No API payload format was invented.
- Registration and login necessarily reveal whether a phone is already registered / credentials are valid through normal validation, as is standard; other auth responses avoid leaking account state.
- No admin interface for changing account statuses (explicitly out of scope for this sub-phase).
- Plaintext OTPs exist only in a local-development cache entry used for manual verification previews; this is disabled outside `local`.
- Existing sessions are not forcibly invalidated when a password is reset; users remain logged in on other devices until they log out (not required for 1.2).
- No marketplace features, categories/areas, favorites, messaging, reports, payments, localization or admin roles were added.
