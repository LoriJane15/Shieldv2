# SH1ELD

SH1ELD is a Laravel 12 web application for monitoring reintegration records, RCSP barangays, assistance programs, government interventions, documents, reports, and role-based inter-agency workflows.

The application uses Laravel, Vite, Tailwind, Alpine.js, Chart.js, Leaflet, and a database-backed queue/session setup.

## Requirements

Install these before running the system:

- PHP 8.2 or newer
- Composer
- Node.js and npm
- Git
- SQLite, MySQL, or another Laravel-supported database

For local development, SQLite is the easiest option because `.env.example` is already configured for it.

## Step-by-Step Local Setup

### 1. Clone the repository

```bash
git clone https://github.com/Siom4ii/Sh1eld2.git
cd Sh1eld2
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JavaScript dependencies

```bash
npm install
```

### 4. Create the environment file

For macOS/Linux/Git Bash:

```bash
cp .env.example .env
```

For Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Never commit the generated `.env` file.

### 5. Generate the application key

```bash
php artisan key:generate
```

### 6. Create the local database

The default `.env.example` uses SQLite:

```env
DB_CONNECTION=sqlite
```

Create the SQLite database file.

For macOS/Linux/Git Bash:

```bash
touch database/database.sqlite
```

For Windows PowerShell:

```powershell
New-Item -ItemType File database/database.sqlite -Force
```

For realistic multi-user development testing, use a dedicated MySQL database instead:

```sql
CREATE DATABASE sh1eld_testing
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Configure `.env` with a database account that is restricted to this development database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sh1eld_testing
DB_USERNAME=your_local_database_user
DB_PASSWORD=your_local_database_password
```

Never point a local testing environment at a production database.

### 7. Run migrations and seeders

Set a local password for the synthetic development accounts. It must contain at
least 12 characters and must not be a password used by a real account:

```env
SHIELD_DEV_PASSWORD=choose-a-local-test-password
```

Then initialize the database:

```bash
php artisan migrate --seed
```

This creates the database tables, location reference data, synthetic role accounts,
and synthetic records for the RCSP, IMPLAN, former-rebel monitoring, and map
workflows. Development records are clearly labelled as synthetic and contain no
real personal information.

To completely rebuild an existing development database, use the following command.
It deletes all data in the configured database:

```bash
php artisan migrate:fresh --seed
```

### 8. Start the development environment

```bash
composer run dev
```

This runs these services together:

- Laravel development server
- Laravel queue listener
- Vite development server

### 9. Open the system

Open this URL in your browser:

```text
http://127.0.0.1:8000
```

## Seeded Development Accounts

When `APP_ENV=local`, the development seeder creates the following usernames:

```text
super_admin
admin
39th_ib
lgu
gov_agency
mblrc
afp
```

The login form uses the username and the local password configured in
`SHIELD_DEV_PASSWORD`. The LGU account is scoped to Digos City, and the government
agency account is scoped to a synthetic test agency. The development seeder is
blocked in production.

## Common Commands

Run only the Laravel server:

```bash
php artisan serve
```

Run only the Vite dev server:

```bash
npm run dev
```

Run only the queue listener:

```bash
php artisan queue:listen
```

Build frontend assets for production:

```bash
npm run build
```

Run the test suite:

```bash
composer run test
```

Or:

```bash
php artisan test
```

Format PHP files:

```bash
vendor/bin/pint
```

Clear cached Laravel configuration:

```bash
php artisan optimize:clear
```

## Legacy Data Import

The project includes an Artisan command for importing legacy data:

```bash
php artisan import:legacy
```

Use `--fresh` only when you intentionally want to wipe the destination tables before import:

```bash
php artisan import:legacy --fresh
```

Run this command only after configuring the required legacy data source.

## Troubleshooting

If the application key is missing:

```bash
php artisan key:generate
```

If database tables are missing:

```bash
php artisan migrate --seed
```

If pages load but styles or scripts are missing:

```bash
npm run build
```

If cached configuration causes unexpected behavior:

```bash
php artisan optimize:clear
```

If `composer run dev` fails on Windows because Laravel Pail or process control extensions are unavailable, run the services separately:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

## Production Notes

Before deploying, configure production-safe values in `.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- Production database credentials
- Mail settings
- Queue connection
- Storage configuration

