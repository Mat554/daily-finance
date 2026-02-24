<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance History</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">History</h1>
            <a href="/" class="text-blue-500 hover:underline">&larr; Back to Today</a>
        </div>

        <div class="space-y-6">
            @foreach($groupedTransactions as $date => $transactions)
                @php
                    $dayIn = $transactions->where('type', 'in')->sum('amount');
                    $dayOut = $transactions->where('type', 'out')->sum('amount');
                @endphp
                
                <div class="border rounded-lg p-4 bg-gray-50">
                    <h2 class="font-bold text-lg mb-2 border-b pb-2">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h2>
                    
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-green-600 font-bold">In: Rp {{ number_format($dayIn, 0) }}</span>
                        <span class="text-red-600 font-bold">Out: Rp {{ number_format($dayOut, 0) }}</span>
                    </div>

                    <ul class="text-sm divide-y">
                        @foreach($transactions as $t)
                            <li class="py-1 flex justify-between">
                                <span>{{ $t->description }}</span>
                                <span class="{{ $t->type == 'in' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $t->type == 'in' ? '+' : '-' }} {{ number_format($t->amount, 0) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    </div>

</body>
</html>