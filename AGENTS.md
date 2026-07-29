# AGENTS.md

## Project Overview

**SH1ELD** is a Laravel 12 web-based monitoring system designed to support the secure management of former rebel reintegration records, RCSP barangays, government interventions, assistance programs, documents, and inter-agency workflows.

The system handles sensitive information and uses role-based access control. All changes must prioritize data confidentiality, authorization, traceability, accuracy, and compatibility with existing workflows.

---

## Repository Structure

Follow the existing Laravel project structure:

```text
app/
├── Http/
│   ├── Controllers/       Request handling and workflow coordination
│   ├── Middleware/        Authentication and authorization checks
│   └── Requests/          Form validation and authorization
├── Models/                Eloquent models and relationships
├── Policies/              Model-level authorization rules
├── Services/              Reusable business logic
└── Notifications/         System and user notifications

database/
├── factories/             Test data factories
├── migrations/            Database schema changes
└── seeders/               Roles, permissions, and starter data

resources/
├── css/                   Frontend styles
├── js/                    JavaScript and Vite entry points
└── views/                 Blade templates grouped by role or feature

routes/
├── web.php                Main application routes
├── auth.php               Authentication routes
└── console.php            Artisan console commands

tests/
├── Feature/               Workflows, permissions, and HTTP behavior
└── Unit/                  Isolated services, helpers, and calculations
```

Keep files grouped by feature or authorized user role. Follow existing paths such as:

```text
resources/views/lgu/rcsp
resources/views/former-rebels
resources/views/assistance
resources/views/reports
```

Do not introduce a new folder structure unless it clearly improves maintainability and follows Laravel conventions.

---

## Setup and Development Commands

Install the required dependencies:

```bash
composer install
npm install
```

Create the local environment file:

```bash
cp .env.example .env
```

For PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Prepare the database:

```bash
php artisan migrate --seed
```

Start the complete development environment:

```bash
composer run dev
```

Run individual services when necessary:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

Build production frontend assets:

```bash
npm run build
```

Clear cached Laravel configuration when troubleshooting:

```bash
php artisan optimize:clear
```

Run the test suite:

```bash
composer run test
```

Alternatively:

```bash
php artisan test
```

---

## Coding Standards

Follow `.editorconfig`:

* UTF-8 encoding
* LF line endings
* Four-space indentation
* Final newline
* No unnecessary trailing whitespace
* Preserve intentional Markdown whitespace

Follow Laravel and PSR conventions:

* Use `StudlyCase` for classes.
* Use `camelCase` for methods and variables.
* Use `snake_case` for database columns.
* Use singular names for Eloquent models.
* Use plural names for database tables.
* Use descriptive class names such as:

  * `FormerRebelController`
  * `StoreFormerRebelRequest`
  * `RcspBarangayPolicy`
  * `AssistanceMonitoringService`
  * `DocumentSubmissionNotification`

Format PHP files before submission:

```bash
vendor/bin/pint
```

Avoid:

* Large controllers containing business logic
* Raw SQL when Eloquent or the query builder is sufficient
* Duplicated validation rules
* Hard-coded role names throughout the codebase
* Business logic directly inside Blade templates
* Unexplained magic numbers or status values
* Unnecessary third-party packages

---

## Architecture and Implementation Rules

### Controllers

Controllers should remain thin. Their main responsibilities are:

1. Receive the request.
2. Call validated form requests.
3. Check authorization.
4. Delegate complex operations to services or actions.
5. Return a response or view.

Do not place long calculations, multi-step workflows, or repeated database operations directly in controllers.

### Form Requests

Use Form Request classes for:

* Input validation
* Request-level authorization
* Custom validation messages
* Normalizing submitted values

Do not rely only on frontend validation.

### Models

Models may contain:

* Relationships
* Attribute casts
* Query scopes
* Simple domain helpers
* Status constants or enums

Always define appropriate casts for:

* Dates and timestamps
* Boolean fields
* JSON or array fields
* Encrypted attributes, when applicable

Prevent mass-assignment vulnerabilities by maintaining `$fillable` or `$guarded` carefully.

### Services and Actions

Move reusable or multi-step business processes into service or action classes, including:

* Former rebel enrollment
* RCSP lifecycle updates
* Assistance validation
* Document processing
* Report generation
* Notification dispatching
* Audit logging
* File management

Use database transactions when an operation modifies multiple related records.

### Blade Views

Blade templates should focus on presentation.

