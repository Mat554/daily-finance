<?php
    $conditionLabels = [
        'surplus'     => ['label' => 'Surplus',      'color' => 'text-green-600',        'bg' => 'bg-green-100 text-green-700',   'icon' => '📈'],
        'break_even'  => ['label' => 'Break Even',  'color' => 'text-yellow-600',       'bg' => 'bg-yellow-100 text-yellow-700', 'icon' => '⚖️'],
        'deficit'     => ['label' => 'Deficit',     'color' => 'text-red-600',          'bg' => 'bg-red-100 text-red-700',      'icon' => '📉'],
    ];
    $cond = $conditionLabels[$report->condition] ?? $conditionLabels['break_even'];
    $scoreColor = $report->score >= 70 ? '#22c55e' : ($report->score >= 40 ? '#f59e0b' : '#ef4444');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $monthName }} Report</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card { @apply bg-white rounded-3xl p-6 shadow-sm border border-gray-100; }
        .health-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px 4px 8px; border-radius: 100px; font-size: 12px; font-weight: 700; letter-spacing: 0.03em; }
        .gauge-track { fill: none; stroke: #f1f5f9; stroke-width: 10; stroke-linecap: round; }
        .gauge-fill { fill: none; stroke-width: 10; stroke-linecap: round; transition: stroke-dashoffset 1.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .report-section { border-radius: 1.5rem; padding: 1.25rem; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white sticky top-0 z-50 shadow-lg shadow-indigo-200/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="/dashboard" class="w-10 h-10 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center hover:bg-white/30 transition">
                        <i class="ph ph-arrow-left text-white text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold leading-none">{{ $monthName }} Report</h1>
                        <p class="text-xs text-indigo-200 leading-none">{{ session('username') }}'s Monthly Financial Review</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/dashboard" class="text-sm text-indigo-200 hover:text-white font-medium">Dashboard</a>
                    <a href="/expenses" class="text-sm text-indigo-200 hover:text-white font-medium">Expenses</a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        <!-- Score + Condition Card -->
        <div class="card">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <!-- Score Gauge -->
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                        <i class="ph ph-heartbeat mr-1"></i>Monthly Score
                    </p>
                    <div class="relative w-24 h-24">
                        <svg width="96" height="96" viewBox="0 0 96 96">
                            <circle class="gauge-track" cx="48" cy="48" r="38"/>
                            <circle class="gauge-fill" cx="48" cy="48" r="38"
                                stroke="{{ $scoreColor }}"
                                stroke-dasharray="{{ 2 * 3.14159 * 38 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 38 * (1 - $report->score/100) }}"
                                transform="rotate(-90 48 48)"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-black text-gray-900">{{ $report->score }}</span>
                            <span class="text-[9px] text-gray-400 font-bold">/100</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Summary -->
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-blue-50 rounded-2xl p-4">
                        <p class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">Income</p>
                        <p class="text-lg font-black text-blue-600">Rp {{ number_format($report->total_income, 0) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-2xl p-4">
                        <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-1">Money Out</p>
                        <p class="text-lg font-black text-red-500">Rp {{ number_format($report->total_money_out, 0) }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-2xl p-4">
                        <p class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">Saved</p>
                        <p class="text-lg font-black text-emerald-600">Rp {{ number_format($report->total_saved, 0) }}</p>
                    </div>
                    <div class="rounded-2xl p-4 {{ $report->net_balance >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Net Balance</p>
                        <p class="text-lg font-black {{ $report->net_balance >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $report->net_balance >= 0 ? '+' : '' }}Rp {{ number_format($report->net_balance, 0) }}
                        </p>
                    </div>
                </div>

                <!-- Condition Badge -->
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                        <i class="ph ph-chart-line mr-1"></i>Condition
                    </p>
                    <span class="health-pill {{ $cond['bg'] }}">
                        <span>{{ $cond['icon'] }}</span>
                        <span class="{{ $cond['color'] }}">{{ $cond['label'] }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- What Went Well -->
        @if(count($summary['went_well']) > 0)
        <div class="report-section bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200">
            <h3 class="text-base font-bold text-green-800 mb-3 flex items-center gap-2">
                <span class="text-xl">✨</span> What Went Well
            </h3>
            <ul class="space-y-2">
                @foreach($summary['went_well'] as $item)
                <li class="flex items-start gap-2 text-sm text-green-700">
                    <span class="mt-0.5 text-green-500 shrink-0"><i class="ph ph-check-circle-fill"></i></span>
                    {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Areas to Improve -->
        @if(count($summary['improvements']) > 0)
        <div class="report-section bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200">
            <h3 class="text-base font-bold text-amber-800 mb-3 flex items-center gap-2">
                <span class="text-xl">📋</span> Areas to Improve
            </h3>
            <ul class="space-y-2">
                @foreach($summary['improvements'] as $item)
                <li class="flex items-start gap-2 text-sm text-amber-700">
                    <span class="mt-0.5 text-amber-500 shrink-0"><i class="ph ph-warning-circle-fill"></i></span>
                    {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Recommendations -->
        @if(count($summary['recommendations']) > 0)
        <div class="report-section bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200">
            <h3 class="text-base font-bold text-blue-800 mb-3 flex items-center gap-2">
                <span class="text-xl">💡</span> Next Month Recommendations
            </h3>
            <ul class="space-y-2">
                @foreach($summary['recommendations'] as $item)
                <li class="flex items-start gap-2 text-sm text-blue-700">
                    <span class="mt-0.5 text-blue-500 shrink-0"><i class="ph ph-lightbulb-fill"></i></span>
                    {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Month-over-Month Comparison -->
        @if($prevReport)
        <div class="card">
            <h3 class="text-sm font-bold text-gray-700 mb-4">
                <i class="ph ph-arrows-left-right text-gray-400 mr-1"></i>vs. Previous Month
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @php
                    $savedDiff = $report->total_saved - $prevReport->total_saved;
                    $netDiff = $report->net_balance - $prevReport->net_balance;
                    $incomeDiff = $report->total_income - $prevReport->total_income;
                @endphp
                <div class="text-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Savings Change</p>
                    <p class="text-lg font-black {{ $savedDiff >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $savedDiff >= 0 ? '+' : '' }}Rp {{ number_format($savedDiff, 0) }}
                    </p>
                    <p class="text-xs text-gray-400">vs prev</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Net Change</p>
                    <p class="text-lg font-black {{ $netDiff >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $netDiff >= 0 ? '+' : '' }}Rp {{ number_format($netDiff, 0) }}
                    </p>
                    <p class="text-xs text-gray-400">vs prev</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Income Change</p>
                    <p class="text-lg font-black {{ $incomeDiff >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $incomeDiff >= 0 ? '+' : '' }}Rp {{ number_format($incomeDiff, 0) }}
                    </p>
                    <p class="text-xs text-gray-400">vs prev</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Regenerate -->
        <div class="text-center">
            <form action="{{ route('monthly-report.generate', [$year, $month]) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm font-bold text-indigo-500 hover:text-indigo-700 px-4 py-2 rounded-xl hover:bg-indigo-50 transition border border-indigo-200">
                    <i class="ph ph-arrows-clockwise mr-1"></i>Regenerate Report
                </button>
            </form>
            <span class="text-xs text-gray-400 mx-2">·</span>
            <a href="/dashboard" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">
                Back to Dashboard
            </a>
        </div>

    </main>
</body>
</html>
