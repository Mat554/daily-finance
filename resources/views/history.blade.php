<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance History</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6 mb-10">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">History</h1>
            <a href="/" class="text-blue-500 hover:underline">&larr; Back to Today</a>
        </div>

        <div class="space-y-6">
            @foreach($groupedTransactions as $date => $transactions)
                @php
                    $dayIn = $transactions->where('type', 'in')->sum('amount');
                    $dayOut = $transactions->where('type', 'out')->sum('amount');
                    $dayBalance = $dayIn - $dayOut;
                @endphp
                
                <div class="border rounded-lg p-4 bg-gray-50 shadow-sm">
                    <h2 class="font-bold text-lg mb-3 border-b pb-2 text-gray-800">
                        {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                    </h2>
                    
                    <ul class="text-sm divide-y mb-4">
                        @foreach($transactions as $t)
                            <li class="py-2 flex justify-between items-center">
                                <span class="text-gray-700">{{ $t->description }}</span>
                                <span class="{{ $t->type == 'in' ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $t->type == 'in' ? '+' : '-' }} {{ number_format($t->amount, 0) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="bg-white border rounded-lg p-3 text-sm shadow-sm">
                        <div class="flex justify-between text-gray-500 mb-1">
                            <span>Total In:</span>
                            <span class="text-green-600">+ Rp {{ number_format($dayIn, 0) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500 mb-2 border-b pb-2">
                            <span>Total Out:</span>
                            <span class="text-red-600">- Rp {{ number_format($dayOut, 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center font-bold text-base mt-1">
                            <span class="text-gray-800">Daily Balance:</span>
                            <span class="{{ $dayBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $dayBalance >= 0 ? '+' : '' }} Rp {{ number_format($dayBalance, 0) }}
                            </span>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-8 border-t-2 border-gray-200 pt-6">
            <h2 class="text-xl font-bold mb-4 text-center text-gray-800">All-Time Summary</h2>
            
            @php
                $grandTotalIn = $groupedTransactions->flatten()->where('type', 'in')->sum('amount');
                $grandTotalOut = $groupedTransactions->flatten()->where('type', 'out')->sum('amount');
                $balance = $grandTotalIn - $grandTotalOut;
            @endphp

            <div class="bg-gray-800 text-white rounded-xl p-5 shadow-lg">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-300">Total Money In:</span>
                    <span class="text-green-400 font-semibold">+ Rp {{ number_format($grandTotalIn, 0) }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-300">Total Money Out:</span>
                    <span class="text-red-400 font-semibold">- Rp {{ number_format($grandTotalOut, 0) }}</span>
                </div>
                
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-600 text-lg">
                    <span class="font-bold">Net Balance:</span>
                    <span class="font-bold {{ $balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        Rp {{ number_format($balance, 0) }}
                    </span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>