Then run:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize
```

## Recent Fixes and Validation

Last updated: July 30, 2026

### Navigation and Login

- Displayed the authenticated user's original logo and name in both SkyDash
  navigation layouts.
- Added a responsive profile chip while preserving the Settings and Logout
  dropdown.
- Corrected Laravel public-disk logo URLs so newly uploaded logos appear in the
  navigation and related views.
- Added an accessible show/hide password control to the login form.
- Aligned the password visibility button with the input and made the crossed eye
  represent a hidden password.

### Development Database

- Added an environment-protected `DevelopmentSeeder`.
- Added synthetic accounts for all seven roles:
  `super_admin`, `admin`, `39th_ib`, `lgu`, `gov_agency`, `mblrc`, and `afp`.
- Added synthetic agencies, RCSP reference records, RCSP workflow states, IMPLAN
  states, Former Rebel monitoring records, and map data.
- Made development seeding repeatable without duplicating its managed records.
- Required the local test-account password through `SHIELD_DEV_PASSWORD`.
- Prevented the development dataset from running automatically in production.
- Added tests covering scoped accounts, workflow data, and repeatable seeding.

### Super Admin User Logos

- Displayed existing user logos in the User Management table and edit form.
- Added logo replacement and recropping of an existing logo.
- Added a square crop workspace with drag alignment and zoom.
- Prevented user-form submission until a newly selected logo crop is accepted.
- Disabled dragging on an axis when the image has no overflow on that axis.
- Standardized browser-generated crops to a 512-by-512 JPEG.
- Restricted server uploads to square JPG, PNG, or WebP images up to 5 MB.
- Stored replacement files before updating the user and deleted the old managed
  logo only after a successful update.
- Removed managed logo files when their user account is deleted.
- Created the Laravel public storage link required to serve uploaded logos.

### Super Admin User and Agency Management

- Grouped user search conditions so role filters remain enforced when searching
  by name or username.
- Added tests for Super Admin-only access to user and agency management.
- Verified role-scoped account creation and automatically cleared municipality or
  agency assignments that do not apply to the selected role.
- Added duplicate username and required role-scope validation coverage.
- Prevented a Super Admin from accidentally removing their own Super Admin role.
- Prevented deletion of users who own RCSP forms or IMPLAN records, avoiding
  cascade deletion of official workflow history.
- Displayed a locked action for users that cannot safely be deleted.
- Converted the government agency profile field into a validated agency logo
  upload accepting JPG, PNG, or WebP images up to 5 MB.
- Added correct public-disk and legacy agency logo URL handling across management,
  dashboard, and IMPLAN views.
- Added unique agency acronym validation for create and update operations.
- Stored replacement agency logos before updating records and safely removed old
  managed files afterward.
- Prevented deletion of agencies with assigned users, IMPLAN responses, or tagging
  history.
- Removed managed agency logo files when an unused agency is safely deleted.
- Displayed existing agency logos in the agency table and edit form.
- Replaced native browser delete prompts with a reusable confirmation modal for
  users and agencies.
- Added record-specific deletion messages, protected modal dismissal, and a
  disabled loading state while permanent deletion is submitted.
- Required an explicit acknowledgment checkbox before the permanent-delete button
  becomes available.

### 39th IB Operational Map

- Rebuilt the operational map with status-colored Former Rebel location markers.
- Added search, status filtering, marker counts, reset, Show All, automatic bounds,
  safe popups, a legend, and loading, empty, and error states.
- Restricted marker data to the authenticated `39th_ib` role.
- Added non-cacheable response headers for sensitive location data.
- Added synthetic test coordinates so the development map has visible markers.
- Replaced external Leaflet code and styles with the locally installed package.
- Changed the map to a stable full-width layout and added automatic resizing when
  the sidebar or viewport changes.
- Barangay infestation boundaries are not displayed because the repository does
  not yet contain authoritative barangay GeoJSON or boundary coordinates.

### MBLRC Former Rebel Monitoring

- Enforced MBLRC role middleware across the registry, profile actions, map data,
  location history, and certificate download endpoints.
- Normalized names, addresses, surrender reasons, and contact numbers before
  server-side validation and storage.
- Required the selected barangay to belong to the selected municipality,
  preventing inconsistent profile addresses.
- Added a database unique constraint for classified IDs. The migration stops with
  a clear error if pre-existing duplicate IDs must be resolved first.
- Prevented deletion of Former Rebel records that already contain program status,
  education/work, location, skill, or assistance history.
- Replaced the registry's browser delete prompt with the acknowledgment-checkbox
  confirmation modal and displayed a lock for protected records.
- Preserved nullable reintegration dates instead of silently recording the current
  date when no official date was supplied.
- Recorded each geolocation update in location history and marked location,
  barangay, and marker JSON responses as private and non-cacheable.
- Updated an existing skill's proficiency when the same skill is entered again,
  avoiding duplicate profile rows.
- Kept the profile's current occupation synchronized when education/work details
  are updated or cleared.
- Stored newly uploaded assistance certificates on Laravel's private disk and
  added an authenticated MBLRC-only download route.
- Kept an authorized-download fallback for legacy certificates already stored on
  the public disk; new certificate uploads are never written there.
- Prevented deletion of completed, received, or certificate-backed assistance
  history while still allowing an erroneous undocumented pending entry to be
  removed.
- Added visible AJAX validation and request errors instead of failing silently on
  profile widget actions.
- Added 12 MBLRC workflow regression tests covering role access, profile
  validation, address integrity, geotag history, status dates, skill updates,
  occupation synchronization, private certificates, deletion safeguards,
  classified-ID uniqueness, and modal confirmation.

### Authentication and Account Recovery

- Fixed password confirmation to verify the currently authenticated user's
  password hash instead of relying on a nullable email address.
- Redirected successful password confirmation to the user's role dashboard or the
  original intended protected URL.
- Kept public registration, email verification, and email password reset disabled
  according to the Super Admin-provisioned account policy.
- Added clear login guidance directing users to the Super Admin for password
  recovery.
- Added Super Admin password replacement coverage and prohibited other roles from
  resetting another user's password.
- Revoked the affected user's database sessions after a Super Admin password
  replacement.
- Standardized managed-account password validation to a minimum of eight
  characters.
- Updated obsolete authentication tests to verify the intended account policy.

### Validation Status

The latest complete Laravel test run passed:

```text
68 tests passed
261 assertions passed
```

PHP formatting, Blade compilation, JavaScript syntax checks, and the production
frontend build also passed for the related changes.

Passing tests confirm the covered authentication, development seeding, RCSP
lifecycle, Super Admin logo/password management, 39th IB map behavior, and MBLRC
Former Rebel monitoring workflows. Other modules still require dedicated automated
coverage and role-by-role user acceptance testing before the complete system can
be considered production-ready.

### System Health Audit

The July 30, 2026 system audit completed the following checks:

- Confirmed Laravel 12.64.0 starts successfully with PHP 8.2.12.
- Confirmed the configured MySQL database is reachable.
- Confirmed all nine migrations are applied.
- Confirmed all 91 application routes register successfully.
- Confirmed the public storage link is present.
- Added a permanent role-by-role smoke test that renders 30 primary pages and
  data endpoints across `super_admin`, `admin`, `39th_ib`, `lgu`, `gov_agency`,
  `mblrc`, and `afp`.
- Confirmed Blade templates compile and the Vite production build succeeds.
- Confirmed there were no Laravel errors dated July 30 in the application log.
  The remaining log entries are historical July 29 errors from issues that have
  since been fixed and covered by passing regression tests.
- Confirmed all files changed during the current work pass Pint formatting.
- The repository-wide Pint check still reports pre-existing style issues in ten
  older files, including a bundled landing-page library. These are formatting
  findings rather than runtime errors and were not rewritten during this audit.

Apply the MBLRC classified-ID constraint with:

```bash
php artisan migrate
```
