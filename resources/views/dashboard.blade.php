<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Dashboard</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card {
            @apply bg-white rounded-3xl p-6 shadow-sm border border-gray-100;
        }

        /* ── Select dropdowns ──────────────────────────────────────────── */
        .dash-select {
            @apply appearance-none bg-white border border-gray-200 rounded-2xl px-4 py-2.5 pr-10 text-sm font-medium text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23888' viewBox='0 0 256 256'%3E%3Cpath d='M213.66 101.66l-80 80a8 8 0 01-11.32 0l-80-80a8 8 0 0111.32-11.32L128 164.69l74.34-74.35a8 8 0 0111.32 11.32z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        /* ── Donut chart ──────────────────────────────────────────────── */
        .donut-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .donut-center {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .donut-track { fill: none; stroke: #f1f5f9; stroke-width: 28; }
        .donut-swing  { fill: none; stroke-width: 28; stroke-linecap: round;
            transition: stroke-dashoffset 1.2s cubic-bezier(0.4, 0, 0.2, 1); }

        /* ── Bar chart ───────────────────────────────────────────────── */
        .bar-wrap {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 120px;
            padding: 0 4px;
        }
        .bar-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex: 1;
        }
        .bar-stack {
            display: flex;
            gap: 2px;
            align-items: flex-end;
            height: 96px;
            width: 100%;
            justify-content: center;
        }
        .bar-segment {
            width: 18px;
            border-radius: 4px 4px 0 0;
            transition: height 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            min-height: 4px;
        }
        .bar-segment.in  { background: #3b82f6; }
        .bar-segment.out { background: #f87171; }
        .bar-label {
            font-size: 9px;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .bar-group:hover .bar-segment { filter: brightness(0.9); }

        /* ── Gauge ─────────────────────────────────────────────────────── */
        .gauge-track {
            fill: none;
            stroke: #f1f5f9;
            stroke-width: 10;
            stroke-linecap: round;
        }
        .gauge-fill {
            fill: none;
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── Health pill ─────────────────────────────────────────────── */
        .health-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px 4px 8px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        /* ── Balance ticker ─────────────────────────────────────────── */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .count-anim { animation: countUp 0.6s ease-out forwards; }

        /* ── Grouped table ───────────────────────────────────────────── */
        .day-row {
            @apply flex items-center justify-between px-4 py-2.5 rounded-2xl cursor-pointer transition-all;
        }
        .day-row:hover { @apply bg-gray-50; }
        .day-row.open   { @apply bg-blue-50 border border-blue-100; }
        .day-items {
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease;
        }
        .day-items.open { max-height: 600px; }
        .tx-row {
            @apply flex items-center justify-between px-4 py-2 rounded-xl transition;
        }
        .tx-row:hover { @apply bg-gray-50; }

        /* ── Fun micro ───────────────────────────────────────────────── */
        .sparkle {
            animation: sparkle 2s ease-in-out infinite;
        }
        @keyframes sparkle {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.15); }
        }
        .float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-4px); }
        }
        .pulse-ring {
            animation: pulse-ring 2s ease-out infinite;
        }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.3); }
            70%  { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        /* ── Scrollbar ───────────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

        /* ── Modal overlay ─────────────────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.active {
            opacity: 1; pointer-events: auto;
        }
        .modal-box {
            background: white;
            border-radius: 2rem;
            padding: 2rem;
            max-width: 480px; width: 90%;
            transform: scale(0.9) translateY(20px);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        .modal-overlay.active .modal-box {
            transform: scale(1) translateY(0);
        }

        /* ── Split slider ──────────────────────────────────────────── */
        .split-track {
            position: relative;
            height: 14px;
            background: linear-gradient(to right, #22c55e 0%, #22c55e 50%, #3b82f6 50%, #3b82f6 100%);
            border-radius: 100px;
            margin: 1rem 0;
        }
        .slider-thumb {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 28px; height: 28px;
            background: white;
            border: 3px solid #fbbf24;
            border-radius: 50%;
            cursor: grab;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 900; color: #d97706;
            transition: box-shadow 0.2s, left 0.1s;
            z-index: 2;
        }
        .slider-thumb:active { cursor: grabbing; box-shadow: 0 4px 16px rgba(251,191,36,0.5); }

        /* ── Split / category cards ────────────────────────────────── */
        .choice-card {
            border-radius: 1.5rem;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            cursor: pointer;
        }
        .choice-card.save  { background: #dcfce7; border-color: #bbf7d0; }
        .choice-card.spend { background: #dbeafe; border-color: #bfdbfe; }
        .choice-card.need  { background: #dcfce7; border-color: #bbf7d0; }
        .choice-card.want  { background: #fce7f3; border-color: #fbcfe8; }
        .choice-card.active {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .choice-card:hover { filter: brightness(0.95); }

        /* ── Preset pills ──────────────────────────────────────────── */
        .preset-pill {
            padding: 6px 14px; border-radius: 100px;
            font-size: 12px; font-weight: 700;
            border: 2px solid; transition: all 0.2s;
            cursor: pointer;
            background: white;
        }
        .preset-pill.balanced { border-color: #a78bfa; color: #7c3aed; }
        .preset-pill.saver   { border-color: #4ade80; color: #16a34a; }
        .preset-pill.spender { border-color: #f472b6; color: #db2777; }
        .preset-pill:hover { transform: scale(1.05); filter: brightness(0.95); }

        /* ── Confetti ──────────────────────────────────────────────── */
        @keyframes confetti-fall {
            0%   { transform: translateY(0) rotate(0deg) translateX(0); opacity: 1; }
            100% { transform: translateY(400px) rotate(720deg) translateX(var(--tx, 50px)); opacity: 0; }
        }
        .confetti-particle {
            position: fixed;
            width: 8px; height: 8px;
            border-radius: 2px;
            animation: confetti-fall 2s ease-out forwards;
            z-index: 9999; pointer-events: none;
        }

        /* ── Coin flip ────────────────────────────────────────────── */
        @keyframes coin-flip {
            0%   { transform: rotateY(0deg) scale(1); }
            30%  { transform: rotateY(180deg) scale(1.1); }
            50%  { transform: rotateY(360deg) scale(1.1); }
            70%  { transform: rotateY(540deg) scale(1.05) translateY(-8px); }
            100% { transform: rotateY(720deg) scale(1) translateY(0); }
        }
        .coin-flip { animation: coin-flip 0.8s ease-in-out forwards; }

        /* ── Toast ────────────────────────────────────────────────── */
        @keyframes toast-in {
            0%   { transform: translateX(120%); opacity: 0; }
            10%  { transform: translateX(0); opacity: 1; }
            85%  { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(120%); opacity: 0; }
        }
        .toast {
            position: fixed; top: 80px; right: 20px;
            z-index: 200;
            background: white;
            border-radius: 1rem;
            padding: 0.875rem 1.25rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            animation: toast-in 4s ease-in-out forwards;
            border-left: 4px solid #22c55e;
            max-width: 320px;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .toast.wan  { border-left-color: #f472b6; }
        .toast.info { border-left-color: #3b82f6; }

        /* ── Badge bounce ─────────────────────────────────────────── */
        @keyframes badge-bounce {
            0%   { transform: scale(0); opacity: 0; }
            60%  { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .badge-new { animation: badge-bounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        /* ── Fire flicker ─────────────────────────────────────────── */
        @keyframes fire-flicker {
            0%, 100% { transform: scale(1) rotate(-3deg); }
            50%       { transform: scale(1.15) rotate(3deg); }
        }
        .fire-icon { display: inline-block; animation: fire-flicker 0.4s ease-in-out infinite; }

        /* ── Golden glow for high savings ─────────────────────────── */
        @keyframes golden-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(251,191,36,0); }
            50%       { box-shadow: 0 0 20px 4px rgba(251,191,36,0.4); }
        }
        .high-savings { animation: golden-pulse 2s ease-in-out infinite; }

        /* ── Category chips ───────────────────────────────────────── */
        .cat-chip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 100px;
            font-size: 12px; font-weight: 700;
            cursor: pointer; transition: all 0.2s;
            border: 2px solid transparent;
        }
        .cat-chip.need  { background: #dcfce7; color: #16a34a; }
        .cat-chip.want  { background: #fce7f3; color: #db2777; }
        .cat-chip:hover { transform: scale(1.05); filter: brightness(0.95); }
        .cat-chip.selected { border-color: currentColor; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

        /* ── Need/Want toggle ─────────────────────────────────────── */
        .nw-toggle {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
        }
        .nw-btn {
            padding: 1rem; border-radius: 1.5rem;
            border: 3px solid transparent; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }
        .nw-btn.need { background: #f0fdf4; }
        .nw-btn.want { background: #fdf2f8; }
        .nw-btn.active.need { border-color: #22c55e; background: #dcfce7; }
        .nw-btn.active.want { border-color: #ec4899; background: #fce7f3; }
        .nw-btn:hover { filter: brightness(0.95); }

        /* ── Split row detail in transactions ──────────────────────── */
        .split-detail {
            margin-top: 4px; margin-left: 40px;
        }
        .split-bar {
            height: 5px; border-radius: 3px;
            background: linear-gradient(to right, #22c55e var(--save-pct), #3b82f6 var(--save-pct));
            margin-top: 6px; max-width: 200px;
        }

        /* ── CTA banners ───────────────────────────────────────────── */
        .cta-banner {
            border-radius: 1.5rem;
            padding: 1rem 1.25rem;
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem;
            border: 1px solid;
        }
        .cta-banner.unsplit { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-color: #fcd34d; }
        .cta-banner.uncat   { background: linear-gradient(135deg, #fdf2f8, #fce7f3); border-color: #f9a8d4; }

        /* ── Await categories reveal ────────────────────────────────── */
        .cat-reveal {
            overflow: hidden; max-height: 0; opacity: 0;
            transition: max-height 0.4s ease, opacity 0.3s ease, margin 0.3s ease;
        }
        .cat-reveal.show { max-height: 200px; opacity: 1; margin-top: 1rem; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white sticky top-0 z-50 shadow-lg shadow-blue-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center float">
                        <i class="ph ph-chart-line-up text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold leading-none">{{ session('username') }}'s Finance</h1>
                        <p class="text-xs text-blue-100 leading-none flex items-center gap-1">
                            <i class="ph ph-sparkle text-xs sparkle"></i>
                            Your personal finance hub
                        </p>
                    </div>
                </div>
                <nav class="flex items-center gap-1">
                    <form action="{{ route('preference') }}" method="POST" class="flex items-center" onsubmit="this._target.value = '{{ session('landing') === 'dashboard' ? '/' : '/dashboard' }}'">
                        @csrf
                        <input type="hidden" name="landing" value="{{ session('landing') === 'dashboard' ? 'tracker' : 'dashboard' }}">
                        <input type="hidden" name="_target" value="">
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium transition
                            {{ session('landing') === 'dashboard' ? 'bg-white/20 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}
                            border border-white/30 hover:bg-white/10" title="Switch to {{ session('landing') === 'dashboard' ? 'Tracker' : 'Dashboard' }}">
                            <i class="ph {{ session('landing') === 'dashboard' ? 'ph-house' : 'ph-chart-line-up' }} text-base"></i>
                            {{ session('landing') === 'dashboard' ? 'Default: Dashboard' : 'Default: Tracker' }}
                        </button>
                    </form>
                    <a href="/history" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm text-blue-100 hover:text-white hover:bg-white/10 transition font-medium">
                        <i class="ph ph-clock-counter-clockwise text-base"></i>
                        History
                    </a>
                    <a href="/logout" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm text-red-200 hover:text-red-100 hover:bg-red-500/20 transition font-medium ml-2">
                        <i class="ph ph-sign-out text-base"></i>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        <!-- Greeting -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Hey, {{ session('username') }}
                <span class="inline-block ml-1">
                    @if(now()->hour < 12) 🌅
                    @elseif(now()->hour < 18) ☀️
                    @else 🌙
                    @endif
                </span>
            </h2>
            <p class="text-sm text-gray-400 mt-0.5">
                {{ now()->format('l, j F Y') }}
                @if($transactionCount > 0)
                    &nbsp;·&nbsp; {{ $transactionCount }} transaction{{ $transactionCount != 1 ? 's' : '' }} tracked
                @endif
            </p>
        </div>

        <!-- Quick Add -->
        <div class="card">
            <form action="{{ route('store') }}" method="POST" class="flex gap-2 items-end flex-wrap sm:flex-nowrap">
                @csrf
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">
                        <i class="ph ph-pencil-simple mr-1"></i>Description
                    </label>
                    <input type="text" name="description" placeholder="Groceries, Salary, Coffee..." required
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition">
                </div>
                <div class="w-32 shrink-0">
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">
                        <i class="ph ph-coins mr-1"></i>Amount
                    </label>
                    <input type="number" name="amount" placeholder="0" required
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition">
                </div>
                <div class="w-36 shrink-0">
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">
                        <i class="ph ph-calendar mr-1"></i>Date
                    </label>
                    <input type="date" name="transaction_date" value="{{ \Carbon\Carbon::now()->toDateString() }}" required
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition">
                </div>
                <div class="w-32 shrink-0">
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">
                        <i class="ph ph-wallet mr-1"></i>Account
                    </label>
                    <select name="account_type" class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition">
                        <option value="">—</option>
                        @foreach(['Cash', 'Bank', 'E-Wallet', 'Savings'] as $acc)
                            <option value="{{ $acc }}">{{ $acc }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="openSplitModal(this.form)"
                    class="flex-1 bg-green-500 hover:bg-green-600 active:scale-95 text-white font-bold py-3 px-5 rounded-2xl transition-all text-sm shadow-lg shadow-green-200 hover:shadow-green-300 flex items-center justify-center gap-2">
                    <i class="ph ph-arrow-circle-down text-lg"></i>Money In
                </button>
                <button type="button" onclick="openExpenseModal(this.form)"
                    class="flex-1 bg-red-500 hover:bg-red-600 active:scale-95 text-white font-bold py-3 px-5 rounded-2xl transition-all text-sm shadow-lg shadow-red-200 hover:shadow-red-300 flex items-center justify-center gap-2">
                    <i class="ph ph-arrow-circle-up text-lg"></i>Money Out
                </button>
            </form>
        </div>

        <!-- Filters -->
        <div class="card flex flex-wrap items-center gap-3">
            <form id="filterForm" method="GET" action="/dashboard" class="flex flex-wrap items-center gap-3 flex-1">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <i class="ph ph-calendar-blank mr-1"></i>Period
                    </label>
                    <select name="filter" class="dash-select" onchange="this.form.submit()">
                        <option value="today"   {{ $filter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week"    {{ $filter === 'week'  ? 'selected' : '' }}>This Week</option>
                        <option value="month"   {{ $filter === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year"    {{ $filter === 'year'  ? 'selected' : '' }}>This Year</option>
                        <option value="all"     {{ $filter === 'all'   ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <i class="ph ph-tag mr-1"></i>Type
                    </label>
                    <select name="type" class="dash-select" onchange="this.form.submit()">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="in"  {{ $type === 'in'  ? 'selected' : '' }}>Money In</option>
                        <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Money Out</option>
                    </select>
                </div>

                <input type="date" name="from" value="{{ $from }}" placeholder="From"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onchange="this.form.submit()">
                <span class="text-gray-300 text-xs">–</span>
                <input type="date" name="to" value="{{ $to }}" placeholder="To"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onchange="this.form.submit()">

                <input type="text" name="q" value="{{ $search }}" placeholder="Search..."
                    class="border border-gray-200 rounded-xl px-3 py-2 text-xs w-40 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeydown="if(event.key==='Enter'){this.form.submit()}">

                @if($search || $from || $to)
                    <a href="/dashboard?filter={{ $filter }}&type={{ $type }}" class="text-xs text-red-400 hover:text-red-600 flex items-center gap-1 font-bold">
                        <i class="ph ph-x-circle"></i>Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- CTA: Unsplit income nudge -->
        @if($unsplitIn > 0)
        <div class="cta-banner unsplit">
            <div>
                <p class="text-sm font-bold text-amber-800 flex items-center gap-1">
                    <span>🎯</span> Rp {{ number_format($unsplitIn, 0) }} in unsplit income!
                </p>
                <p class="text-xs text-amber-600 mt-0.5">Split it now and start your Save/Spend journey.</p>
            </div>
            <button onclick="openSplitModalForAmount({{ $unsplitIn }}, 'Unsplit Income')"
                class="shrink-0 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow-sm">
                Split Now →
            </button>
        </div>
        @endif

        <!-- CTA: Uncategorized expense nudge -->
        @if($uncategorizedOut > 0)
        <div class="cta-banner uncat">
            <div>
                <p class="text-sm font-bold text-pink-700 flex items-center gap-1">
                    <span>🏷️</span> Rp {{ number_format($uncategorizedOut, 0) }} uncategorized!
                </p>
                <p class="text-xs text-pink-500 mt-0.5">Tag your expenses as Need or Want.</p>
            </div>
            <button onclick="openExpenseModalForAmount({{ $uncategorizedOut }}, 'Uncategorized')"
                class="shrink-0 bg-pink-500 hover:bg-pink-600 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow-sm">
                Categorize →
            </button>
        </div>
        @endif

        <!-- Stats + Donut + Gauge Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Card A: Save vs Spend -->
            <div class="card text-center">
                <h3 class="text-sm font-bold text-gray-700 mb-1">
                    <i class="ph ph-scales text-emerald-500 mr-1"></i>Save vs Spend
                </h3>
                <p class="text-xs text-gray-400 mb-3">Income allocation</p>

                @php
                    $splitCirc = 2 * 3.14159 * 50;
                    $svSsaveDash = $splitCirc * ($splitSavingsRate / 100);
                    $svSspendDash = $splitCirc * ((100 - $splitSavingsRate) / 100);
                @endphp

                <div class="flex justify-center mb-3">
                    <div class="donut-wrap" style="width:130px;height:130px">
                        <svg width="130" height="130" viewBox="0 0 130 130">
                            <circle class="donut-track" cx="65" cy="65" r="50" stroke-width="20"/>
                            @if($splitCount > 0)
                            <circle class="donut-swing" cx="65" cy="65" r="50" stroke="#22c55e"
                                stroke-dasharray="{{ $svSsaveDash }} {{ $splitCirc }}"
                                stroke-dashoffset="0" transform="rotate(-90 65 65)"/>
                            <circle class="donut-swing" cx="65" cy="65" r="50" stroke="#3b82f6"
                                stroke-dasharray="{{ $svSspendDash }} {{ $splitCirc }}"
                                stroke-dashoffset="{{ -$svSsaveDash }}" transform="rotate(-90 65 65)"/>
                            @endif
                        </svg>
                        <div class="donut-center">
                            <span class="text-2xl font-black text-gray-900 count-anim">
                                {{ $splitCount > 0 ? round($splitSavingsRate) : 0 }}%
                            </span>
                            <span class="text-[10px] text-gray-400">saved</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="bg-emerald-50 rounded-2xl p-2.5 text-center {{ $splitSavingsRate >= 60 && $splitCount > 0 ? 'high-savings' : '' }}">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-0.5">💰 Saved</p>
                        <p class="text-sm font-black text-emerald-700">Rp {{ number_format($totalSaved, 0) }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-2.5 text-center">
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">🛒 Allocated</p>
                        <p class="text-sm font-black text-blue-700">Rp {{ number_format($totalSpent, 0) }}</p>
                    </div>
                </div>

                @if($splitCount > 0)
                <p class="text-[10px] text-gray-400 mb-2">{{ $splitCount }} split{{ $splitCount != 1 ? 's' : '' }} tracked</p>
                @else
                <p class="text-[10px] text-gray-400 mb-2 italic">No splits yet</p>
                @endif

                <!-- Streak -->
                @if($streak > 0)
                <div class="bg-amber-50 rounded-xl py-1.5 px-3 mb-2">
                    <span class="fire-icon text-sm mr-1">🔥</span>
                    <span class="text-xs font-black text-amber-700">{{ $streak }}-day streak!</span>
                </div>
                @endif

                <!-- Badges -->
                @if(count($badges) > 0)
                <div class="flex flex-wrap gap-1 justify-center pt-1 border-t border-gray-50">
                    @foreach($badges as $badge)
                    <span class="px-2 py-0.5 bg-gradient-to-r {{ $badge['bg'] }} rounded-full text-[10px] font-bold flex items-center gap-0.5">
                        {{ $badge['icon'] }} {{ $badge['name'] }}
                    </span>
                    @endforeach
                </div>
                @endif

                <!-- Motivation -->
                <p class="text-[10px] italic text-gray-400 mt-2 leading-tight">{{ $saveVsSpendMessage }}</p>
            </div>

            <!-- Card B: Need vs Want -->
            <div class="card text-center">
                <h3 class="text-sm font-bold text-gray-700 mb-1">
                    <i class="ph ph-tag text-pink-500 mr-1"></i>Need vs Want
                </h3>
                <p class="text-xs text-gray-400 mb-3">Expense breakdown</p>

                @php
                    $nwCirc = 2 * 3.14159 * 50;
                    $nwNeedDash = $nwCirc * ($needPct / 100);
                    $nwWantDash = $nwCirc * ((100 - $needPct) / 100);
                @endphp

                <div class="flex justify-center mb-3">
                    <div class="donut-wrap" style="width:130px;height:130px">
                        <svg width="130" height="130" viewBox="0 0 130 130">
                            <circle class="donut-track" cx="65" cy="65" r="50" stroke-width="20"/>
                            @if($needCount + $wantCount > 0)
                            <circle class="donut-swing" cx="65" cy="65" r="50" stroke="#22c55e"
                                stroke-dasharray="{{ $nwNeedDash }} {{ $nwCirc }}"
                                stroke-dashoffset="0" transform="rotate(-90 65 65)"/>
                            <circle class="donut-swing" cx="65" cy="65" r="50" stroke="#ec4899"
                                stroke-dasharray="{{ $nwWantDash }} {{ $nwCirc }}"
                                stroke-dashoffset="{{ -$nwNeedDash }}" transform="rotate(-90 65 65)"/>
                            @endif
                        </svg>
                        <div class="donut-center">
                            <span class="text-2xl font-black text-gray-900 count-anim">
                                {{ $needCount + $wantCount > 0 ? round($needPct) : 0 }}%
                            </span>
                            <span class="text-[10px] text-gray-400">need</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="bg-emerald-50 rounded-2xl p-2.5 text-center">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-0.5">🏠 Need</p>
                        <p class="text-sm font-black text-emerald-700">Rp {{ number_format($needAmount, 0) }}</p>
                        <p class="text-[10px] text-emerald-400">{{ $needCount }} item{{ $needCount != 1 ? 's' : '' }}</p>
                    </div>
                    <div class="bg-pink-50 rounded-2xl p-2.5 text-center">
                        <p class="text-[10px] font-bold text-pink-500 uppercase tracking-wider mb-0.5">🎉 Want</p>
                        <p class="text-sm font-black text-pink-700">Rp {{ number_format($wantAmount, 0) }}</p>
                        <p class="text-[10px] text-pink-400">{{ $wantCount }} item{{ $wantCount != 1 ? 's' : '' }}</p>
                    </div>
                </div>

                <p class="text-[10px] italic text-gray-400 mt-1 leading-tight">{{ $needVsWantMessage }}</p>
            </div>

            <!-- Donut Chart -->
            <div class="card text-center">
                <h3 class="text-sm font-bold text-gray-700 mb-1">
                    <i class="ph ph-chart-donut text-blue-500 mr-1"></i>Money Flow
                </h3>
                <p class="text-xs text-gray-400 mb-4">Income vs Expenses</p>

                @php
                    $total = $totalIn + $totalOut;
                    $inPct  = $total > 0 ? ($totalIn / $total) : 0.5;
                    $circumference = 2 * 3.14159 * 70; // r=70
                    $dashIn  = $circumference * $inPct;
                    $dashOut = $circumference * (1 - $inPct);
                @endphp

                <div class="flex justify-center mb-3">
                    <div class="donut-wrap">
                        <svg width="180" height="180" viewBox="0 0 180 180">
                            <circle class="donut-track" cx="90" cy="90" r="70"/>
                            <circle class="donut-swing"
                                cx="90" cy="90" r="70"
                                stroke="#f87171"
                                stroke-dasharray="{{ $dashOut }} {{ $circumference }}"
                                stroke-dashoffset="0"
                                transform="rotate(-90 90 90)"
                                id="donutOut"/>
                            <circle class="donut-swing"
                                cx="90" cy="90" r="70"
                                stroke="#3b82f6"
                                stroke-dasharray="{{ $dashIn }} {{ $circumference }}"
                                stroke-dashoffset="{{ $circumference * 0.25 }}"
                                transform="rotate(-90 90 90)"
                                id="donutIn"/>
                        </svg>
                        <div class="donut-center">
                            <span class="text-2xl font-black text-gray-900 count-anim">
                                Rp{{ $total > 0 ? number_format($total/1000000, 1) . 'M' : '0' }}
                            </span>
                            <span class="text-xs text-gray-400">total</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center gap-6 text-xs font-semibold">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                        <span class="text-gray-500">In</span>
                        <span class="text-blue-600">{{ $total > 0 ? round($inPct*100) : 0 }}%</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>
                        <span class="text-gray-500">Out</span>
                        <span class="text-red-500">{{ $total > 0 ? round((1-$inPct)*100) : 0 }}%</span>
                    </span>
                </div>
            </div>

            <!-- Stats Column -->
            <div class="card space-y-3">
                <!-- Net Balance (big) -->
                <div class="text-center pb-3 border-b border-gray-50">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Net Balance</p>
                    <p class="text-3xl font-black {{ $netBalance >= 0 ? 'text-green-600' : 'text-red-500' }} count-anim">
                        @if($netBalance >= 0)
                            <span class="inline-block float">+</span>
                        @endif
                        Rp {{ number_format($netBalance, 0) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $netBalance >= 0 ? '🎉 You\'re in the green!' : '💸 Watch your spending' }}
                    </p>
                </div>

                <!-- In / Out mini -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-blue-50 rounded-2xl p-3 text-center">
                        <p class="text-xs font-bold text-blue-400 mb-1 uppercase tracking-wider">In</p>
                        <p class="text-lg font-black text-blue-600">Rp {{ number_format($totalIn, 0) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-2xl p-3 text-center">
                        <p class="text-xs font-bold text-red-400 mb-1 uppercase tracking-wider">Out</p>
                        <p class="text-lg font-black text-red-500">Rp {{ number_format($totalOut, 0) }}</p>
                    </div>
                </div>

                <!-- Secondary stats -->
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div>
                        <p class="text-xs font-bold text-gray-400 mb-0.5">{{ $transactionCount }}</p>
                        <p class="text-xs text-gray-400">Txns</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 mb-0.5">{{ $activeDays }}</p>
                        <p class="text-xs text-gray-400">Days</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold {{ $dailyAvg >= 0 ? 'text-green-600' : 'text-red-500' }} mb-0.5">
                            {{ $dailyAvg >= 0 ? '+' : '' }}{{ number_format($dailyAvg, 0) }}
                        </p>
                        <p class="text-xs text-gray-400">Daily Avg</p>
                    </div>
                </div>
            </div>

            <!-- Account Balance Distribution -->
            <div class="card">
                <h3 class="text-sm font-bold text-gray-700 mb-4">
                    <i class="ph ph-wallet text-indigo-500 mr-1"></i>Balance
                </h3>

                @php
                    $ACC_COLORS  = ['Cash' => '#f59e0b', 'Bank' => '#3b82f6', 'E-Wallet' => '#8b5cf6', 'Savings' => '#22c55e'];
                    $ACC_BG      = ['Cash' => '#fef3c7', 'Bank' => '#dbeafe', 'E-Wallet' => '#ede9fe', 'Savings' => '#dcfce7'];
                    $ACC_ICONS   = ['Cash' => '💵', 'Bank' => '🏦', 'E-Wallet' => '📱', 'Savings' => '🏠'];
                @endphp

                <div class="grid grid-cols-2 gap-3 mb-4">
                    @foreach(['Cash', 'Bank', 'E-Wallet', 'Savings'] as $accName)
                        @php $accBal = $accountBalances[$accName] ?? 0; @endphp
                        <div class="rounded-2xl p-4 text-center"
                            style="background:{{ $ACC_BG[$accName] ?? '#f3f4f6' }}">
                            <div class="text-2xl mb-1">{{ $ACC_ICONS[$accName] ?? '💰' }}</div>
                            <div class="text-xs font-semibold text-gray-500 mb-1">{{ $accName }}</div>
                            <div class="text-base font-black {{ $accBal < 0 ? 'text-red-500' : 'text-gray-900' }}">
                                Rp {{ number_format(abs($accBal), 0) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-3 text-center">
                    <span class="text-xs text-gray-400">Total</span>
                    <span class="text-xl font-black text-gray-900 block">
                        Rp {{ number_format($totalAccountBalance, 0) }}
                    </span>
                </div>
            </div>

            <!-- Health Score + Month -->
            <div class="card space-y-3">
                <!-- Financial Health -->
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                        <i class="ph ph-heartbeat mr-1"></i>Financial Health
                    </p>

                    @php
                        // Score: 0-100 based on whole-portfolio savings rate.
                        // Distinct from the Save vs Spend donut's $splitSavingsRate.
                        $portfolioSavingsRate = ($totalIn > 0) ? ($totalIn - $totalOut) / $totalIn : 0;
                        $score = max(0, min(100, round(($portfolioSavingsRate + 1) * 50)));
                        $scoreColor = $score >= 70 ? '#22c55e' : ($score >= 40 ? '#f59e0b' : '#ef4444');
                        $scoreLabel = $score >= 70 ? 'Healthy' : ($score >= 40 ? 'Okay' : 'Needs Work');
                        $scoreBg    = $score >= 70 ? 'bg-green-100 text-green-700' : ($score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600');
                        $scoreIcon  = $score >= 70 ? '💚' : ($score >= 40 ? '🟡' : '🔴');
                    @endphp

                    <div class="flex justify-center mb-2">
                        <div class="relative w-20 h-20">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle class="gauge-track" cx="40" cy="40" r="32"/>
                                <circle class="gauge-fill" cx="40" cy="40" r="32"
                                    stroke="{{ $scoreColor }}"
                                    stroke-dasharray="{{ 2 * 3.14159 * 32 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 32 * (1 - $score/100) }}"
                                    transform="rotate(-90 40 40)"
                                    id="healthGauge"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl">{{ $score }}</span>
                                <span class="text-[9px] font-bold text-gray-400">/100</span>
                            </div>
                        </div>
                    </div>
                    <span class="health-pill {{ $scoreBg }}">
                        {{ $scoreIcon }} {{ $scoreLabel }}
                    </span>
                </div>

                <div class="border-t border-gray-50 pt-3">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">This Month</span>
                        <span class="text-xs font-bold {{ $monthNet >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $monthNet >= 0 ? '+' : '' }}Rp {{ number_format($monthNet, 0) }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1 bg-blue-50 rounded-xl p-2 text-center">
                            <p class="text-xs text-blue-500 font-bold">+Rp {{ number_format($monthIn, 0) }}</p>
                            <p class="text-[10px] text-blue-300">In</p>
                        </div>
                        <div class="flex-1 bg-red-50 rounded-xl p-2 text-center">
                            <p class="text-xs text-red-500 font-bold">-Rp {{ number_format($monthOut, 0) }}</p>
                            <p class="text-[10px] text-red-300">Out</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-50 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">All-Time Net</span>
                        <span class="text-sm font-black {{ $allTimeNet >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $allTimeNet >= 0 ? '+' : '' }}Rp {{ number_format($allTimeNet, 0) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Bar Chart -->
        <div class="card">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-gray-700">
                    <i class="ph ph-chart-bar text-indigo-500 mr-1"></i>Monthly Breakdown
                </h3>
                <span class="text-xs text-gray-400">Last 6 months (all time)</span>
            </div>

            @php
                $maxMonthly = max(collect($monthlyTrend)->max('in') ?: 1, collect($monthlyTrend)->max('out') ?: 1);
            @endphp

            <div class="bar-wrap mt-4">
                @foreach($monthlyTrend as $m)
                    @php
                        $hIn  = $maxMonthly > 0 ? round(($m['in']  / $maxMonthly) * 88) : 4;
                        $hOut = $maxMonthly > 0 ? round(($m['out'] / $maxMonthly) * 88) : 4;
                        $net  = $m['in'] - $m['out'];
                    @endphp
                    <div class="bar-group" title="{{ $m['label'] }}: In Rp{{ number_format($m['in'],0) }}, Out Rp{{ number_format($m['out'],0) }}">
                        <div class="bar-stack">
                            <div class="bar-segment in"
                                style="height: {{ $hIn }}px"
                                data-label="{{ $m['label'] }}"
                                data-in="{{ number_format($m['in'], 0) }}"
                                data-out="{{ number_format($m['out'], 0) }}"
                                data-net="{{ number_format($net, 0) }}"
                                data-type="in"></div>
                            <div class="bar-segment out"
                                style="height: {{ $hOut }}px"
                                data-label="{{ $m['label'] }}"
                                data-in="{{ number_format($m['in'], 0) }}"
                                data-out="{{ number_format($m['out'], 0) }}"
                                data-net="{{ number_format($net, 0) }}"
                                data-type="out"></div>
                        </div>
                        <span class="bar-label">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Bar chart legend + tooltip -->
            <div class="flex items-center justify-between mt-3">
                <div class="flex items-center gap-4 text-xs text-gray-400 font-semibold">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-sm bg-blue-500 inline-block"></span> Income
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span> Expenses
                    </span>
                </div>
                <div id="barTooltip" class="text-xs text-gray-600 font-semibold min-w-[200px] text-right">
                    Hover a bar to see details
                </div>
            </div>
        </div>

        <!-- Savings Distribution Card -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-vault text-emerald-500"></i>
                        Savings Distribution
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Where your saved money goes
                        @if($totalAllocated > 0)
                            &nbsp;·&nbsp; <span class="font-bold text-emerald-600">Rp {{ number_format($totalAllocated, 0) }}</span> allocated
                        @endif
                    </p>
                </div>
                <button onclick="openTemplateModal()"
                    class="text-xs font-bold text-emerald-600 hover:text-emerald-800 px-3 py-1.5 rounded-xl hover:bg-emerald-50 transition border border-emerald-200">
                    <i class="ph ph-{{ empty($distributionTemplate) ? 'ph-plus' : 'ph-pencil-simple' }} mr-1"></i>
                    {{ empty($distributionTemplate) ? 'Set Up' : 'Edit Template' }}
                </button>
            </div>

            @if(empty($distributionTemplate))
                <!-- No template yet -->
                <div class="text-center py-8">
                    <div class="text-4xl mb-3">🏦</div>
                    <p class="text-sm font-semibold text-gray-500 mb-1">No distribution template yet</p>
                    <p class="text-xs text-gray-400 mb-4">Set up how your savings split automatically. E.g. 25% each into 4 categories.</p>
                    <button onclick="openTemplateModal()"
                        class="text-xs font-bold text-emerald-600 hover:text-emerald-800 px-4 py-2 rounded-xl hover:bg-emerald-50 transition border border-emerald-200">
                        Set Up Template
                    </button>
                </div>
            @else
                <!-- Template preview -->
                @php
                    $templateTotalPct = collect($distributionTemplate)->sum('pct');
                    $templateColors = ['#22c55e', '#3b82f6', '#f59e0b', '#a78bfa', '#f472b6', '#fb923c', '#14b8a6', '#e879f9'];
                    $templateIcons = ['🛡️', '📈', '🎯', '💼', '🏠', '✈️', '🎓', '💰'];
                @endphp
                <div class="flex gap-2 mb-3">
                    @foreach($distributionTemplate as $i => $item)
                        <div class="flex-1 rounded-2xl p-3 text-center"
                            style="background: {{ $templateColors[$i] ?? '#6b7280' }}15; border: 1px solid {{ $templateColors[$i] ?? '#6b7280' }}30;">
                            <div class="text-lg mb-1">{{ $item['icon'] ?? ($templateIcons[$i] ?? '📦') }}</div>
                            <div class="text-lg font-black" style="color: {{ $templateColors[$i] ?? '#6b7280' }}">{{ $item['pct'] }}%</div>
                            <div class="text-[10px] text-gray-500 font-semibold truncate">{{ $item['name'] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Total: <span class="font-bold {{ $templateTotalPct == 100 ? 'text-emerald-600' : 'text-red-500' }}">{{ $templateTotalPct }}%</span></span>
                    <span class="text-xs text-emerald-500 font-bold">Auto-applied on split</span>
                </div>
                @if($templateTotalPct != 100)
                    <div class="mt-2 p-2 rounded-xl bg-amber-50 border border-amber-200 text-center">
                        <p class="text-xs text-amber-700 font-bold">⚠️ Template must total 100% to work</p>
                    </div>
                @endif

                <!-- Category bars -->
                @if(count($distributionChart) > 0 && $totalAllocated > 0)
                    @php
                        $maxDist = collect($distributionChart)->max('amount') ?: 1;
                        $distColors = [
                            'Emergency Fund' => '#22c55e',
                            'Investment'    => '#3b82f6',
                            'Goals'         => '#f59e0b',
                            'Buffer'        => '#a78bfa',
                        ];
                        $distIcons = [
                            'Emergency Fund' => '🛡️',
                            'Investment'    => '📈',
                            'Goals'         => '🎯',
                            'Buffer'        => '💼',
                        ];
                    @endphp
                    <div class="space-y-3 mt-4">
                        @foreach($distributionChart as $item)
                            @if($item['amount'] > 0)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">{{ $distIcons[$item['category']] ?? '📦' }}</span>
                                        <span class="text-sm font-semibold text-gray-700">{{ $item['category'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-400 font-medium">{{ $item['pct'] }}%</span>
                                        <span class="text-sm font-black text-gray-800">Rp {{ number_format($item['amount'], 0) }}</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700"
                                        style="width: {{ $maxDist > 0 ? round($item['amount'] / $maxDist * 100) : 0 }}%; background-color: {{ $distColors[$item['category']] ?? '#6b7280' }};"></div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Allocated</span>
                        <span class="text-lg font-black text-emerald-600">Rp {{ number_format($totalAllocated, 0) }}</span>
                    </div>
                @endif
            @endif
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-list-bullets text-gray-400"></i>
                        Transactions
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $transactionCount }} transaction{{ $transactionCount != 1 ? 's' : '' }}
                        @if($filter !== 'all' || $type !== 'all' || $search)
                            <span class="text-blue-500 font-bold">— filtered</span>
                        @endif
                    </p>
                </div>
                <!-- View toggle -->
                <div class="flex items-center gap-1 text-xs">
                    <button onclick="setView('grouped')" id="btnGrouped"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 font-bold transition">
                        <i class="ph ph-calendar-blank mr-0.5"></i>Grouped
                    </button>
                    <button onclick="setView('flat')" id="btnFlat"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 font-bold transition">
                        <i class="ph ph-list mr-0.5"></i>Flat
                    </button>
                </div>
            </div>

            <!-- GROUPED VIEW -->
            <div id="viewGrouped">
                @if($groupedTx->isEmpty())
                    <div class="text-center py-12">
                        <div class="text-5xl mb-3">📭</div>
                        <p class="text-gray-400 font-semibold">No transactions found</p>
                        <a href="/dashboard?filter=all&type=all" class="inline-block mt-3 text-xs text-blue-500 hover:text-blue-700 font-bold">
                            Clear filters
                        </a>
                    </div>
                @else
                    @foreach($groupedTx as $date => $dayTx)
                        @php
                            $dayIn  = $dayTx->where('type', 'in')->sum('amount');
                            $dayOut = $dayTx->where('type', 'out')->sum('amount');
                            $dayNet = $dayIn - $dayOut;
                            $dayLabel = \Carbon\Carbon::parse($date)->format('D, j M Y');
                            $isToday = $date === \Carbon\Carbon::today()->format('Y-m-d');
                        @endphp
                        <div class="mb-1.5">
                            <div class="day-row open" onclick="toggleDay('{{ $date }}', this)">
                                <div class="flex items-center gap-3">
                                    <i id="arr-{{ $date }}" class="ph ph-caret-right text-gray-400 text-sm transition-transform duration-200" style="transform: rotate(90deg)"></i>
                                    <span class="text-sm font-bold text-gray-700">{{ $dayLabel }}</span>
                                    @if($isToday)
                                        <span class="text-[10px] font-bold bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">TODAY</span>
                                    @endif
                                    <span class="text-xs text-gray-300">({{ $dayTx->count() }})</span>
                                </div>
                                <div class="flex items-center gap-4 text-xs font-bold">
                                    <span class="text-green-600">+Rp {{ number_format($dayIn, 0) }}</span>
                                    <span class="text-red-500">-Rp {{ number_format($dayOut, 0) }}</span>
                                    <span class="{{ $dayNet >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $dayNet >= 0 ? '+' : '' }}Rp {{ number_format($dayNet, 0) }}
                                    </span>
                                </div>
                            </div>

                            <div id="day-{{ $date }}" class="day-items open pl-4 ml-2 border-l-2 border-gray-100 mt-1 space-y-1">
                                @foreach($dayTx as $t)
                                    <div class="tx-row group">
                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                            <span class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0
                                                {{ $t->type === 'in' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                                                <i class="ph {{ $t->type === 'in' ? 'ph-arrow-circle-down' : 'ph-arrow-circle-up' }} text-sm"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <span class="text-sm text-gray-800 font-medium truncate block">{{ $t->description }}</span>
                                                @if($t->type === 'in' && $t->is_split)
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full">
                                                        💰 {{ $t->save_pct }}% = Rp {{ number_format($t->saved_amount, 0) }}
                                                    </span>
                                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">
                                                        🛒 {{ $t->spend_pct }}% = Rp {{ number_format($t->spent_amount, 0) }}
                                                    </span>
                                                </div>
                                                <div class="split-bar" style="--save-pct: {{ $t->save_pct }}%"></div>
                                                @endif
                                                @if($t->type === 'out' && $t->need_or_want)
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                                        {{ $t->need_or_want === 'need' ? 'text-emerald-600 bg-emerald-50' : 'text-pink-600 bg-pink-50' }}">
                                                        {{ $t->need_or_want === 'need' ? '🏠 Need' : '🎉 Want' }}
                                                        @if($t->expense_category)
                                                        · {{ $t->expense_category }}
                                                        @endif
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-sm font-black {{ $t->type === 'in' ? 'text-green-600' : 'text-red-500' }}">
                                                {{ $t->type === 'in' ? '+' : '-' }}Rp {{ number_format($t->amount, 0) }}
                                            </span>
                                            <a href="/transaction/{{ $t->id }}/edit"
                                                class="text-gray-300 hover:text-blue-500 transition p-1 rounded-lg hover:bg-blue-50">
                                                <i class="ph ph-pencil-simple text-sm"></i>
                                            </a>
                                            <form action="/transaction/{{ $t->id }}" method="POST" class="inline"
                                                onsubmit="return confirm('Delete this transaction?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-gray-300 hover:text-red-500 transition p-1 rounded-lg hover:bg-red-50">
                                                    <i class="ph ph-trash text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- FLAT VIEW -->
            <div id="viewFlat" class="hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-wider pb-3 pr-3 cursor-pointer" onclick="sortFlat('date', this)" >
                                    Date <i class="ph ph-arrows-down-up text-xs ml-1"></i>
                                </th>
                                <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-wider pb-3 pr-3 cursor-pointer" onclick="sortFlat('desc', this)">
                                    Description <i class="ph ph-arrows-down-up text-xs ml-1"></i>
                                </th>
                                <th class="text-center text-xs font-bold text-gray-400 uppercase tracking-wider pb-3 px-2">Type</th>
                                <th class="text-right text-xs font-bold text-gray-400 uppercase tracking-wider pb-3 pl-3 cursor-pointer" onclick="sortFlat('amount', this)">
                                    Amount <i class="ph ph-arrows-down-up text-xs ml-1"></i>
                                </th>
                                <th class="pb-3 w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="flatBody">
                            @foreach($groupedTx->flatten() as $t)
                                <tr class="border-b border-gray-50 hover:bg-blue-50/50 transition rounded-xl">
                                    <td class="py-2.5 pr-3 text-xs text-gray-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($t->transaction_date)->format('j M Y') }}
                                    </td>
                                    <td class="py-2.5 pr-3 font-semibold text-gray-800 truncate max-w-[180px]">
                                        {{ $t->description }}
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold
                                            {{ $t->type === 'in' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                                            <i class="ph {{ $t->type === 'in' ? 'ph-arrow-circle-down' : 'ph-arrow-circle-up' }} text-xs"></i>
                                            {{ $t->type === 'in' ? 'In' : 'Out' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pl-3 text-right font-black whitespace-nowrap {{ $t->type === 'in' ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $t->type === 'in' ? '+' : '-' }}Rp {{ number_format($t->amount, 0) }}
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <a href="/transaction/{{ $t->id }}/edit" class="text-gray-300 hover:text-blue-500 transition">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════
         INCOME SPLIT MODAL
    ═══════════════════════════════════════════════════════ -->
    <div id="splitModal" class="modal-overlay" onclick="if(event.target===this)closeSplitModal()">
        <div class="modal-box">
            <!-- Header with coin -->
            <div class="text-center mb-5">
                <div id="splitCoin" class="w-16 h-16 bg-yellow-100 border-4 border-yellow-400 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl">
                    💰
                </div>
                <h2 class="text-xl font-black text-gray-900">You Got Income!</h2>
                <p id="splitAmountDisplay" class="text-3xl font-black text-green-600 mt-1">Rp 0</p>
                <p id="splitDescDisplay" class="text-sm text-gray-400">from: Salary</p>
            </div>

            <p class="text-center text-sm font-semibold text-gray-500 mb-4">
                How do you want to split this?
            </p>

            <!-- Save / Spend cards -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div id="cardSave" class="choice-card save active">
                    <div class="text-2xl mb-1">💰</div>
                    <p class="text-xs font-bold text-green-600 uppercase tracking-wider">Save</p>
                    <p id="pctSaveDisplay" class="text-2xl font-black text-green-700">50%</p>
                    <p id="amtSaveDisplay" class="text-sm font-bold text-green-600">Rp 0</p>
                </div>
                <div id="cardSpend" class="choice-card spend">
                    <div class="text-2xl mb-1">🛒</div>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Spend</p>
                    <p id="pctSpendDisplay" class="text-2xl font-black text-blue-700">50%</p>
                    <p id="amtSpendDisplay" class="text-sm font-bold text-blue-600">Rp 0</p>
                </div>
            </div>

            <!-- Mini donut preview -->
            <div class="flex justify-center mb-4">
                <div class="donut-wrap" style="width:80px;height:80px">
                    <svg width="80" height="80" viewBox="0 0 80 80">
                        <circle class="donut-track" cx="40" cy="40" r="30" stroke-width="8"/>
                        <circle id="modalDonutSave" class="donut-swing" cx="40" cy="40" r="30"
                            stroke="#22c55e" stroke-width="8" stroke-dasharray="94.2 188.4"
                            stroke-dashoffset="0" transform="rotate(-90 40 40)"/>
                        <circle id="modalDonutSpend" class="donut-swing" cx="40" cy="40" r="30"
                            stroke="#3b82f6" stroke-width="8" stroke-dasharray="94.2 188.4"
                            stroke-dashoffset="0" transform="rotate(-90 40 40)"/>
                    </svg>
                </div>
            </div>

            <!-- Slider -->
            <div class="px-2 mb-4">
                <div class="flex justify-between text-xs font-bold text-gray-400 mb-2">
                    <span>💰 SAVE</span>
                    <span>🛒 SPEND</span>
                </div>
                <div class="relative h-4 bg-gray-100 rounded-full mb-2">
                    <div id="sliderFill" class="absolute left-0 top-0 h-full rounded-full transition-all duration-75"
                        style="width:50%; background: linear-gradient(to right, #22c55e, #3b82f6);"></div>
                    <div id="sliderThumb" class="absolute top-1/2 -translate-y-1/2 w-8 h-8 bg-white border-3 border-yellow-400 rounded-full shadow-md transition-all duration-75 flex items-center justify-center text-xs font-black text-yellow-600"
                        style="left: calc(50% - 16px);">₩</div>
                    <input type="range" id="splitSlider" min="0" max="100" value="50"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        oninput="updateSplit(this.value)">
                </div>
            </div>

            <!-- Presets -->
            <div class="flex justify-center gap-2 mb-4">
                <button type="button" class="preset-pill balanced" onclick="setPreset('balanced')">🎯 Balanced</button>
                <button type="button" class="preset-pill saver" onclick="setPreset('saver')">🏦 Saver</button>
                <button type="button" class="preset-pill spender" onclick="setPreset('spender')">🛍 Spender</button>
            </div>

            <!-- Motivational message -->
            <p id="splitMessage" class="text-center text-sm italic text-gray-500 mb-3">
                A great split starts your month right!
            </p>

            <!-- ── SAVINGS DISTRIBUTION STEP ─────────────────────────────── -->
            <div id="distSection" class="hidden">
                <div class="border-t border-gray-100 pt-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1">
                            <i class="ph ph-vault"></i> Distribute your savings
                        </p>
                        <button type="button" onclick="closeDistSection()"
                            class="text-xs text-gray-400 hover:text-gray-600 px-2 py-0.5 rounded hover:bg-gray-100 transition">
                            <i class="ph ph-x"></i> Skip
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-3">Allocate your saved Rp <span id="distSaveAmtLabel" class="font-bold text-emerald-600">0</span> into categories</p>

                    <!-- Distribution categories -->
                    <div id="distCategories" class="space-y-2">
                        <!-- Filled by JS -->
                    </div>

                    <!-- Distribution bar preview -->
                    <div class="mt-3">
                        <div class="flex gap-1 h-3 rounded-full overflow-hidden" id="distBarPreview">
                            <!-- Filled by JS -->
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-[9px] text-gray-400" id="distTotalPct">0%</span>
                            <span class="text-[9px] text-gray-400">100%</span>
                        </div>
                        <p id="distError" class="text-[10px] text-red-500 mt-1 hidden">Total must equal 100%</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="button" onclick="closeSplitModal()"
                    class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">
                    Skip
                </button>
                <button type="button" onclick="submitWithSplit()"
                    class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold text-sm shadow-lg shadow-green-200 hover:shadow-green-300 hover:from-green-600 hover:to-emerald-600 active:scale-95 transition-all flex items-center justify-center gap-2">
                    💰 Save &amp; Log Income
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         EXPENSE CATEGORIZER MODAL
    ═══════════════════════════════════════════════════════ -->
    <div id="expenseModal" class="modal-overlay" onclick="if(event.target===this)closeExpenseModal()">
        <div class="modal-box">
            <!-- Header -->
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-red-100 border-4 border-red-300 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl">
                    🏷️
                </div>
                <h2 class="text-xl font-black text-gray-900">Where does this go?</h2>
                <p id="expenseAmountDisplay" class="text-3xl font-black text-red-500 mt-1">Rp 0</p>
                <p id="expenseDescDisplay" class="text-sm text-gray-400">for: Coffee</p>
            </div>

            <!-- Need vs Want toggle -->
            <div class="nw-toggle mb-4">
                <div id="nwBtnNeed" class="nw-btn need" onclick="selectNeedWant('need')">
                    <div class="text-3xl mb-1">🏠</div>
                    <p class="text-base font-black text-emerald-700">Need</p>
                    <p class="text-xs text-emerald-500 mt-0.5">Bills, food, transport...</p>
                </div>
                <div id="nwBtnWant" class="nw-btn want" onclick="selectNeedWant('want')">
                    <div class="text-3xl mb-1">🎉</div>
                    <p class="text-base font-black text-pink-700">Want</p>
                    <p class="text-xs text-pink-500 mt-0.5">Fun, shopping, treats...</p>
                </div>
            </div>

            <!-- Account selector -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">
                    <i class="ph ph-wallet mr-1"></i>Pay from account
                </label>
                <select id="expenseAccountSelect"
                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-pink-400 bg-gray-50 text-center">
                    <option value="">— None —</option>
                    @foreach(['Cash', 'Bank', 'E-Wallet', 'Savings'] as $acc)
                        <option value="{{ $acc }}">{{ $acc }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category picker (reveals after Need/Want selection) -->
            <div id="catReveal" class="cat-reveal">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Pick a category</p>
                <div id="catChips" class="flex flex-wrap gap-2 justify-center">
                    <!-- Filled by JS based on selection -->
                </div>
            </div>

            <!-- Distribution deduction selector (optional) -->
            <div id="distDeduceSection" class="mt-3 border-t border-gray-100 pt-3">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-2 text-center flex items-center justify-center gap-1">
                    <i class="ph ph-vault"></i> Deduct from savings
                </p>
                <select id="distCatSelect"
                    class="w-full border border-emerald-200 rounded-2xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-emerald-50 text-center">
                    <option value="">— Split evenly (all categories) —</option>
                    <!-- Filled by JS based on template -->
                </select>
                <p class="text-[10px] text-gray-400 mt-1 text-center">Optional: pick where this expense comes from</p>
            </div>

            <!-- Motivational -->
            <p id="expenseMessage" class="text-center text-sm italic text-gray-400 mb-5">
                Tag it to see insights!
            </p>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="button" onclick="closeExpenseModal()"
                    class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">
                    Skip
                </button>
                <button type="button" id="expenseSubmitBtn" onclick="submitWithCategory()"
                    class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-pink-500 to-rose-500 text-white font-bold text-sm shadow-lg shadow-pink-200 hover:shadow-pink-300 hover:from-pink-600 hover:to-rose-600 active:scale-95 transition-all flex items-center justify-center gap-2">
                    🏷️ Log Expense
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         DISTRIBUTION TEMPLATE SETUP MODAL
    ═══════════════════════════════════════════════════════ -->
    <div id="templateModal" class="modal-overlay" onclick="if(event.target===this)closeTemplateModal()">
        <div class="modal-box" style="max-width:520px">
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-emerald-100 border-4 border-emerald-400 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl">
                    ⚙️
                </div>
                <h2 class="text-xl font-black text-gray-900">Distribution Template</h2>
                <p class="text-sm text-gray-400 mt-1">Set how your savings split automatically when you receive money</p>
            </div>

            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Save % (of each Money In)</p>
            <div class="flex items-center gap-3 mb-4">
                <input type="range" id="tmplSaveSlider" min="0" max="100" value="{{ $distributionSavePct }}"
                    class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                    oninput="document.getElementById('tmplSavePctLabel').textContent=this.value+'%'">
                <span class="text-lg font-black text-emerald-600 w-14 text-center" id="tmplSavePctLabel">{{ $distributionSavePct }}%</span>
            </div>

            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Distribution categories</p>
            <div id="tmplCategories" class="space-y-2 mb-3 max-h-60 overflow-y-auto">
                <!-- Filled by JS -->
            </div>

            <button type="button" onclick="addTemplateRow()"
                class="w-full text-xs font-bold text-emerald-600 hover:text-emerald-800 py-2 rounded-xl hover:bg-emerald-50 transition border border-dashed border-emerald-200 mb-3">
                <i class="ph ph-plus mr-1"></i>Add Category
            </button>

            <!-- Preview bar -->
            <div class="mb-3">
                <div class="flex gap-1 h-4 rounded-full overflow-hidden" id="tmplPreviewBar">
                    <!-- Filled by JS -->
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-[10px] text-gray-400" id="tmplTotalPctLabel">0%</span>
                    <span class="text-[10px] text-gray-400" id="tmplTotalError" style="display:none" class="text-red-400 font-bold">Must equal 100%</span>
                </div>
            </div>

            <!-- Save template -->
            <div class="flex gap-3">
                <button type="button" onclick="deleteTemplate()"
                    class="flex-1 py-3 rounded-2xl border-2 border-red-200 text-sm font-bold text-red-400 hover:bg-red-50 transition">
                    Delete
                </button>
                <button type="button" onclick="saveTemplate()"
                    class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-sm shadow-lg shadow-emerald-200 hover:shadow-emerald-300 hover:from-emerald-600 hover:to-teal-600 active:scale-95 transition-all flex items-center justify-center gap-2">
                    💾 Save Template
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         SAVINGS DISTRIBUTION SETUP MODAL
    ═══════════════════════════════════════════════════════ -->
    <div id="distributionModal" class="modal-overlay" onclick="if(event.target===this)closeDistributionModal()">
        <div class="modal-box">
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-emerald-100 border-4 border-emerald-400 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl">
                    🏦
                </div>
                <h2 class="text-xl font-black text-gray-900">Split & Distribute</h2>
                <p class="text-sm text-gray-400 mt-1">Retroactively split Money In and allocate savings</p>
            </div>

            @if($unsplitInTx->isEmpty())
                <p class="text-sm text-gray-400 text-center py-4">No unsplit Money In transactions to distribute.</p>
            @else
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Select Money In to split ({{ number_format($unsplitIn, 0) }} total)</p>
                <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-xl mb-4 divide-y divide-gray-100">
                    @foreach($unsplitInTx as $tx)
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="dist-tx-check" value="{{ $tx->id }}" data-amount="{{ $tx->amount }}" checked>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $tx->description }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('M d') }}</p>
                            </div>
                            <span class="text-sm font-bold text-green-600">Rp {{ number_format($tx->amount, 0) }}</span>
                        </label>
                    @endforeach
                </div>

                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Save percentage</p>
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-500">Save</span>
                        <span class="text-xs font-bold text-emerald-600" id="distSavePct">50%</span>
                        <span class="text-xs text-gray-500">Spend</span>
                    </div>
                    <input type="range" id="distSaveSlider" min="0" max="100" value="50"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                        oninput="document.getElementById('distSavePct').textContent=this.value+'%'; document.getElementById('distSpendPct').textContent=(100-this.value)+'%'">
                    <div class="flex justify-end mt-1">
                        <span class="text-xs text-red-400 font-bold" id="distSpendPct">50%</span>
                    </div>
                </div>

                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Category distribution (of saved amount)</p>
                <div class="space-y-2 mb-4">
                    @foreach(['Emergency Fund' => '#22c55e', 'Investment' => '#3b82f6', 'Goals' => '#f59e0b', 'Buffer' => '#a78bfa'] as $cat => $color)
                        <div class="flex items-center gap-2">
                            <input type="number" id="distPct_{{$cat}}" value="25" min="0" max="100" class="w-14 border border-gray-200 rounded-lg px-2 py-1 text-xs text-center" oninput="updateDistTotal()">
                            <span class="text-xs font-semibold text-gray-600 w-28">{{ $cat }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div id="distBar_{{$cat}}" class="h-2 rounded-full transition-all" style="width:25%;background:{{ $color }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p id="distTotalError" class="text-xs text-red-500 mb-3 hidden text-center">Total must equal 100%</p>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeDistributionModal()"
                        class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="button" onclick="submitRetroDistribution()"
                        class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-sm shadow-lg shadow-emerald-200 hover:shadow-emerald-300 hover:from-emerald-600 hover:to-teal-600 active:scale-95 transition-all flex items-center justify-center gap-2">
                        🏦 Apply Split & Distribute
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Account Balance Modal -->
    <!-- Hidden forms -->
    <form id="splitForm" action="{{ route('store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="description" id="splitFormDesc">
        <input type="hidden" name="amount" id="splitFormAmt">
        <input type="hidden" name="type" value="in">
        <input type="hidden" name="transaction_date" id="splitFormDate">
        <input type="hidden" name="save_pct" id="splitFormSavePct">
        <input type="hidden" name="spend_pct" id="splitFormSpendPct">
        <input type="hidden" name="saved_amount" id="splitFormSavedAmt">
        <input type="hidden" name="spent_amount" id="splitFormSpentAmt">
        <input type="hidden" name="is_split" id="splitFormIsSplit" value="1">
        <input type="hidden" name="split_preset" id="splitFormPreset">
        <input type="hidden" name="savings_distribution" id="splitFormDist">
        <input type="hidden" name="account_type" id="splitFormAccount">
    </form>

    <form id="distributionForm" action="{{ route('saveDistribution') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="transaction_ids" id="distFormIds">
        <input type="hidden" name="save_pct" id="distFormSavePct">
        <input type="hidden" name="spend_pct" id="distFormSpendPct">
        <input type="hidden" name="distribution" id="distributionFormData">
    </form>

    <form id="distributionTemplateForm" action="{{ route('saveDistributionTemplate') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="template" id="distTemplateData">
        <input type="hidden" name="save_pct" id="distTemplateSavePct">
    </form>

    <form id="expenseForm" action="{{ route('store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="description" id="expenseFormDesc">
        <input type="hidden" name="amount" id="expenseFormAmt">
        <input type="hidden" name="type" value="out">
        <input type="hidden" name="transaction_date" id="expenseFormDate">
        <input type="hidden" name="need_or_want" id="expenseFormNow">
        <input type="hidden" name="expense_category" id="expenseFormCat">
        <input type="hidden" name="account_type" id="expenseFormAccount">
        <input type="hidden" name="distribution_category" id="expenseFormDistCat">
    </form>

    <script>
        // ── Constants ─────────────────────────────────────────────────
        const CIRC = 2 * Math.PI * 30; // donut circumference (r=30)
        const PRESETS = {
            balanced: 50,
            saver: 80,
            spender: 20,
        };

        // ── Bar chart tooltip ──────────────────────────────────────────
        document.querySelectorAll('.bar-segment').forEach(bar => {
            bar.addEventListener('mouseenter', () => {
                const tip = document.getElementById('barTooltip');
                const d = bar.dataset;
                const sign = parseInt(d.net.replace(/,/g,'')) >= 0 ? '+' : '';
                tip.innerHTML = `<span class="text-gray-400">${d.label}:</span> ` +
                    `<span class="text-blue-500">In Rp${d.in}</span> · ` +
                    `<span class="text-red-400">Out Rp${d.out}</span> · ` +
                    `<span class="${parseInt(d.net.replace(/,/g,'')) >= 0 ? 'text-green-600' : 'text-red-500'}">Net ${sign}Rp${d.net}</span>`;
            });
            bar.addEventListener('mouseleave', () => {
                document.getElementById('barTooltip').innerHTML = 'Hover a bar to see details';
            });
        });

        // ── Day toggle ───────────────────────────────────────────────
        function toggleDay(date, row) {
            const el = document.getElementById('day-' + date);
            const arrow = document.getElementById('arr-' + date);
            const isOpen = el.classList.contains('open');

            el.classList.toggle('open');
            row.classList.toggle('open');

            if (isOpen) {
                el.style.maxHeight = '0';
                arrow.style.transform = '';
            } else {
                el.style.maxHeight = el.scrollHeight + 'px';
                arrow.style.transform = 'rotate(90deg)';
            }
        }

        // ── View toggle ───────────────────────────────────────────────
        function setView(view) {
            document.getElementById('viewGrouped').classList.toggle('hidden', view !== 'grouped');
            document.getElementById('viewFlat').classList.toggle('hidden', view !== 'flat');

            ['btnGrouped', 'btnFlat'].forEach(id => {
                const el = document.getElementById(id);
                const isActive = (id === 'btnGrouped' && view === 'grouped') || (id === 'btnFlat' && view === 'flat');
                el.classList.toggle('bg-blue-600', isActive);
                el.classList.toggle('text-white', isActive);
                el.classList.toggle('border-blue-600', isActive);
                el.classList.toggle('text-gray-500', !isActive);
            });
        }
        setView('grouped');

        // ── Flat table sort ───────────────────────────────────────────
        function sortFlat(col, th) {
            const tbody = document.getElementById('flatBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Toggle direction
            if (sortFlat.dir === col) {
                sortFlat.dir = sortFlat.dir === 'asc' ? 'desc' : 'asc';
            } else {
                sortFlat.dir = 'asc';
                sortFlat.col = col;
            }
            sortFlat.col = col;

            rows.sort((a, b) => {
                const cells = [null, ...a.querySelectorAll('td')];
                const acells = [null, ...b.querySelectorAll('td')];
                let va, vb;
                if (col === 'date') {
                    va = a.querySelector('td').textContent.trim();
                    vb = b.querySelector('td').textContent.trim();
                } else if (col === 'desc') {
                    va = a.querySelectorAll('td')[1].textContent.trim();
                    vb = b.querySelectorAll('td')[1].textContent.trim();
                } else if (col === 'amount') {
                    va = parseFloat(a.querySelectorAll('td')[3].textContent.replace(/[^0-9]/g, ''));
                    vb = parseFloat(b.querySelectorAll('td')[3].textContent.replace(/[^0-9]/g, ''));
                }
                if (col === 'amount') {
                    return sortFlat.dir === 'asc' ? va - vb : vb - va;
                }
                return sortFlat.dir === 'asc'
                    ? va.localeCompare(vb)
                    : vb.localeCompare(va);
            });

            rows.forEach(r => tbody.appendChild(r));
        }
        sortFlat.col = null;
        sortFlat.dir = 'asc';

        // ── Animate gauge on load ─────────────────────────────────────
        window.addEventListener('load', () => {
            const gauge = document.getElementById('healthGauge');
            if (gauge) {
                const total = 2 * Math.PI * 32;
                const finalOffset = total * 0.15; // CSS sets initial
                gauge.style.strokeDashoffset = total;
                setTimeout(() => {
                    gauge.style.strokeDashoffset = finalOffset;
                }, 100);
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // INCOME SPLIT MODAL
        // ═══════════════════════════════════════════════════════════════
        let pendingForm = null;
        let splitAmount = 0;
        let splitDesc = '';
        let splitDate = '';
        let splitPreset = 'balanced';

        let distState = {
            enabled: false,
            total: 0,
            amounts: {},
            pct: {},
            totalPct: 0,
            templateLoaded: false,
        };

        // Load template from PHP
        const DIST_TEMPLATE = @json($distributionTemplate);
        const DIST_COLORS = ['#22c55e', '#3b82f6', '#f59e0b', '#a78bfa', '#f472b6', '#fb923c', '#14b8a6', '#e879f9'];
        const DIST_ICONS  = ['🛡️', '📈', '🎯', '💼', '🏠', '✈️', '🎓', '💰'];

        function initDistStateFromTemplate() {
            if (DIST_TEMPLATE.length > 0) {
                DIST_TEMPLATE.forEach(cat => {
                    distState.amounts[cat.name] = 0;
                    distState.pct[cat.name] = cat.pct;
                });
                distState.totalPct = DIST_TEMPLATE.reduce((sum, c) => sum + c.pct, 0);
            }
        }
        initDistStateFromTemplate();

        function openSplitModal(form) {
            console.log('openSplitModal called', form);
            if (!form.description.value.trim()) {
                alert('Please enter a description first!');
                form.description.focus();
                return;
            }
            if (!form.amount.value || parseFloat(form.amount.value) <= 0) {
                alert('Please enter an amount!');
                form.amount.focus();
                return;
            }
            pendingForm = form;
            splitAmount = parseFloat(form.amount.value);
            splitDesc = form.description.value.trim();
            splitDate = form.transaction_date.value;

            console.log('Opening split modal with:', { splitAmount, splitDesc, splitDate });

            document.getElementById('splitAmountDisplay').textContent =
                'Rp ' + splitAmount.toLocaleString('id-ID');
            document.getElementById('splitDescDisplay').textContent =
                'from: ' + splitDesc;

            // Coin flip
            const coin = document.getElementById('splitCoin');
            coin.classList.remove('coin-flip');
            void coin.offsetWidth;
            coin.classList.add('coin-flip');

            updateSplit(50);
            document.getElementById('splitSlider').value = 50;
            splitPreset = 'balanced';
            document.getElementById('splitModal').classList.add('active');
            console.log('splitModal active class added');
        }

        function openSplitModalForAmount(amount, desc) {
            pendingForm = null;
            splitAmount = amount;
            splitDesc = desc;
            splitDate = new Date().toISOString().split('T')[0];
            document.getElementById('splitAmountDisplay').textContent =
                'Rp ' + amount.toLocaleString('id-ID');
            document.getElementById('splitDescDisplay').textContent =
                'from: ' + desc;
            const coin = document.getElementById('splitCoin');
            coin.classList.remove('coin-flip');
            void coin.offsetWidth;
            coin.classList.add('coin-flip');
            updateSplit(50);
            document.getElementById('splitModal').classList.add('active');
        }

        function closeSplitModal() {
            document.getElementById('splitModal').classList.remove('active');
            pendingForm = null;
        }

        function updateSplit(savePct) {
            const pct = parseInt(savePct);
            const spendPct = 100 - pct;
            const saveAmt = splitAmount * pct / 100;
            const spendAmt = splitAmount * spendPct / 100;

            document.getElementById('pctSaveDisplay').textContent = pct + '%';
            document.getElementById('pctSpendDisplay').textContent = spendPct + '%';
            document.getElementById('amtSaveDisplay').textContent =
                'Rp ' + Math.round(saveAmt).toLocaleString('id-ID');
            document.getElementById('amtSpendDisplay').textContent =
                'Rp ' + Math.round(spendAmt).toLocaleString('id-ID');

            // Card highlighting
            document.getElementById('cardSave').classList.toggle('active', pct > 50);
            document.getElementById('cardSpend').classList.toggle('active', spendPct > 50);

            // Slider visual
            document.getElementById('sliderFill').style.width = pct + '%';
            document.getElementById('sliderThumb').style.left = 'calc(' + pct + '% - 16px)';

            // Modal donut
            const saveDash = CIRC * (pct / 100);
            const spendDash = CIRC * (spendPct / 100);
            const donutSave = document.getElementById('modalDonutSave');
            const donutSpend = document.getElementById('modalDonutSpend');
            donutSave.setAttribute('stroke-dasharray', saveDash + ' ' + CIRC);
            donutSpend.setAttribute('stroke-dasharray', spendDash + ' ' + CIRC);
            donutSpend.setAttribute('stroke-dashoffset', -saveDash);

            // Motivational message
            const msgs = {
                80: "Maximum saver mode! You're a legend! 🚀",
                70: "Whoa, {{ session('username') }} is on fire! 🔥",
                60: 'Solid saving discipline! Keep it up! 💪',
                50: 'A great split starts your month right! 🎯',
                30: 'Treat yourself, but wisely! ✨',
                0:  'Every split counts — great job! 🎯',
            };
            let msg = msgs[0];
            for (const [threshold, text] of Object.entries(msgs)) {
                if (pct >= parseInt(threshold)) msg = text;
            }
            document.getElementById('splitMessage').textContent = msg;

            // Show/hide distribution section based on save pct
            if (pct > 0 && !distState.enabled) {
                openDistSection();
            } else if (pct === 0) {
                closeDistSection();
            }
        }

        function setPreset(name) {
            const val = PRESETS[name];
            document.getElementById('splitSlider').value = val;
            splitPreset = name;
            updateSplit(val);
        }

        // ═══════════════════════════════════════════════════════════════
        // INCOME SPLIT MODAL — DISTRIBUTION STEP
        // ═══════════════════════════════════════════════════════════════
        const DIST_CATEGORIES = [
            { name: 'Emergency Fund', color: '#22c55e', icon: '🛡️' },
            { name: 'Investment',     color: '#3b82f6', icon: '📈' },
            { name: 'Goals',          color: '#f59e0b', icon: '🎯' },
            { name: 'Buffer',        color: '#a78bfa', icon: '💼' },
        ];

        function openDistSection() {
            const savePct = parseInt(document.getElementById('splitSlider').value);
            const saveAmt = splitAmount * savePct / 100;
            distState.total = saveAmt;
            distState.enabled = true;

            // If template exists, use it; otherwise use defaults
            if (DIST_TEMPLATE.length > 0) {
                DIST_TEMPLATE.forEach(cat => {
                    distState.amounts[cat.name] = saveAmt * cat.pct / 100;
                    distState.pct[cat.name] = cat.pct;
                });
                distState.totalPct = DIST_TEMPLATE.reduce((sum, c) => sum + c.pct, 0);
            } else {
                // Fallback to 25/25/25/25
                const cats = ['Emergency Fund', 'Investment', 'Goals', 'Buffer'];
                cats.forEach((name, i) => {
                    distState.amounts[name] = saveAmt * 25 / 100;
                    distState.pct[name] = 25;
                });
                distState.totalPct = 100;
            }

            buildDistCategoryUI();
            document.getElementById('distSection').classList.remove('hidden');
            document.getElementById('distSaveAmtLabel').textContent = Math.round(saveAmt).toLocaleString('id-ID');
            updateDistBarPreview();
        }

        function closeDistSection() {
            distState.enabled = false;
            document.getElementById('distSection').classList.add('hidden');
        }

        function buildDistCategoryUI() {
            const container = document.getElementById('distCategories');
            container.innerHTML = '';

            const cats = DIST_TEMPLATE.length > 0 ? DIST_TEMPLATE : [
                { name: 'Emergency Fund', color: '#22c55e', icon: '🛡️' },
                { name: 'Investment',     color: '#3b82f6', icon: '📈' },
                { name: 'Goals',          color: '#f59e0b', icon: '🎯' },
                { name: 'Buffer',         color: '#a78bfa', icon: '💼' },
            ];

            cats.forEach((cat, idx) => {
                const pct = distState.pct[cat.name] ?? 0;
                const amt = distState.amounts[cat.name] ?? 0;
                const color = cat.color || (DIST_COLORS[idx] || '#6b7280');
                const icon = cat.icon || (DIST_ICONS[idx] || '📦');
                container.innerHTML += `
                    <div class="flex items-center gap-2">
                        <span class="text-sm w-6 text-center">${icon}</span>
                        <span class="text-xs font-semibold text-gray-600 w-24 truncate">${cat.name}</span>
                        <input type="range" min="0" max="100" value="${pct}"
                            class="flex-1 h-1.5 rounded-full cursor-pointer"
                            style="accent-color:${color}"
                            oninput="updateDistPct('${cat.name.replace(/'/g, "\\'")}', this.value, '${color}')">
                        <span class="text-xs font-bold text-gray-500 w-8 text-right">${pct}%</span>
                        <span class="text-xs font-bold w-24 text-right" style="color:${color}">Rp ${Math.round(amt).toLocaleString('id-ID')}</span>
                    </div>`;
            });
        }

        function updateDistPct(catName, pctVal, color) {
            const pct = parseInt(pctVal);
            distState.pct[catName] = pct;
            distState.amounts[catName] = distState.total * pct / 100;
            distState.totalPct = Object.values(distState.pct).reduce((a, b) => a + b, 0);
            buildDistCategoryUI();
            updateDistBarPreview();
        }

        function updateDistBarPreview() {
            const bar = document.getElementById('distBarPreview');
            const totalPct = document.getElementById('distTotalPct');
            const error = document.getElementById('distError');
            const total = Object.values(distState.pct).reduce((a, b) => a + b, 0);
            totalPct.textContent = total + '%';
            error.classList.toggle('hidden', total === 100);
            bar.innerHTML = '';

            const cats = DIST_TEMPLATE.length > 0 ? DIST_TEMPLATE : [
                { name: 'Emergency Fund', color: '#22c55e' },
                { name: 'Investment',     color: '#3b82f6' },
                { name: 'Goals',          color: '#f59e0b' },
                { name: 'Buffer',         color: '#a78bfa' },
            ];

            cats.forEach((cat, i) => {
                const pct = distState.pct[cat.name] || 0;
                const color = cat.color || (DIST_COLORS[i] || '#6b7280');
                if (pct > 0) {
                    bar.innerHTML += `<div style="width:${pct}%;background:${color}" class="h-full transition-all duration-200 rounded-full"></div>`;
                }
            });
        }

        function submitWithSplit() {
            const savePct = parseInt(document.getElementById('splitSlider').value);
            const spendPct = 100 - savePct;
            const saveAmt = splitAmount * savePct / 100;
            const spendAmt = splitAmount * spendPct / 100;

            document.getElementById('splitFormDesc').value = splitDesc;
            document.getElementById('splitFormAmt').value = splitAmount;
            document.getElementById('splitFormDate').value = splitDate;
            document.getElementById('splitFormSavePct').value = savePct;
            document.getElementById('splitFormSpendPct').value = spendPct;
            document.getElementById('splitFormSavedAmt').value = saveAmt;
            document.getElementById('splitFormSpentAmt').value = spendAmt;
            document.getElementById('splitFormPreset').value = splitPreset;
            document.getElementById('splitFormAccount').value = pendingForm && pendingForm.account_type ? pendingForm.account_type.value : '';

            // DEBUG
            console.log('submitWithSplit called', {
                splitDesc, splitAmount, splitDate, savePct, spendPct,
                distEnabled: distState.enabled, distTotalPct: distState.totalPct
            });

            // Distribution data — auto-apply template if it exists
            const cats = DIST_TEMPLATE.length > 0 ? DIST_TEMPLATE : [
                { name: 'Emergency Fund' },
                { name: 'Investment' },
                { name: 'Goals' },
                { name: 'Buffer' },
            ];

            if (distState.enabled && distState.totalPct === 100) {
                const dist = cats.map((cat, idx) => ({
                    category: cat.name,
                    pct: distState.pct[cat.name] || (DIST_TEMPLATE[idx] ? DIST_TEMPLATE[idx].pct : 0),
                    icon: DIST_TEMPLATE[idx] ? DIST_TEMPLATE[idx].icon : '📦',
                    amount: Math.round(distState.amounts[cat.name] || 0),
                })).filter(d => d.pct > 0);
                document.getElementById('splitFormDist').value = JSON.stringify(dist);
            } else if (DIST_TEMPLATE.length > 0 && saveAmt > 0) {
                // Auto-apply template percentages even if user didn't open the distribution step
                const dist = DIST_TEMPLATE.map(cat => ({
                    category: cat.name,
                    pct: cat.pct,
                    icon: cat.icon || '📦',
                    amount: Math.round(saveAmt * cat.pct / 100),
                }));
                document.getElementById('splitFormDist').value = JSON.stringify(dist);
            } else {
                document.getElementById('splitFormDist').value = '';
            }

            closeSplitModal();
            triggerConfetti();
            showToast(
                `Rp ${Math.round(saveAmt).toLocaleString('id-ID')} saved! Great job, {{ session('username') }}! 🎉`
            );

            // DEBUG: Check form before submit
            const f = document.getElementById('splitForm');
            console.log('Form action:', f.action);
            console.log('Form fields:', {
                desc: document.getElementById('splitFormDesc').value,
                amt: document.getElementById('splitFormAmt').value,
                type: f.querySelector('[name=type]').value,
                is_split: f.querySelector('[name=is_split]').value,
                save_pct: document.getElementById('splitFormSavePct').value,
            });
            f.submit();
        }

        // ═══════════════════════════════════════════════════════════════
        // DISTRIBUTION SETUP MODAL (retroactive split + distribute)
        // ═══════════════════════════════════════════════════════════════

        function openDistributionSetupModal() {
            document.getElementById('distributionModal').classList.add('active');
        }

        function closeDistributionModal() {
            document.getElementById('distributionModal').classList.remove('active');
        }

        // ═══════════════════════════════════════════════════════════════
        // DISTRIBUTION TEMPLATE MODAL
        // ═══════════════════════════════════════════════════════════════
        let tmplState = {
            rows: [],
        };

        function openTemplateModal() {
            // Init rows from template
            if (DIST_TEMPLATE.length > 0) {
                tmplState.rows = DIST_TEMPLATE.map(c => ({ name: c.name, pct: c.pct, icon: c.icon || '📦' }));
            } else {
                // Default: 2 categories 50/50
                tmplState.rows = [
                    { name: 'Emergency Fund', pct: 50, icon: '🛡️' },
                    { name: 'Investment', pct: 50, icon: '📈' },
                ];
            }
            renderTemplateRows();
            updateTemplatePreview();
            document.getElementById('templateModal').classList.add('active');
        }

        function closeTemplateModal() {
            document.getElementById('templateModal').classList.remove('active');
        }

        function addTemplateRow() {
            tmplState.rows.push({ name: '', pct: 0, icon: '📦' });
            renderTemplateRows();
        }

        function removeTemplateRow(idx) {
            tmplState.rows.splice(idx, 1);
            renderTemplateRows();
            updateTemplatePreview();
        }

        const ICON_OPTIONS = ['🛡️', '📈', '🎯', '💼', '🏠', '✈️', '🎓', '💰', '🍎', '🚗', '🏥', '🎁'];

        function renderTemplateRows() {
            const container = document.getElementById('tmplCategories');
            container.innerHTML = '';
            tmplState.rows.forEach((row, idx) => {
                const iconOptions = ICON_OPTIONS.map(ic =>
                    `<option value="${ic}" ${row.icon === ic ? 'selected' : ''}>${ic}</option>`
                ).join('');
                container.innerHTML += `
                    <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2">
                        <select class="border border-gray-200 rounded-lg px-2 py-1 text-sm w-14 text-center bg-white"
                            onchange="tmplState.rows[${idx}].icon = this.value; renderTemplateRows()">
                            ${iconOptions}
                        </select>
                        <input type="text" value="${row.name}" placeholder="Category name"
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm font-medium bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            oninput="tmplState.rows[${idx}].name = this.value">
                        <input type="number" value="${row.pct}" min="0" max="100" placeholder="%"
                            class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            oninput="tmplState.rows[${idx}].pct = parseInt(this.value) || 0; updateTemplatePreview()">
                        <span class="text-sm font-bold text-gray-400 w-6 text-center">%</span>
                        <button type="button" onclick="removeTemplateRow(${idx})"
                            class="text-red-400 hover:text-red-600 transition w-6 flex justify-center">
                            <i class="ph ph-x text-sm"></i>
                        </button>
                    </div>`;
            });
        }

        function updateTemplatePreview() {
            const bar = document.getElementById('tmplPreviewBar');
            const totalLabel = document.getElementById('tmplTotalPctLabel');
            const errorLabel = document.getElementById('tmplTotalError');
            const total = tmplState.rows.reduce((sum, r) => sum + (parseInt(r.pct) || 0), 0);
            totalLabel.textContent = total + '%';
            const isValid = total === 100;
            totalLabel.style.color = isValid ? '' : '#ef4444';
            errorLabel.style.display = isValid ? 'none' : 'block';

            bar.innerHTML = '';
            let offset = 0;
            tmplState.rows.forEach((row, i) => {
                const pct = parseInt(row.pct) || 0;
                if (pct > 0) {
                    const color = DIST_COLORS[i] || '#6b7280';
                    bar.innerHTML += `<div style="width:${pct}%;background:${color}" class="h-full transition-all duration-200 rounded-full"></div>`;
                }
            });
        }

        function saveTemplate() {
            const total = tmplState.rows.reduce((sum, r) => sum + (parseInt(r.pct) || 0), 0);
            if (total !== 100) {
                alert('Total must equal 100%!');
                return;
            }
            const savePct = document.getElementById('tmplSaveSlider').value;
            document.getElementById('distTemplateData').value = JSON.stringify(tmplState.rows);
            document.getElementById('distTemplateSavePct').value = savePct;
            closeTemplateModal();
            document.getElementById('distributionTemplateForm').submit();
        }

        function deleteTemplate() {
            if (!confirm('Delete this distribution template?')) return;
            closeTemplateModal();
            window.location.href = '/dashboard/distribution-template/delete';
        }

        // Update category bars on slider input
        function updateDistTotal() {
            const cats = ['Emergency Fund', 'Investment', 'Goals', 'Buffer'];
            const colors = {
                'Emergency Fund': '#22c55e',
                'Investment': '#3b82f6',
                'Goals': '#f59e0b',
                'Buffer': '#a78bfa',
            };
            cats.forEach(cat => {
                const val = parseInt(document.getElementById('distPct_' + cat).value) || 0;
                document.getElementById('distBar_' + cat).style.width = val + '%';
            });
        }

        // Called when a percentage input changes — update the corresponding bar
        ['Emergency Fund', 'Investment', 'Goals', 'Buffer'].forEach(cat => {
            const input = document.getElementById('distPct_' + cat);
            if (input) {
                input.addEventListener('input', updateDistTotal);
            }
        });

        function submitRetroDistribution() {
            // Collect selected transaction IDs
            const ids = Array.from(document.querySelectorAll('.dist-tx-check:checked'))
                .map(cb => cb.value);
            if (ids.length === 0) {
                alert('Please select at least one Money In to split.');
                return;
            }

            const savePct = parseInt(document.getElementById('distSaveSlider').value);
            const spendPct = 100 - savePct;

            // Collect distribution percentages
            const cats = ['Emergency Fund', 'Investment', 'Goals', 'Buffer'];
            let totalPct = 0;
            const dist = cats.map(cat => {
                const pct = parseInt(document.getElementById('distPct_' + cat).value) || 0;
                totalPct += pct;
                return { category: cat, pct: pct, amount: 0 }; // amount computed server-side
            });

            if (totalPct !== 100) {
                document.getElementById('distTotalError').classList.remove('hidden');
                return;
            }
            document.getElementById('distTotalError').classList.add('hidden');

            // Fill hidden form
            document.getElementById('distFormIds').value = JSON.stringify(ids);
            document.getElementById('distFormSavePct').value = savePct;
            document.getElementById('distFormSpendPct').value = spendPct;
            document.getElementById('distributionFormData').value = JSON.stringify(dist);

            closeDistributionModal();
            document.getElementById('distributionForm').submit();
        }

        // Strip distribution URL param if present (from old links)
        (function() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('dist')) {
                params.delete('dist');
                const cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', cleanUrl);
            }
        })();

        // ═══════════════════════════════════════════════════════════════
        // EXPENSE CATEGORIZER MODAL
        // ═══════════════════════════════════════════════════════════════
        let expenseForm = null;
        let expenseAmount = 0;
        let expenseDesc = '';
        let expenseDate = '';
        let selectedNow = null;
        let selectedCat = null;

        const CAT_CHOICES = {
            need: ['🏠 Bills', '🍎 Food', '🚌 Transport', '💊 Health', '📦 Other'],
            want: ['🎮 Fun', '🛍 Shopping', '☕ Treats', '🎁 Gifts', '📦 Other'],
        };

        function openExpenseModal(form) {
            if (!form.description.value.trim()) {
                alert('Please enter a description first!');
                form.description.focus();
                return;
            }
            if (!form.amount.value || parseFloat(form.amount.value) <= 0) {
                alert('Please enter an amount!');
                form.amount.focus();
                return;
            }
            expenseForm = form;
            expenseAmount = parseFloat(form.amount.value);
            expenseDesc = form.description.value.trim();
            expenseDate = form.transaction_date.value;

            document.getElementById('expenseAmountDisplay').textContent =
                'Rp ' + expenseAmount.toLocaleString('id-ID');
            document.getElementById('expenseDescDisplay').textContent =
                'for: ' + expenseDesc;

            // Set account type from form and update balance display
            const accSelect = document.getElementById('expenseAccountSelect');
            accSelect.value = form.account_type ? form.account_type.value : '';

            // Reset state
            selectedNow = null;
            selectedCat = null;
            document.getElementById('nwBtnNeed').classList.remove('active');
            document.getElementById('nwBtnWant').classList.remove('active');
            document.getElementById('catReveal').classList.remove('show');
            document.getElementById('expenseFormNow').value = '';
            document.getElementById('expenseFormCat').value = '';

            document.getElementById('expenseModal').classList.add('active');
        }

        function openExpenseModalForAmount(amount, desc) {
            expenseForm = null;
            expenseAmount = amount;
            expenseDesc = desc;
            expenseDate = new Date().toISOString().split('T')[0];
            document.getElementById('expenseAmountDisplay').textContent =
                'Rp ' + amount.toLocaleString('id-ID');
            document.getElementById('expenseDescDisplay').textContent =
                'for: ' + desc;
            selectedNow = null;
            selectedCat = null;
            document.getElementById('nwBtnNeed').classList.remove('active');
            document.getElementById('nwBtnWant').classList.remove('active');
            document.getElementById('catReveal').classList.remove('show');
            document.getElementById('expenseModal').classList.add('active');
        }

        function closeExpenseModal() {
            document.getElementById('expenseModal').classList.remove('active');
            expenseForm = null;
        }

        function selectNeedWant(value) {
            selectedNow = value;
            selectedCat = null;

            document.getElementById('nwBtnNeed').classList.toggle('active', value === 'need');
            document.getElementById('nwBtnWant').classList.toggle('active', value === 'want');

            // Build category chips
            const chips = document.getElementById('catChips');
            chips.innerHTML = '';
            CAT_CHOICES[value].forEach(cat => {
                const emoji = cat.split(' ')[0];
                const label = cat.split(' ').slice(1).join(' ');
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'cat-chip ' + value;
                chip.innerHTML = cat;
                chip.onclick = () => selectCategory(label, value, chip);
                chips.appendChild(chip);
            });

            document.getElementById('catReveal').classList.add('show');
            document.getElementById('expenseFormNow').value = value;
        }

        function selectCategory(cat, now, chipEl) {
            selectedCat = cat;
            document.querySelectorAll('#catChips .cat-chip').forEach(c => c.classList.remove('selected'));
            chipEl.classList.add('selected');
            document.getElementById('expenseFormCat').value = cat;
        }

        function submitWithCategory() {
            if (expenseForm) {
                document.getElementById('expenseFormDesc').value = expenseDesc;
                document.getElementById('expenseFormAmt').value = expenseAmount;
                document.getElementById('expenseFormDate').value = expenseDate;
            }
            document.getElementById('expenseFormNow').value = selectedNow || '';
            document.getElementById('expenseFormCat').value = selectedCat || '';
            document.getElementById('expenseFormAccount').value = document.getElementById('expenseAccountSelect').value || '';
            document.getElementById('expenseFormDistCat').value = document.getElementById('distCatSelect').value || '';

            // DEBUG
            console.log('submitWithCategory', {
                expenseDesc, expenseAmount, expenseDate,
                selectedNow, selectedCat,
                accountType: document.getElementById('expenseAccountSelect').value
            });

            closeExpenseModal();
            const msg = selectedNow === 'need'
                ? '🏠 Tagged as Need! Smart move.'
                : selectedNow === 'want'
                ? '🎉 Tagged as Want! Enjoy wisely.'
                : 'Expense logged!';
            showToast(msg, selectedNow === 'want' ? 'wan' : 'info');
            const f = document.getElementById('expenseForm');
            console.log('Expense form action:', f.action, {
                desc: document.getElementById('expenseFormDesc').value,
                now: document.getElementById('expenseFormNow').value,
                cat: document.getElementById('expenseFormCat').value,
            });
            f.submit();
        }

        // ═══════════════════════════════════════════════════════════════
        // SHARED: CONFETTI + TOAST
        // ═══════════════════════════════════════════════════════════════
        function triggerConfetti() {
            const colors = ['#fbbf24', '#22c55e', '#3b82f6', '#f472b6', '#a78bfa', '#fb923c'];
            for (let i = 0; i < 40; i++) {
                const p = document.createElement('div');
                p.className = 'confetti-particle';
                p.style.background = colors[Math.floor(Math.random() * colors.length)];
                p.style.left = (35 + Math.random() * 30) + '%';
                p.style.top = '35%';
                p.style.setProperty('--tx', (Math.random() * 200 - 100) + 'px');
                p.style.animationDelay = (Math.random() * 0.5) + 's';
                p.style.animationDuration = (1.5 + Math.random() * 1.5) + 's';
                document.body.appendChild(p);
                setTimeout(() => p.remove(), 3500);
            }
        }

        function showToast(message, type = '') {
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<span class="text-lg mr-2">💰</span><span class="text-sm font-semibold text-gray-800">' + message + '</span>';
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 4500);
        }
    </script>

</body>
</html>
