<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Finance Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6">
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

        <form action="{{ route('store') }}" method="POST" class="mb-6 space-y-3 border-b-2 border-gray-100 pb-6">
            @csrf
            
            <input type="date" name="transaction_date" value="{{ $currentDate }}" class="w-full border p-2 rounded text-gray-700" onchange="window.location.href='/?date=' + this.value" required>
            
            <input type="text" name="description" placeholder="What was it?" class="w-full border p-2 rounded" required>
            <input type="number" name="amount" placeholder="Amount" class="w-full border p-2 rounded" required>
            
            <div class="flex gap-2 pt-2">
                <button type="submit" name="type" value="in" class="flex-1 bg-green-500 text-white font-semibold py-2 rounded hover:bg-green-600 transition">Money In</button>
                <button type="submit" name="type" value="out" class="flex-1 bg-red-500 text-white font-semibold py-2 rounded hover:bg-red-600 transition">Money Out</button>
            </div>
        </form>

        <div class="mb-8">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Today's Entries</h2>
            
            @if($transactions->isEmpty())
                <p class="text-center text-gray-400 text-sm py-4 italic">No transactions yet today.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($transactions as $t)
                        <li class="py-3 flex justify-between items-center">
                            <div>
                                <span class="block text-gray-800 font-medium">{{ $t->description }}</span>
                                <a href="{{ route('edit', $t->id) }}" class="text-xs text-blue-500 hover:text-blue-700 hover:underline">Edit</a>
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
            <a href="{{ route('history') }}" class="block w-full text-center bg-gray-800 text-white font-bold py-3 rounded hover:bg-gray-900 transition shadow-sm">
                End the Day
            </a>
            
            <a href="{{ route('history') }}" class="block w-full text-center border-2 border-gray-200 text-gray-600 font-bold py-2 rounded hover:bg-gray-50 transition">
                View History
            </a>
        </div>

    </div> </body>
</html>