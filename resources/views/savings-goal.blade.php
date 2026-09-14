<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Goal</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card { @apply bg-white rounded-3xl p-6 shadow-sm border border-gray-100; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-gradient-to-r from-pink-500 to-rose-500 text-white sticky top-0 z-50 shadow-lg shadow-pink-200/50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="/dashboard" class="w-10 h-10 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center hover:bg-white/30 transition">
                        <i class="ph ph-arrow-left text-white text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold leading-none">Savings Goal</h1>
                        <p class="text-xs text-pink-200 leading-none">{{ session('username') }}'s Finance</p>
                    </div>
                </div>
                <nav class="flex items-center gap-1">
                    <a href="/dashboard" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm text-pink-200 hover:text-white hover:bg-white/10 transition font-medium">
                        <i class="ph ph-chart-line-up text-base"></i>Dashboard
                    </a>
                    <a href="/expenses" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm text-pink-200 hover:text-white hover:bg-white/10 transition font-medium">
                        <i class="ph ph-wallet text-base"></i>Expenses
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        <!-- Set Goal -->
        <div class="card">
            <h3 class="text-sm font-bold text-gray-700 mb-4">
                <i class="ph ph-piggy-bank text-pink-500 mr-1"></i>Your Monthly Target
            </h3>
            <form action="{{ route('savings-goal.store') }}" method="POST" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Monthly Savings Goal (Rp)</label>
                    <input type="number" name="goal" value="{{ $goal > 0 ? $goal : '' }}" placeholder="e.g. 2000000" min="0"
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 bg-gray-50 focus:bg-white transition">
                </div>
                <button type="submit" class="bg-pink-500 hover:bg-pink-600 active:scale-95 text-white font-bold py-3 px-6 rounded-2xl transition-all text-sm shadow-sm">
                    <i class="ph ph-check mr-1"></i>Set Goal
                </button>
            </form>
            @if($goal > 0)
                <form action="{{ route('savings-goal.destroy') }}" method="POST" class="mt-3">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-bold transition">
                        <i class="ph ph-trash mr-0.5"></i>Clear goal
                    </button>
                </form>
            @endif
        </div>

        <!-- Progress -->
        @php
            $currentSaved = \App\Models\Transaction::where('username', session('username'))
                ->where('type', 'in')
                ->where('is_split', true)
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('saved_amount');
            $goal = $goal > 0 ? $goal : 0;
            $progress = $goal > 0 ? min(100, round($currentSaved / $goal * 100)) : 0;
            $daysInMonth = now()->daysInMonth;
            $daysElapsed = now()->day;
            $expectedByNow = $goal > 0 ? round($goal * $daysElapsed / $daysInMonth) : 0;
            $onTrack = $expectedByNow > 0 && $currentSaved >= $expectedByNow;
            $projectedSavings = $goal > 0 ? round($currentSaved / max(1, $daysElapsed) * $daysInMonth) : 0;
        @endphp

        @if($goal > 0)
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-chart-line-up text-emerald-500"></i>
                        This Month's Progress
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('F Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black text-emerald-600">Rp {{ number_format($currentSaved, 0) }}</p>
                    <p class="text-xs text-gray-400">of Rp {{ number_format($goal, 0) }} goal</p>
                </div>
            </div>

            <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden mb-3">
                <div class="h-full rounded-full transition-all duration-700
                    {{ $onTrack ? 'bg-emerald-400' : 'bg-amber-400' }}"
                    style="width: {{ $progress }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mb-4">
                <span>{{ $progress }}% complete</span>
                <span>{{ $goal - $currentSaved > 0 ? 'Rp ' . number_format($goal - $currentSaved, 0) . ' remaining' : 'Goal reached!' }}</span>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-50 rounded-2xl p-3 text-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Expected by Now</p>
                    <p class="text-sm font-black text-gray-700">Rp {{ number_format($expectedByNow, 0) }}</p>
                </div>
                <div class="rounded-2xl p-3 text-center {{ $onTrack ? 'bg-emerald-50' : 'bg-amber-50' }}">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1 {{ $onTrack ? 'text-emerald-600' : 'text-amber-600' }}">Status</p>
                    <p class="text-sm font-black {{ $onTrack ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $onTrack ? 'On Track' : 'Behind Pace' }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-3 text-center">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Projected</p>
                    <p class="text-sm font-black text-gray-700">Rp {{ number_format($projectedSavings, 0) }}</p>
                </div>
            </div>

            @if(!$onTrack && $currentSaved > 0)
                <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200">
                    <p class="text-xs text-amber-700 font-semibold">
                        <i class="ph ph-warning mr-1"></i>
                        At this pace, you'll save ~Rp {{ number_format($projectedSavings, 0) }}
                        @if($projectedSavings < $goal)
                            — {{ $goal - $projectedSavings > 0 ? 'Rp ' . number_format($goal - $projectedSavings, 0) . ' short' : '' }} of your goal
                        @else
                            — you'll hit your goal!
                        @endif
                    </p>
                </div>
            @endif
        </div>
        @else
        <div class="card text-center py-12">
            <div class="text-5xl mb-3">🎯</div>
            <p class="text-sm font-semibold text-gray-500 mb-1">No savings goal set</p>
            <p class="text-xs text-gray-400">Set a monthly target above to track your savings progress</p>
        </div>
        @endif

    </main>
</body>
</html>
