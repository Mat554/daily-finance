<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — {{ session('username') }}</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import 'tailwindcss';
        .cat-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 700; }
        .cat-card { border-radius: 1.5rem; padding: 1.25rem; border: 1px solid; transition: all 0.2s; }
        .cat-card:hover { filter: brightness(0.97); }
        .net-positive { color: #16a34a; }
        .net-negative { color: #dc2626; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box { background: white; border-radius: 2rem; padding: 2rem; max-width: 420px; width: 90%; transform: scale(0.9) translateY(20px); transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1); }
        .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white sticky top-0 z-50 shadow-lg">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ session('landing', 'tracker') === 'dashboard' ? '/dashboard' : '/' }}" class="text-white/80 hover:text-white transition">
                        <i class="ph ph-arrow-left text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold">Categories</h1>
                        <p class="text-xs text-indigo-200">
                            <i class="ph ph-tag mr-0.5"></i>
                            Monthly spending by category
                        </p>
                    </div>
                </div>
                <button onclick="openCreateModal()"
                    class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold px-4 py-2 rounded-xl transition flex items-center gap-1.5">
                    <i class="ph ph-plus"></i> New
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6 space-y-5">

        <!-- Month selector -->
        <div class="flex items-center justify-between">
            <form method="GET" class="flex items-center gap-3">
                <select name="month" class="dash-select" onchange="this.form.submit()">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year" class="dash-select" onchange="this.form.submit()">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
            <a href="/dashboard" class="text-xs font-medium text-indigo-500 hover:text-indigo-700">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Success message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-2xl p-3 text-sm text-green-700 font-semibold">
                <i class="ph ph-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Summary row -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Income</p>
                <p class="text-lg font-black text-blue-600">Rp {{ number_format(collect($monthlyData)->sum('income'), 0) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Spending</p>
                <p class="text-lg font-black text-red-500">Rp {{ number_format(collect($monthlyData)->sum('spending'), 0) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Net</p>
                @php $netTotal = collect($monthlyData)->sum('net'); @endphp
                <p class="text-lg font-black {{ $netTotal >= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $netTotal >= 0 ? '+' : '' }}Rp {{ number_format($netTotal, 0) }}
                </p>
            </div>
        </div>

        <!-- Category cards -->
        @forelse($categories as $cat)
            @php
                $data = $monthlyData[$cat->id] ?? ['income' => 0, 'spending' => 0, 'net' => 0];
                $bgAlpha = '15';
            @endphp
            <div class="cat-card bg-white" style="border-color: {{ $cat->color ?? '#e5e7eb' }}30;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg font-bold"
                            style="background: {{ $cat->color ?? '#6b7280' }}20; color: {{ $cat->color ?? '#6b7280' }};">
                            {{ $cat->icon ?? '📦' }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">{{ $cat->name }}</h3>
                            <p class="text-xs text-gray-400">
                                {{ $cat->transactions()->whereYear('transaction_date', $year)->whereMonth('transaction_date', $month)->count() }} transactions
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openEditModal({{ $cat->id }}, '{{ htmlspecialchars($cat->name, ENT_QUOTES) }}', '{{ $cat->icon ?? '📦' }}', '{{ $cat->color ?? '#6b7280' }}')"
                            class="p-2 rounded-xl text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition">
                            <i class="ph ph-pencil-simple"></i>
                        </button>
                        <form action="/categories/{{ $cat->id }}" method="POST"
                            onsubmit="return confirm('Delete category &quot;{{ addslashes($cat->name) }}&quot;? Existing transactions will keep their history.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Income / Spending -->
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold text-blue-400 uppercase tracking-wider mb-0.5">Income</p>
                        <p class="text-sm font-black text-blue-600">Rp {{ number_format($data['income'], 0) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold text-red-400 uppercase tracking-wider mb-0.5">Spending</p>
                        <p class="text-sm font-black text-red-500">Rp {{ number_format($data['spending'], 0) }}</p>
                    </div>
                    <div class="rounded-xl p-3 text-center" style="background: {{ abs($data['net']) > 0 ? ($data['net'] >= 0 ? '#dcfce7' : '#fee2e2') : '#f9fafb' }}">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Net</p>
                        <p class="text-sm font-black {{ $data['net'] >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $data['net'] >= 0 ? '+' : '' }}Rp {{ number_format($data['net'], 0) }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <div class="text-5xl mb-4">📂</div>
                <p class="text-gray-500 font-semibold mb-2">No categories yet</p>
                <p class="text-gray-400 text-sm mb-6">Categories are created automatically on your first visit.</p>
                <button onclick="openCreateModal()"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-6 py-3 rounded-2xl transition">
                    Create Category
                </button>
            </div>
        @endforelse
    </main>

    <!-- Create Modal -->
    <div id="createModal" class="modal-overlay" onclick="if(event.target===this)closeCreateModal()">
        <div class="modal-box">
            <h2 class="text-xl font-black text-gray-900 mb-5 text-center">New Category</h2>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Name</label>
                    <input type="text" name="name" placeholder="e.g. Groceries" required maxlength="50"
                        class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Icon</label>
                        <select name="icon" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['🍔','🚗','🎬','🛍','📄','💊','📚','🏠','✈️','🎓','💰','🎁','💼','🎯','📦','🍕','☕','🛒','💳','⚡'] as $icon)
                                <option value="{{ $icon }}">{{ $icon }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Color</label>
                        <select name="color" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['#f97316','#3b82f6','#a855f7','#ec4899','#eab308','#22c55e','#06b6d4','#ef4444','#8b5cf6','#14b8a6','#f59e0b','#6b7280'] as $color)
                                <option value="{{ $color }}" style="background:{{ $color }}20;color:{{ $color }}">{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()"
                        class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold text-sm shadow-lg hover:shadow-xl transition">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay" onclick="if(event.target===this)closeEditModal()">
        <div class="modal-box">
            <h2 class="text-xl font-black text-gray-900 mb-5 text-center">Edit Category</h2>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Name</label>
                    <input type="text" name="name" id="editName" required maxlength="50"
                        class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Icon</label>
                        <select name="icon" id="editIcon" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['🍔','🚗','🎬','🛍','📄','💊','📚','🏠','✈️','🎓','💰','🎁','💼','🎯','📦','🍕','☕','🛒','💳','⚡'] as $icon)
                                <option value="{{ $icon }}">{{ $icon }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Color</label>
                        <select name="color" id="editColor" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['#f97316','#3b82f6','#a855f7','#ec4899','#eab308','#22c55e','#06b6d4','#ef4444','#8b5cf6','#14b8a6','#f59e0b','#6b7280'] as $color)
                                <option value="{{ $color }}">{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold text-sm shadow-lg hover:shadow-xl transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() { document.getElementById('createModal').classList.add('active'); }
        function closeCreateModal() { document.getElementById('createModal').classList.remove('active'); }
        function openEditModal(id, name, icon, color) {
            document.getElementById('editForm').action = '/categories/' + id;
            document.getElementById('editName').value = name;
            document.getElementById('editIcon').value = icon;
            document.getElementById('editColor').value = color;
            document.getElementById('editModal').classList.add('active');
        }
        function closeEditModal() { document.getElementById('editModal').classList.remove('active'); }
    </script>
</body>
</html>
