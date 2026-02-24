<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6">
        <h1 class="text-2xl font-bold text-center mb-6">Edit Transaction</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('update', $transaction->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT') 

            <div>
                <label class="block text-sm text-gray-600">Date</label>
                <input type="date" name="transaction_date" value="{{ $transaction->transaction_date }}" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-600">Description</label>
                <input type="text" name="description" value="{{ $transaction->description }}" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-600">Amount</label>
                <input type="number" name="amount" value="{{ $transaction->amount }}" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Type</label>
                <div class="flex gap-2">
                    <button type="submit" name="type" value="in" class="flex-1 py-2 rounded text-white {{ $transaction->type == 'in' ? 'bg-green-600' : 'bg-green-300 hover:bg-green-500' }}">Money In</button>
                    <button type="submit" name="type" value="out" class="flex-1 py-2 rounded text-white {{ $transaction->type == 'out' ? 'bg-red-600' : 'bg-red-300 hover:bg-red-500' }}">Money Out</button>
                </div>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="/" class="text-gray-500 hover:underline">Cancel and go back</a>
        </div>
    </div>

</body>
</html>