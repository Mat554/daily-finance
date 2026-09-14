# Monthly Financial Health Features — Research

## Context

Daily Finance currently tracks individual income/expense transactions but lacks **monthly financial context**. Users who don't budget regularly miss three critical questions:

1. "How long will my expenses last me?"
2. "How much do I actually have left this month after everything?"
3. "What should I do differently next month?"

This document researches adding: monthly expense management, daily income/outcome running totals, monthly net balance calculation, savings tracking, and a monthly financial condition report with improvement suggestions.

---

## 1. Monthly Recurring Expenses

### Purpose
Track fixed and variable monthly expenses (rent, subscriptions, utilities, groceries, etc.) with allocated budgets. Users see how many months their money lasts against these commitments.

### Core Concepts

| Concept | Description |
|---|---|
| **Expense** | A named budget item (e.g. "Groceries", "Rent", "Internet") |
| **Allocated Amount** | Budget set aside for this expense per month |
| **Category** | Fixed (rent, insurance) vs. Variable (groceries, transport) |
| **Duration Tracking** | How many months the allocated budget has been consumed by actual spending |
| **Rollover** | Whether unspent budget rolls to next month |

### Behavior

- User creates monthly expenses with a name + allocated amount
- When a transaction matches an expense (by description keyword or explicit link), it deducts from the allocated budget
- The system shows: "Groceries — Rp 1,500,000 allocated. Rp 1,200,000 spent (80%). 1.2 months covered."
- When actual spending exceeds allocation, it shows a warning
- Users can mark an expense as "paused" or "completed" (seasonal expenses)

### Edge Cases

- Variable expenses (groceries) are harder to predict — show a 3-month rolling average
- Multiple transactions may map to one expense
- Expense allocation should not conflict with existing transactions — it's a budget overlay, not a spending gate

---

## 2. Daily Income/Outcome Running Tally

### Purpose
Give users a quick read of "today's financial picture" — how much came in, how much went out, and the net for that specific day.

### Current State
The tracker (`/`) shows daily transactions and computes `totalIn - totalOut` for that day.

### What's Missing

- **Running balance**: After each transaction, show cumulative net for the current month
- **vs. Monthly budget**: Compare today's net to the monthly average
- **vs. Same day last month**: Context on whether today was better or worse

### Implementation

- Compute running monthly net as: `Σ(income) - Σ(expenses)` from month start to today
- This can be computed on-the-fly from existing transactions — no new model needed
- Display as a summary bar on the tracker: "This month so far: +Rp 3.2M net"

---

## 3. Monthly Net Balance (End-of-Month Calculation)

### Purpose
At month close (or anytime), show the complete picture: income minus all outgoing money minus monthly expenses.

### Formula

```
Monthly Net = Total Income
            - Total Money Out (all transactions where type = 'out')
            - Monthly Expense Allocations Used
            - Savings Put Aside
            = Available Cash for Next Month
```

### States

| Condition | Label | Color |
|---|---|---|
| Net > allocated savings | **Surplus** | Green |
| Net = allocated savings | **Break Even** | Yellow |
| Net < 0 | **Deficit** | Red |
| Net between 0 and savings target | **Under-Saved** | Orange |

### Display
Show on dashboard as a prominent monthly summary card:
- "August 2026 — Surplus Rp 1,200,000"
- Breakdown: Income Rp 8M, Expenses Rp 4.5M, Savings Target Rp 2.5M, Net = +Rp 1M

---

## 4. Savings Tracking & Recommendations

### Purpose
Move beyond the current split-based savings (save% of income) to a **target-based savings** system. Users set a monthly savings goal and track progress toward it.

### Core Concepts

| Concept | Description |
|---|---|
| **Savings Goal** | Monthly amount user wants to save |
| **Current Savings** | Actual money set aside this month (from income splits + manual transfers) |
| **Savings Progress** | % of goal achieved |
| **Savings Velocity** | How fast they're saving — will they hit the goal? |
| **Projected Month-End Savings** | Based on current pace, will they hit the goal? |

### Behavior

- User sets a monthly savings goal (e.g. Rp 2,000,000)
- System tracks `saved_amount` from split income transactions
- Shows: "Rp 1,400,000 saved of Rp 2,000,000 goal (70%) — on track to hit your goal!"
- If behind pace: "At this rate you'll save Rp 1,800,000 — Rp 200,000 short of your goal"
- Savings velocity can use a simple linear projection or a weighted 7-day average

### Conflict with Existing Split System
The current app already has `save_pct` / `saved_amount` per income transaction. This feature should **integrate with** (not replace) that — use the existing `saved_amount` as the source of truth for savings progress.

---

## 5. Monthly Financial Condition Report

### Purpose
An AI-free, rules-based monthly summary that tells users:
1. How financially healthy this month was
2. What went well
3. What could improve next month

### Report Sections

#### A. Monthly Score (0-100)
Computed from weighted factors:

| Factor | Weight | Logic |
|---|---|---|
| Savings rate vs. target | 30% | Did they hit their savings goal? |
| Need vs. Want ratio | 20% | Are they spending too much on wants? |
| Expense coverage | 20% | Did expenses stay within allocated budgets? |
| Income consistency | 15% | Did they receive income this month? |
| No deficit months | 15% | Did they avoid going into negative net? |

#### B. What Went Well (auto-generated bullets)
- "You saved 72% of your income — above your 60% target!"
- "Your grocery spending was Rp 200,000 under budget"
- "You maintained a savings streak for 3 consecutive months"

