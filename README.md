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

To use MySQL instead, update the `DB_*` values in `.env`, create the database manually, then continue with the migration step.

### 7. Run migrations and seeders

```bash
php artisan migrate --seed
```

This creates the database tables, locations, sessions table, queue table, and starter user data.

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

## Seeded Login Account

After `php artisan migrate --seed`, the seeder creates one test user:

```text
Email: test@example.com
Password: password
Role: lgu
```

The login form uses `username`, not email. The seeded username is generated automatically. To view it, run:

```bash
php artisan tinker
```

Then run this inside Tinker:

```php
App\Models\User::where('email', 'test@example.com')->value('username');
```

Use the returned username with password `password`.

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
