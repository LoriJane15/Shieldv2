# SH1ELD Local Setup Guide

This guide reproduces the local development setup currently used by the project:
Laravel with MySQL on `127.0.0.1:3306` and a database named `sh1eld_testing`. Run
all commands from a terminal unless a step specifically says PowerShell.

## 1. Install the required software

Install:

- [Git](https://git-scm.com/downloads)
- PHP 8.2 or newer with the `fileinfo`, `mbstring`, `openssl`, `mysqli`, and
  `pdo_mysql` extensions enabled
- [Composer](https://getcomposer.org/download/)
- A current Node.js LTS release, which includes npm
- MySQL 8 or a local development package that provides MySQL, such as Laragon,
  XAMPP, or MySQL Community Server

After installing them, open a new terminal and confirm that every command works:

```bash
git --version
php --version
composer --version
node --version
npm --version
mysql --version
```

If a command is not recognized, restart the terminal and ensure that the program's
installation directory is included in the system `PATH`.

Start the MySQL service before continuing. In Laragon or XAMPP, use the application
control panel to start MySQL.

## 2. Download the project

Choose a folder where you keep projects, then run:

```bash
git clone https://github.com/Siom4ii/Sh1eld2.git
cd Sh1eld2
```

If the repository was downloaded as a ZIP instead, extract it, open a terminal
inside the extracted `Sh1eld2` folder, and continue with the next step.

## 3. Create the local environment file

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

On macOS, Linux, or Git Bash:

```bash
cp .env.example .env
```

Open `.env` in a text editor and replace its application and database settings with:

```env
APP_NAME=SH1ELD
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

SHIELD_DEV_PASSWORD=Shield-Local-2026-Test!

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sh1eld_testing
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

`SHIELD_DEV_PASSWORD` must contain at least 12 characters. It is used only for
synthetic local accounts. Never reuse a real password or commit `.env`.

The current local setup uses the MySQL `root` account without a password. If your
MySQL installation has a password, enter it after `DB_PASSWORD=`. For a shared or
long-lived environment, create a dedicated database user instead of using `root`.

## 4. Create the MySQL database

Open MySQL from Laragon, XAMPP/phpMyAdmin, MySQL Workbench, or the command line.
Create an empty local database with this SQL:

```sql
CREATE DATABASE sh1eld_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

From a terminal, the same operation can be performed with:

```bash
mysql -u root -p
```

Enter the MySQL password when prompted, run the SQL above, and type `exit` to close
the MySQL prompt. If the local `root` account has no password, press Enter at the
password prompt.

If Laravel later reports `could not find driver`, enable the `pdo_mysql` and
`mysqli` extensions in the `php.ini` used by command-line PHP, then restart the
terminal.

## 5. Install project dependencies

Run these commands from the project folder:

```bash
composer install
npm install
```

These commands create the local `vendor` and `node_modules` directories. Dependency
installation can take several minutes on a new device.

## 6. Generate the application key

```bash
php artisan key:generate
```

The command writes a unique `APP_KEY` into the local `.env` file.

## 7. Create and seed the local database

```bash
php artisan migrate --seed
```

This creates the tables, location reference data, seven synthetic role accounts,
and sample records for the RCSP, IMPLAN, former-rebel monitoring, and map workflows.
The development seeder runs only when `APP_ENV=local`.

## 8. Start SH1ELD

```bash
composer run dev
```

Keep this terminal open. It runs the Laravel server, queue listener, and Vite
frontend server together. Open the application at:

```text
http://127.0.0.1:8000
```

Stop all development services by pressing `Ctrl+C` in the terminal.

## 9. Sign in with a test account

All seeded accounts use the value of `SHIELD_DEV_PASSWORD` from `.env`. With the
example configuration above, the password is `Shield-Local-2026-Test!`.

| Username | Account role |
| --- | --- |
| `super_admin` | Super Admin |
| `admin` | Katuparan Center |
| `39th_ib` | 39th IB |
| `lgu` | DILG — LGU (scoped to Digos City) |
| `gov_agency` | Government Agency (scoped to a synthetic agency) |
| `mblrc` | MBLRC |
| `afp` | AFP |

These credentials are development-only and must never be used in production.

## Updating an existing local copy

From the project folder, stop the development servers and run:

```bash
git pull
composer install
npm install
php artisan migrate
npm run build
php artisan optimize:clear
```

Start the application again with `composer run dev`.

## Resetting local sample data

To delete and rebuild only the database configured in `.env`, run:

```bash
php artisan migrate:fresh --seed
```

This permanently deletes that database's current records. Verify that `.env` points
to the disposable local `sh1eld_testing` database before using the command.