#### C. Areas to Improve (auto-generated bullets)
- "Your entertainment expenses exceeded budget by 40%"
- "You only saved 23% of income — aim for at least 30%"
- "Your utility bills were Rp 500,000 higher than last month"

#### D. Next Month Recommendations
- Specific, actionable suggestions based on data:
  - "Consider reducing dining-out budget by Rp 300,000 to cover the utility increase"
  - "Your savings rate dropped 15% this month — review any new recurring expenses"
  - "You're on track to exceed your grocery budget by month-end. Consider meal planning."

---

## 6. Data Model Changes

### Option A: New `Expense` Model (Recommended)
Create a separate `expenses` table for monthly budget tracking.

```php
// app/Models/Expense.php
[
    'id',
    'username',
    'name',              // "Groceries", "Rent"
    'category',          // 'fixed' | 'variable'
    'allocated_amount',  // monthly budget
    'keywords',         // JSON array of matching transaction keywords
    'is_active',        // boolean
    'rollover',         // boolean — unused budget rolls to next month?
    'created_at',
    'updated_at',
]
```

**Pros:** Clean separation from transactions; flexible keyword matching
**Cons:** New table + migration needed

### Option B: Extend Transaction Model
Add fields to track expense linkage directly on transactions.

**Cons:** Bloats the transaction model; doesn't capture the "budget" concept well

### Option C: AccountBalance as Monthly Snapshot
Use a variant of `AccountBalance` to track monthly net positions.

**Cons:** Doesn't handle expense budget tracking

### Recommendation: Option A (new `Expense` model)

---

### New `MonthlyReport` Model

```php
// app/Models/MonthlyReport.php
[
    'id',
    'username',
    'year',
    'month',
    'total_income',
    'total_expenses',
    'total_money_out',
    'total_saved',
    'net_balance',
    'condition',        // 'surplus' | 'break_even' | 'deficit' | 'under_saved'
    'score',            // 0-100
    'summary_json',     // { went_well: [], improvements: [], recommendations: [] }
    'created_at',
    'updated_at',
]
```

**Key decision:** Should monthly reports be auto-generated on month-end (via a scheduled job), or generated on-demand when user visits the dashboard? **On-demand + cached** — generate when first accessed, store the result. User can regenerate with a "Refresh Report" button.

---

## 7. UX / UI Changes

### New Pages / Sections

| Location | Change |
|---|---|
| Dashboard | New "Monthly Financial Report" card at top |
| Dashboard | "Monthly Net" summary card |
| Dashboard | "Savings Progress" bar with goal indicator |
| `/expenses` (new) | Monthly expense CRUD (create, edit, pause, delete) |
| `/monthly-report/{year}/{month}` (new) | Full monthly report view |

### Dashboard Widgets (new)

1. **Monthly Net Card**
   - Current month: income - outgoings - expenses used
   - Color-coded by condition

2. **Savings Progress Bar**
   - Shows current savings vs. goal
   - Velocity indicator (on track / behind / ahead)

3. **Expense Budget Cards**
   - Row per expense: name, allocated, spent, remaining
   - Progress bar per expense
   - Warning if overspent

4. **Monthly Report Button**
   - "View August 2026 Report" CTA
   - Badge if report is new/unread

---

## 8. Routes

```
GET  /expenses                     — List monthly expenses
POST /expenses                     — Create expense
PUT  /expenses/{expense}           — Update expense
DELETE /expenses/{expense}         — Delete expense
POST /expenses/{expense}/toggle    — Pause/unpause expense

GET  /savings-goal                 — View/edit monthly savings goal
POST /savings-goal                 — Set savings goal

GET  /monthly-report/{year}/{month} — View monthly report
POST /monthly-report/generate       — Generate/cached monthly report for a given month
```

---

## 9. Key Implementation Considerations

1. **Keyword matching for expenses**: When a user logs a transaction, the system can auto-link it to an expense by keyword. E.g. transaction "GoFood order" matches expense "Food" if "gofood" is in keywords. Show a gentle prompt: "Did this transaction count toward your Groceries budget?"

2. **No automatic deductions**: Expenses are a **budget overlay**, not a gate. Users can still log transactions freely — the expense budget just tracks and warns.

3. **Existing data compatibility**: The monthly report can work with existing transactions. The `Expense` model is purely additive.

4. **Savings velocity**: Use a simple approach — `saved_amount / days_elapsed * 30` to project end-of-month savings. More complex: weighted average of last 2 months.

5. **Monthly report generation**: Run on-demand, cache the result. Regenerate if user adds/edits transactions after the report was generated.

6. **Threshold alerts**: If savings rate drops below a threshold or expenses exceed budget, show a subtle banner — but keep it non-intrusive for casual users.

---

## 10. Phasing Recommendation

### Phase 1 — Core (smallest scope, highest impact)
- Add `Expense` model + CRUD (`/expenses`)
- Show expense budget progress on dashboard
- Auto-link transactions to expenses by keyword

### Phase 2 — Monthly Balance
- Add `MonthlyReport` model
- Show monthly net card on dashboard
- Monthly report view page

### Phase 3 — Savings Tracking
- Add savings goal setting
- Savings progress bar on dashboard
- Velocity projection

### Phase 4 — Full Report
- Monthly score calculation
- Auto-generated bullets (went well / improvements)
- Recommendations engine
- Monthly report with all sections
