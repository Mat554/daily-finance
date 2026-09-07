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
- **Auth**: Simple session-based — no passwords, just a username entered on `/login`
- **Deployment**: Vercel (configured via `vercel.json`)

## Commands

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

# Clear config cache (before running tests)
php artisan config:clear
```

## Architecture

### Routes (`routes/web.php`)
- `GET /login` — Login form
- `POST /login` — Set username + default landing preference in session, redirect per preference
- `POST /preference` — Toggle default landing between `tracker` and `dashboard` (session-only)
- `GET /logout` — Clear username from session (keeps landing preference)
- All other routes protected by `CheckUsername` middleware

### Controller (`app/Http/Controllers/FinanceController.php`)
Single controller handling all business logic:
- `dashboard()` — Analytics: filters, monthly trend, savings/income splits, need-vs-want categorization, badges, streak tracking
- `index()` — Daily tracker view
- `store()` / `update()` / `destroy()` — CRUD on transactions (redirects via `redirectAfterAction()`)
- `history()` — Grouped transaction history
- `edit()` — Edit form
- `setPreference()` — Toggle `session('landing')` between `tracker` and `dashboard`
- `redirectAfterAction()` — Private helper; redirects to dashboard or `/?date=...` based on `session('landing')`

### Models
- **Transaction** — Core model. Fields: `description`, `amount`, `type` (`in`/`out`), `transaction_date`, `username`. Optional fields: `is_split`, `save_pct`, `spend_pct`, `saved_amount`, `spent_amount`, `split_preset`, `savings_distribution` (JSON), `savings_allocated`, `need_or_want`, `expense_category`

### Migrations
- `create_transactions_table` — Core fields
- `add_username_to_transactions_table` — Multi-user support
- `add_split_and_category_fields_to_transactions_table` — Income splitting and expense categorization
- `add_savings_distribution_fields_to_transactions_table` — Savings allocation tracking

## Key Conventions

- **Landing preference**: `session('landing')` defaults to `'tracker'` on every login. Users can switch via `POST /preference`. Mutations (store/update/destroy) redirect based on this preference via `FinanceController::redirectAfterAction()`.
- **No special usernames**: Every logged-in user can reach `/dashboard`. The `matius` gate is removed — no hardcoded username checks anywhere.
- **Asset pipeline**: `vite.config.js` bundles `resources/css/app.css` and `resources/js/app.js`. Build output goes to `public/build/`.
- **CSS**: Tailwind v4 with `@import 'tailwindcss'` (no config file — uses `@theme` block in `app.css`).
- **Fonts**: Instrument Sans (configured in `@theme`).
- **DB migrations**: Use `php artisan migrate` after pulling changes. New migrations added on 2026-09-07 for split/category features.