Use:

* Components for repeated UI elements
* Partials for reusable form sections
* Named routes instead of hard-coded URLs
* `@can`, `@cannot`, or policies for conditional actions
* Escaped output through `{{ }}` by default

Do not expose sensitive data in hidden fields, HTML comments, JavaScript variables, or unauthorized page elements.

---

## Domain and Workflow Rules

SH1ELD contains government and reintegration workflows. Do not simplify, bypass, or redesign a workflow without confirming its intended process.

When modifying a module:

* Preserve the existing approval sequence.
* Preserve record ownership and agency responsibility.
* Confirm who may create, view, update, approve, reject, archive, or export records.
* Record important status changes.
* Avoid silently changing official records.
* Do not delete historical information merely because a current record was updated.
* Use clear statuses rather than ambiguous Boolean fields for multi-step processes.
* Keep user-facing terminology consistent throughout the system.

Examples of important domain areas include:

* Former rebel profiles and enrollment
* RCSP barangay monitoring
* Reintegration progress
* Assistance and intervention records
* E-CLIP and Amnesty-related records
* Firearms and document processing
* Agency submissions and approvals
* Geotagged locations
* Reports and descriptive analytics
* Notifications and audit history

Do not invent government program rules, eligibility requirements, office responsibilities, cluster functions, or approval procedures. Implement only requirements supported by the project specifications.

---

## Authentication and Authorization

All protected features must enforce authorization on the server.

Do not rely only on:

* Hidden buttons
* Disabled form controls
* Navigation visibility
* Frontend route guards

Use the appropriate combination of:

* Authentication middleware
* Role or permission middleware
* Policies
* Gates
* Form Request authorization
* Query restrictions

Every protected action should answer:

1. Is the user authenticated?
2. Does the user have the required role or permission?
3. Is the user allowed to access this specific record?
4. Is the record in a status that allows the requested action?

Prevent insecure direct object reference vulnerabilities by authorizing the actual model instance.

Never return records from agencies, provinces, municipalities, offices, or assignments outside the authenticated user's authorized scope.

---

## Database and Migration Guidelines

All schema changes must use Laravel migrations.

Migration requirements:

* Use descriptive migration names.
* Add indexes to frequently filtered foreign keys and status fields.
* Define foreign-key constraints where appropriate.
* Specify nullable fields intentionally.
* Include safe rollback logic in `down()`.
* Avoid destructive changes without a migration plan.
* Preserve existing production data.
* Use transactions for sensitive data migrations when supported.
* Do not edit an old migration that may already have run in another environment.

Use seeders for:

* Roles
* Permissions
* Reference data
* Development accounts
* Required system statuses

Do not place actual confidential or personally identifiable information in seeders, factories, screenshots, or test fixtures.

When adding a status column, prefer an enum, constant-backed value, or documented reference table rather than unexplained strings.

---

## File Upload Guidelines

Uploaded files may contain sensitive documents.

All file-handling changes must:

* Validate file type, extension, and size.
* Generate safe server-side filenames.
* Prevent path traversal.
* Store private files outside publicly accessible directories.
* Authorize every file download or preview.
* Avoid trusting the original filename.
* Prevent executable file uploads.
* Handle missing files gracefully.
* Record upload and replacement activity when required.

Use Laravel storage disks instead of manually constructing filesystem paths.

Public access through `storage:link` should only be used for files intentionally classified as public.

---

## Logging and Audit Trails

Important activities should be traceable, particularly:

* Record creation
* Profile updates
* Status changes
* Submission, approval, rejection, or return
* Document uploads and replacements
* Assistance updates
* Data exports
* Administrative changes
* Permission or role changes

Audit records should identify, when applicable:

* User
* Action
* Affected record
* Previous value
* New value
* Date and time
* Relevant office or agency
* IP address or request context

Do not store passwords, tokens, complete confidential documents, or unnecessary sensitive data in application logs.

---

## Testing Guidelines

Use Laravel's PHPUnit-based test runner.

Place tests according to responsibility:

```text
tests/Feature
```

Use for:

* Authentication
* Authorization
* HTTP requests
* Form validation
* Database workflows
* File uploads
* Role-specific interfaces
* Notifications
* Reports and exports

```text
tests/Unit
```

Use for:

* Services
* Helpers
* Calculations
* Status transitions
* Data transformation
* Isolated domain rules

Use descriptive test names, such as:

