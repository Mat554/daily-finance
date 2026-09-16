# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Daily Finance is a **Laravel 12** personal finance tracker with two views:
- **`/`** — Simple daily tracker
- **`/dashboard`** — Full analytics dashboard

Every logged-in user can access both. Users choose their default landing (tracker or dashboard) via the in-app toggle, stored in `session('landing')`.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+, PostgreSQL (prod) / SQLite (local)
- **Frontend**: Blade templates, Tailwind CSS v4, Vite, plain JavaScript with Axios
- **Auth**: Session-based with User model. "Mama" logs in with username only. All other users log in with username or email + password. Self-service password reset available via `/forgot-password`.
- **Deployment**: Vercel (configured via `vercel.json` with `npm run build` as build command)

## ⚠️ Database Safety Rules

**CRITICAL: Never run `migrate:fresh` or any command that wipes the database.** This destroys all user data permanently (transactions, expenses, reports, account balances).

- `php artisan migrate` — safe, runs incremental migrations
- `php artisan migrate:fresh` — **NEVER run** — drops all tables and wipes all data
- `php artisan migrate:fresh --seed` — **NEVER run** — same as above plus reseeds
- `php artisan db:seed` — safe, seeds into existing data
- Any `DROP TABLE` or `TRUNCATE` — **NEVER run** without explicit user confirmation

If the user asks to reset or clean the database, stop and ask for confirmation before proceeding. Explain that `migrate:fresh` permanently deletes all data.



```bash
# Install dependencies
composer install
npm install

# Local development (runs all services concurrently)
composer run dev

# Run individually
php artisan serve          # Laravel dev server
npm run dev                # Vite hot-reload
php artisan queue:listen   # Background queue worker

# Build assets for production
npm run build

# Run tests
composer test

# Run migrations
php artisan migrate

# Reset DB (fresh migrate + seed)
php artisan migrate:fresh --seed

# Manage user passwords (seeded users only, no self-service)
php artisan user:password <username> <password>
```

## Architecture

### Auth System
- `AuthController` handles login (GET/POST), logout (POST), register (GET/POST), and forgot-password (GET/POST)
- `CheckUsername` middleware checks `session('user_id')` and `session('username')`
- **"Mama"**: username-only login, no password needed
- **All other users**: login with username or email + password
- Users register with username + email + password
- Session stores: `user_id` (User primary key), `username` (for data isolation), `landing` (preference)

### Controllers (`app/Http/Controllers/`)
| Controller | Purpose |
|---|---|
| `AuthController` | Login/logout |
| `TrackerController` | Daily tracker + history |
| `TransactionController` | CRUD + income splitting + savings distribution |
| `DashboardController` | Analytics, distribution templates, account balances |
| `SettingsController` | Landing preference toggle |
| `ExpenseController` | Monthly budget categories + keyword matching |
| `ExpensePaymentController` | Payment logging for expenses |
| `MonthlyReportController` | 5-factor score, monthly summaries |
| `SavingsGoalController` | Session-stored savings goal + progress |

### Models
| Model | Purpose |
|---|---|
| `Transaction` | Core — description, amount, type, date, username + split/distribution/category fields |
| `Expense` | Monthly budget — name, category, allocated amount, keywords (JSON), due dates, credit tracking |
| `ExpensePayment` | Payments for expenses |
| `MonthlyReport` | Generated reports per month — score, condition, summary |
| `AccountBalance` | Per-account balances — Cash/Bank/E-Wallet/Savings |
| `User` | Auth — username (unique), email (unique), name, password (nullable), password_change_required |

### Routes (`routes/web.php`)
- `GET /login` — Login form
- `POST /login` — Authenticate (username or email + password), set session, redirect per landing preference
- `GET /register` — Registration form
- `POST /register` — Create account (username + email + password)
- `GET /forgot-password` — Reset password form
- `POST /forgot-password` — Reset password (find by username or email)
- `POST /logout` — Clear auth session (GET redirects to /login)
- All other routes protected by `CheckUsername` middleware

### Migrations
- `create_users_table` — Users table (id, username, name, password, password_change_required)
- `add_email_to_users_table` — Email column (nullable, unique)
- `create_transactions_table` — Core fields
- `add_username_to_transactions_table` — Multi-user support
- `add_split_and_category_fields_to_transactions_table` — Income splitting and expense categorization
- `add_savings_distribution_fields_to_transactions_table` — Savings allocation tracking
- `add_account_type_to_transactions_table` — Account balance tracking
- `create_expenses_table` + related — Monthly budget management
- `create_monthly_reports_table` — Monthly financial reports
- `create_expense_payments_table` — Payment logging
- `ensure_mama_user_exists` — Seeds Mama into users table for existing installations

### Key Conventions

- **Data isolation**: `session('username')` is the key for all queries. Controllers use `Transaction::where('username', session('username'))`.
- **Landing preference**: `session('landing')` defaults to `'tracker'`. Mutations redirect based on this.
- **Session-stored preferences**: Landing preference, savings goal, and distribution template all live in PHP session.
- **No special usernames**: Every logged-in user can reach `/dashboard`. No hardcoded username gates.
- **Asset pipeline**: `vite.config.js` bundles `resources/css/app.css` and `resources/js/app.js`. Build output goes to `public/build/`.
- **CSS**: Tailwind v4 with `@import 'tailwindcss'` (no config file — uses `@theme` block in `app.css`).
- **Fonts**: Instrument Sans (configured in `@theme`).
