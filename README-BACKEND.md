# I Can Hear You Rwanda – back-end setup

PHP 8+ and MySQL/MariaDB (e.g. XAMPP). The original front-end markup and CSS are unchanged; the static
mock data was replaced by real database-driven pages.

## 1. Install
1. Copy the folder into `htdocs/` (so it is reachable at `http://localhost/i-can-hear-you/`).
2. Create a database and import `database/database.sql`; set credentials in `config/database.php`.
3. Run the migration (adds a few columns/tables the back-end needs). **Required.**
   `php database/migrate.php`            – schema only
   `php database/migrate.php --seed`     – schema + demo accounts, bookings, resources
4. Make `uploads/` writable by the web server.
5. Open `pages/login.php`.

Demo logins (only with `--seed`; change them before going live):
| Role | Email | Password |
|---|---|---|
| Admin | admin@icanhearyou.rw | Admin@12345 |
| Counsellor | jean@icanhearyou.rw | Counsellor@123 |
| Client | alice.m@example.com | Client@12345 |

Without `--seed`, create the first admin at `Admin/create_admin.php` (it refuses to run once an admin exists –
delete the file afterwards).

## 2. What now works
**Client:** dashboard (real stats), find counsellors (live availability), counsellor profile, booking with
live free-slot lookup and double-booking protection, my bookings (cancel), my sessions (join video link /
open chat, feedback), messages (real chat, 5 s polling), notifications, resources (save/unsave),
profile, settings (preferences, password change, deactivate).

**Counsellor:** dashboard, bookings (approve / decline / cancel / start / mark completed), clients,
sessions, messages, session notes (per session), notifications, resources (upload file or link; goes to
admin approval if setting `counsellor_admin_approval` = 1), profile (public details), settings.

**Admin:** existing users/clients/counsellors pages plus bookings, sessions, notifications (broadcast),
schools, organizations, programs (add / change status / delete), resources (approve, archive, delete, add),
reports (date range + CSV export), system settings.

Booking flow: client requests -> counsellor approves -> a `sessions` row is created (video sessions get a
private Jitsi Meet link, no API key needed) -> counsellor completes -> client can leave feedback. Every step
creates in-app notifications and audit-log entries.

## 3. Structure added
- `includes/` bootstrap, helpers, shared page variables, admin entity definitions
- `api/` bookings, messages, notifications, sessions, resources, account endpoints (CSRF-protected)
- `Admin/*-actions.php` admin POST handlers
- `assets/js/app.js` shared behaviour; `messages.js`, `booking.js`, `counselor-lists.js` rewritten for the API
- `database/migrate.php` idempotent migration + demo data

## 4. Not done / limits
- Written without a PHP runtime available, so it has **not been executed**; please test each page once and
  report any error (check the PHP error log).
- Not implemented: e-mail sending, password reset, profile photos, chat file attachments, calendar views,
  per-row edit forms for schools/organizations/programs (create, status change and delete only).
- Old fake-behaviour scripts were trimmed (profile/settings/resources/sessions); other admin JS files for
  users/clients/counsellors were left untouched.