```php
test_authorized_lgu_user_can_update_rcsp_record()
test_unauthorized_user_cannot_view_former_rebel_profile()
test_approved_submission_cannot_be_edited_without_permission()
test_uploaded_document_rejects_unsupported_file_types()
```

Add regression tests when changing:

* Role-based access
* Record ownership
* Approval workflows
* Validation
* Migrations
* File handling
* Notifications
* Report filters
* Sensitive-data visibility

Tests should verify both successful and prohibited behavior.

Run before submitting changes:

```bash
vendor/bin/pint --test
php artisan test
npm run build
```

---

## Security and Privacy Requirements

SH1ELD handles sensitive personal and government-related records. Security requirements are mandatory.

Never:

* Commit `.env`
* Commit credentials, tokens, API keys, or private certificates
* Log passwords or sensitive documents
* Expose personal information through URLs
* Return unrestricted model collections
* Disable authorization to make a feature work
* Use unvalidated request values in file paths or queries
* Store plaintext passwords
* Include production data in tests or screenshots
* Expose detailed exceptions in production

Use:

* Laravel validation
* Policies and middleware
* CSRF protection
* Parameterized queries
* Secure password hashing
* Encryption where appropriate
* Rate limiting for sensitive actions
* Signed or authorized download routes
* Minimal data exposure in API and view responses

Document required environment variables in `.env.example` using safe placeholder values.

---

## Frontend and Accessibility Guidelines

Keep the interface consistent with existing SH1ELD screens.

UI changes should:

* Work on desktop and mobile layouts.
* Display clear success and error messages.
* Preserve entered form values after validation errors.
* Use labels for all input fields.
* Use accessible buttons and links.
* Show loading or disabled states during submissions.
* Confirm destructive actions.
* Avoid communicating status through color alone.
* Use clear wording appropriate for government users.
* Keep dashboards readable and avoid unnecessary visual clutter.

Do not introduce a new design system unless requested.

---

## Routes and API Responses

Use named routes:

```php
route('former-rebels.show', $formerRebel)
```

Prefer route model binding where appropriate.

Group routes using relevant middleware:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Protected routes
});
```

For JSON responses:

* Use consistent response structures.
* Return appropriate HTTP status codes.
* Do not expose stack traces or internal implementation details.
* Return only fields required by the requesting user.
* Apply authorization before serialization.

---

## Commit Guidelines

Use short, imperative commit subjects.

Examples:

```text
Add RCSP record validation
Restrict former rebel profile access
Create assistance monitoring service
Fix document upload authorization
Add tests for agency-level permissions
```

Keep commits focused on one logical change.

Avoid vague subjects such as:

```text
Update files
Fix things
Changes
Final update
```

---

## Pull Request Guidelines

Each pull request should include:

* A clear summary of the change
* The reason for the change
* Affected modules and roles
* Test results
* Related issue or task reference
* Screenshots for UI changes
* Migration or seeding instructions
* New environment variables
* Queue, storage, or scheduler requirements
* Security or data-access implications

Clearly identify breaking changes and manual deployment steps.

---

## Agent Working Rules

Before editing code:

1. Inspect the relevant routes, controllers, models, requests, policies, views, migrations, and tests.
2. Follow existing project patterns before introducing new abstractions.
3. Identify all user roles affected by the change.
4. Confirm authorization and data-visibility requirements.
5. Check whether the change affects migrations, queues, notifications, files, reports, or audit logs.

While editing:

* Make the smallest complete change that solves the task.
* Do not rewrite unrelated files.
* Do not remove working functionality without a stated requirement.
* Preserve backward compatibility where practical.
* Avoid installing packages when the framework already provides the needed functionality.
* Add comments only when the reason behind the code is not obvious.
* Update related tests and documentation.

After editing:

1. Format the changed files.
2. Run the relevant tests.
3. Run the complete test suite when practical.
4. Build frontend assets when frontend files changed.
5. Review authorization and privacy implications.
6. Report migrations, seeders, environment variables, or commands required to apply the change.

---

## Definition of Done

A task is complete only when:

* The requested behavior is implemented.
* Server-side validation is present.
* Authorization is enforced.
* Sensitive data remains protected.
* Existing workflows are preserved.
* Relevant tests are added or updated.
* PHP formatting passes.
* The frontend build passes when applicable.
* Migrations include safe rollback behavior.
* Required setup or deployment instructions are documented.
* No credentials, temporary files, debug output, or production data are committed.
