# Phase 1.3 — Roles, Authorization & Localization: Completion Report

## 0. Inspection before implementation

The repository already contained Sub-phase 1.1 (Laravel 12 + Breeze-derived phone auth foundation) and Sub-phase 1.2 (phone normalization, database-backed OTP verification, phone-OTP password reset, profile/avatar updates, account statuses, SMS abstraction). Existing controllers, requests, services, migrations, routes, Blade views, middleware aliases and the 58-test suite were reviewed first. Working authentication was reused; no auth component was rebuilt. Only additive changes were made, and no marketplace features from later phases were introduced.

## 1. Roles and authorization implemented

- `users.role` stores `user`, `admin` or `superadmin` (constants on `App\Models\User`).
- Public registration always creates `user`; role is absent from `$fillable`, ignored by registration validation, and prohibited by profile validation.
- `admin` may be assigned only through `UserPolicy::updateRole()` by a superadmin.
- `superadmin` has no public route and is assigned only by `SuperadminSeeder` (or model factory state in tests).
- Superadmins inherit admin access; admins never inherit superadmin access.

## 2. Superadmin seeder details

- `database/seeders/SuperadminSeeder.php` reads `SUPERADMIN_NAME`, `SUPERADMIN_PHONE` and `SUPERADMIN_PASSWORD` through env-backed `config('services.superadmin.*')` entries in `config/services.php`.
- No production password is hardcoded. `.env.example` contains commented, empty placeholders only.
- The phone is canonicalized with `EthiopianPhone`, password hashed with `Hash::make()`, and `phone_verified_at` stamped.
- Repeated runs never duplicate: it skips when any superadmin already exists, and skips (without promoting or overwriting) when the configured phone belongs to an existing account.
- Missing credentials skip with a console warning; partially provided or invalid credentials throw a clear `RuntimeException`.
- `DatabaseSeeder` calls `SuperadminSeeder`. `php artisan db:seed --force` was run locally with no configured credentials and skipped safely without creating an account.

## 3. Middleware and Gate configuration

- Laravel 12 aliases registered in `bootstrap/app.php`:
  - `admin` → `EnsureUserHasAdminRole` (admin or superadmin only).
  - `superadmin` → `EnsureUserIsSuperAdmin` (delegates to the Gate).
  - Existing `phone.verified` and `account.active` aliases kept unchanged.
- `SetLocale` is appended to the `web` middleware group so every web route applies the session locale.
- `AppServiceProvider::boot()` defines `Gate::define('access-superadmin', ...)` allowing only `isSuperAdmin()` and registers `UserPolicy`.
- Both privileged middleware redirect guests to `login`, terminate sessions for suspended/banned users, and return 403 for authenticated but unauthorized users. No admin or superadmin dashboard routes were created; test-only probe routes exercise the middleware.

## 4. Localization architecture

- Supported locales: English `en` (default/fallback), Amharic `am`, Afaan Oromoo `om`, declared in `config/app.php`.
- `SetLocale` reads `session('locale')`, rejects unknown values, stores the English default for invalid values, and calls `App::setLocale()`.
- `POST /locale` (`LocaleController`) validates the submitted locale with `Rule::in(...)`, stores it in the session, applies it to the current request, and returns to the previous page.
- A reusable `x-locale-switcher` component appears in the app navigation, guest layout and welcome page, so it works for authenticated and unauthenticated users.
- All Phase 1 interface strings, controller flash/status messages, rule messages and accessibility labels now resolve through translation keys. User-generated content (names, phone numbers) is never translated automatically.

## 5. Translation files created

For each of `lang/en/`, `lang/am/`, `lang/om/` (33 files total):

| File | Purpose |
| --- | --- |
| `messages.php` | General interface strings, tagline, setup notice |
| `navigation.php` | Navigation and account menu labels |
| `auth.php` | Authentication errors, login-required and inactive-account messages |
| `registration.php` | Registration form and status/error messages |
| `login.php` | Login form and status/error messages |
| `phone.php` | Phone verification/OTP strings |
| `passwords.php` | Password reset flow and enumeration-safe status |
| `profile.php` | Profile labels, statuses, password-visibility labels |
| `validation.php` | Validation rules, friendly attribute names, protected-field messages, phone-format rule |
| `common.php` | Common error and success messages |
| `locales.php` | Native language names (English, አማርኛ, Afaan Oromoo) |

English `validation.php` is the full Laravel rule set. Amharic and Afaan Oromo files translate the rules used by Phase 1; untranslated Laravel rules intentionally fall back to English via `APP_FALLBACK_LOCALE=en`.

The translations are functional drafts. They have **not** been professionally reviewed or verified by a native-speaker translator.

## 6. Files created and modified

### Created

- `database/migrations/2026_11_24_110000_add_role_to_users_table.php`
- `database/seeders/SuperadminSeeder.php`
- `app/Policies/UserPolicy.php`
- `app/Http/Middleware/EnsureUserHasAdminRole.php`
- `app/Http/Middleware/EnsureUserIsSuperAdmin.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Http/Controllers/LocaleController.php`
- `resources/views/components/locale-switcher.blade.php`
- `lang/en/*.php`, `lang/am/*.php`, `lang/om/*.php` (33 files)
- `tests/Feature/RolesAndAuthorizationTest.php`
- `tests/Feature/SuperadminSeederTest.php`
- `tests/Feature/LocalizationTest.php`
- `PHASE_1_3_COMPLETION_REPORT.md`

### Modified

