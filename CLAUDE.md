# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Daily Finance is a **Laravel 12** personal finance tracker with a two-tier UX:
- **Matius** (case-insensitive) gets the full analytics dashboard at `/dashboard`
- **All other users** get a simple daily tracker at `/`

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
- `POST /login` — Set username in session, redirect to dashboard (Matius) or tracker
- `GET /logout` — Clear session
- All other routes protected by `CheckUsername` middleware

### Controller (`app/Http/Controllers/FinanceController.php`)
Single controller handling all business logic:
- `dashboard()` — Analytics for Matius: filters, monthly trend, savings/income splits, need-vs-want categorization, badges, streak tracking
- `index()` — Daily tracker view (non-Matius users)
- `store()` / `update()` / `destroy()` — CRUD on transactions
- `history()` — Grouped transaction history
- `edit()` — Edit form

### Models
- **Transaction** — Core model. Fields: `description`, `amount`, `type` (`in`/`out`), `transaction_date`, `username`. Optional fields: `is_split`, `save_pct`, `spend_pct`, `saved_amount`, `spent_amount`, `split_preset`, `savings_distribution` (JSON), `savings_allocated`, `need_or_want`, `expense_category`

### Migrations
- `create_transactions_table` — Core fields
- `add_username_to_transactions_table` — Multi-user support
- `add_split_and_category_fields_to_transactions_table` — Income splitting and expense categorization
- `add_savings_distribution_fields_to_transactions_table` — Savings allocation tracking

## Key Conventions

- **User routing**: Hardcoded check `strtolower(session('username')) === 'matius'` gates the dashboard. This check appears in multiple places (routes, controller, views) — be consistent when modifying.
- **Redirect logic after mutations**: Controller actions redirect to `/dashboard` for Matius, back to the date's tracker for others.
- **Asset pipeline**: `vite.config.js` bundles `resources/css/app.css` and `resources/js/app.js`. Build output goes to `public/build/`.
- **CSS**: Tailwind v4 with `@import 'tailwindcss'` (no config file — uses `@theme` block in `app.css`).
- **Fonts**: Instrument Sans (configured in `@theme`).
- **DB migrations**: Use `php artisan migrate` after pulling changes. New migrations added on 2026-09-07 for split/category features.
