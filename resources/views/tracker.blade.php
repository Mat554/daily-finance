<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Finance Tracker</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Card entrance animation */
        .tracker-card {
            animation: slideUp 0.4s ease-out both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Form card entrance */
        .entries-card { animation: slideUp 0.4s ease-out 0.1s both; }
        /* Button press feedback */
        .btn-press {
            transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.1s ease;
        }
        .btn-press:active {
            transform: scale(0.97);
        }
        /* Input focus transitions */
        .form-input {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        /* Summary card subtle pulse on load */
        .summary-card {
            animation: fadeIn 0.5s ease-out 0.05s both;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-md mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="ph ph-wallet text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-800">{{ session('username') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <form action="{{ route('preference') }}" method="POST" onsubmit="this._target.value = '{{ session('landing') === 'dashboard' ? '/' : '/dashboard' }}'">
                        @csrf
                        <input type="hidden" name="landing" value="{{ session('landing') === 'dashboard' ? 'tracker' : 'dashboard' }}">
                        <input type="hidden" name="_target" value="">
                        <button type="submit" class="text-xs font-medium flex items-center gap-1 transition
                            {{ session('landing') === 'tracker' ? 'text-blue-600 font-bold' : 'text-gray-400 hover:text-blue-500' }}">
                            <i class="ph {{ session('landing') === 'tracker' ? 'ph-house-fill' : 'ph-house' }}"></i>
                            Default: {{ session('landing') === 'tracker' ? 'Tracker' : 'Dashboard' }}
                        </button>
                    </form>
                    <a href="/history?from=tracker" class="text-xs font-medium flex items-center gap-1 transition text-gray-400 hover:text-blue-500" title="History">
                        <i class="ph ph-clock-counter-clockwise"></i>
                        <span class="hidden sm:inline">History</span>
                    </a>
                    <a href="/logout" class="text-xs text-gray-400 hover:text-red-500 flex items-center gap-1 transition">
                        <i class="ph ph-sign-out"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-md mx-auto px-4 py-6">
       <h1 class="text-2xl font-bold text-center mb-6">
    Tracker for {{ \Carbon\Carbon::parse($currentDate)->format('M d, Y') }}
</h1>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-green-100 p-4 rounded-lg text-center">
                <span class="text-green-600 font-bold">IN</span>
                <p class="text-xl font-semibold">Rp {{ number_format($totalIn, 0) }}</p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg text-center">
                <span class="text-red-600 font-bold">OUT</span>
                <p class="text-xl font-semibold">Rp {{ number_format($totalOut, 0) }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('store') }}" method="POST" class="tracker-card mb-6 space-y-3 border-b-2 border-gray-100 pb-6">
            @csrf

            <input type="date" name="transaction_date" value="{{ $currentDate }}" class="w-full border p-2 rounded text-gray-700 form-input" onchange="window.location.href='/?date=' + this.value" required>

            <input type="text" name="description" placeholder="What was it?" class="w-full border p-2 rounded form-input" required>
            <input type="number" name="amount" placeholder="Amount" class="w-full border p-2 rounded form-input" required>
            <select name="category_id" class="w-full border p-2 rounded text-gray-600 form-input">
                <option value="">— Category (optional) —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $lastUsedCategoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->icon }} {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="account_type" class="w-full border p-2 rounded text-gray-600 form-input">
                <option value="">— Account (optional) —</option>
                @foreach(['Cash', 'Bank', 'E-Wallet', 'Savings'] as $acc)
                    <option value="{{ $acc }}">{{ $acc }}</option>
                @endforeach
            </select>

            <div class="flex gap-2 pt-2">
                <button type="submit" name="type" value="in" class="btn-press flex-1 bg-green-500 text-white font-semibold py-2 rounded hover:bg-green-600 transition">Money In</button>
                <button type="submit" name="type" value="out" class="btn-press flex-1 bg-red-500 text-white font-semibold py-2 rounded hover:bg-red-600 transition">Money Out</button>
            </div>
        </form>

        <div class="entries-card mb-8">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Today's Entries</h2>
            
            @if($transactions->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4 italic">No transactions yet today.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($transactions as $t)
                        <li class="py-3 flex justify-between items-center">
                            <div>
                                <span class="block text-gray-800 font-medium">{{ $t->description }}</span>
                                @if($t->category)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-1.5 py-0.5 rounded-full"
                                        style="background: {{ $t->category->color ?? '#6b7280' }}20; color: {{ $t->category->color ?? '#6b7280' }};">
                                        {{ $t->category->icon ?? '📦' }} {{ $t->category->name }}
                                    </span>
                                @elseif($t->expense_category)
                                    <span class="text-xs text-gray-400">{{ $t->expense_category }}</span>
                                @endif
                                <a href="{{ route('edit', $t->id) }}" class="block text-xs text-blue-500 hover:text-blue-700 hover:underline">Edit</a>
                            </div>
                            <span class="font-bold {{ $t->type == 'in' ? 'text-green-500' : 'text-red-500' }}">
                                {{ $t->type == 'in' ? '+' : '-' }} Rp {{ number_format($t->amount, 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="space-y-3">
            <a href="{{ route('history') }}?from=tracker" class="btn-press block w-full text-center bg-gray-800 text-white font-bold py-3 rounded hover:bg-gray-900 transition shadow-sm">
                End the Day
            </a>

            <a href="{{ route('history') }}?from=tracker" class="btn-press block w-full text-center border-2 border-gray-200 text-gray-600 font-bold py-2 rounded hover:bg-gray-50 transition">
                View History
            </a>
        </div>

    </div>

</body>
</html>