- `app/Models/User.php`, `app/Providers/AppServiceProvider.php`, `app/Rules/EthiopianPhoneRule.php`
- `app/Http/Controllers/Auth/{AuthenticatedSession,RegisteredUser,PhoneVerification,PasswordReset}Controller.php`, `app/Http/Controllers/ProfileController.php`
- `app/Http/Middleware/EnsureAccountIsActive.php`, `app/Http/Requests/Profile/UpdateProfileRequest.php`
- `bootstrap/app.php`, `config/app.php`, `config/services.php`, `routes/web.php`
- `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php`
- All Phase 1 Blade views/layouts/components, `resources/css/app.css`, `tailwind.config.js`
- `.env.example`, `README.md`, `tests/Feature/ProfileTest.php` (role assertion updated for the new column)
- Rebuilt `public/build/` assets with Vite

## 7. Database migrations

- `2026_11_24_110000_add_role_to_users_table`: adds indexed `role` string (default `user`), backfills any null values, and drops the column on rollback.
- Applied with `php artisan migrate --force`. Rollback and re-migration were both executed successfully. No existing rows or tables were dropped.

## 8. Security controls

- Role is not mass assignable; profile updates explicitly prohibit `role`, `status`, `phone` and `phone_verified_at`.
- `UserPolicy::updateRole()` allows only a superadmin acting on another non-superadmin, and only to `user`/`admin`; self-role changes and assignment of `superadmin` are always denied.
- Superadmin Gate and middleware are separate from admin middleware; admins cannot reach superadmin-protected functionality.
- Suspended/banned privileged accounts are logged out and redirected to login by both middleware.
- Guests are redirected to login (with intended URL preserved); authenticated unauthorized users receive 403.
- Seeder never promotes/overwrites an existing account and never exposes a public creation route.
- Language switch is CSRF-protected server-side and restricted to configured locales; session tampering falls back to English.
- Existing account-status, OTP, rate-limit, hashing, session-regeneration and enumeration-safety controls from 1.2 remain intact.

## 9. Automated test results

`php artisan test --compact` → **84 passed, 348 assertions** (baseline was 58 passed / 225 assertions).

New coverage includes:

- **Roles:** registration defaults to `user` even when a role is submitted; role not mass assignable; profile role changes rejected; policy rules for self/role escalation; Gate allows only superadmin; admin/superadmin middleware pass/deny/redirect cases; suspended/banned privileged accounts blocked.
- **Seeder:** creates hashed superadmin; normalizes phone; repeated runs do not duplicate; existing accounts not promoted/overwritten; changing configured phone does not create a second superadmin; missing/partial credentials handled; `DatabaseSeeder` wiring; public registration cannot create admin/superadmin.
- **Localization:** English default; Amharic and Afaan Oromo selection and rendering; persistence across requests; unsupported session and submitted locales fall back/rejected; grouped keys resolve; UTF-8 Amharic characters render; switcher for guests and authenticated users; previous page preserved; user-generated content unchanged; Ethiopic font class applied.

PHPUnit continues to use MySQL (`adama_market_test`) because this environment's PHP CLI lacks `pdo_sqlite`. All tests use `FakeOtpProvider`; no real SMS is sent. Pint passes on all 1.3 PHP files.

## 10. Incomplete work or known issues

- No admin or superadmin dashboard, user-management UI, category/area, listing, favorites, chat, reports or payment features were added (all intentionally deferred).
- Amharic/Afaan Oromo validation files translate the rules used in Phase 1; less common Laravel rules fall back to English. No native-speaker review has been performed.
- AfroMessage delivery remains implemented against the documented API but has not been exercised with live credentials (carried over from 1.2).
- Role integrity is enforced in application code (constants, policy, guarded attributes, seeder) rather than a database enum/check constraint.
- Existing sessions are still not invalidated when a password is reset (carried over from 1.2; not required for this phase).

## Phase 1 completion checklist

### Sub-phase 1.1 — Foundation (already present, verified)

- [x] Laravel 12 + Breeze Blade scaffold, Tailwind, Alpine/Vite assets
- [x] Phone-only registration/login foundation and auth routes/views
- [x] Initial users/cache/jobs migrations and phone-only users table adaptation
- [x] OTP foundation and Ethiopian phone normalization support

### Sub-phase 1.2 — Authentication & user accounts (already present, verified)

- [x] Phone-only registration with normalized unique phone and no email
- [x] Phone login, rate limiting, generic errors, session regeneration
- [x] Database-backed SMS OTP verification with hashing, expiry, attempts, resend throttle and single use
- [x] Phone-OTP password reset with enumeration-safe responses
- [x] Profile view/update with validated avatar upload
- [x] `active`/`suspended`/`banned` account restrictions
- [x] SMS provider abstraction (log default, documented AfroMessage implementation)
- [x] Automated tests for all of the above

### Sub-phase 1.3 — Roles, authorization & localization (this sub-phase)

- [x] Three roles via `users.role`; public registration always creates `user`
- [x] Environment-driven `SuperadminSeeder` wired into `DatabaseSeeder`, idempotent and non-destructive
- [x] `access-superadmin` Gate and `UserPolicy` for role/account management
- [x] `admin` and `superadmin` middleware with guest redirects and status checks
- [x] Role escalation and unauthorized access controls tested
- [x] English/Amharic/Afaan Oromoo translations with grouped, consistent keys
- [x] Session-backed `SetLocale` middleware and language switcher for guests/users
- [x] Noto Sans Ethiopic font support without breaking Latin interfaces
- [x] Full regression and new automated test suite passing (84 tests / 348 assertions)

Phase 1 is complete. Phase 2 has **not** been started.
