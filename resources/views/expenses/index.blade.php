<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Budgets</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card { @apply bg-white rounded-3xl p-6 shadow-sm border border-gray-100; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box { background: white; border-radius: 2rem; padding: 2rem; max-width: 480px; width: 90%; transform: scale(0.9) translateY(20px); transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1); }
        .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
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
                        <h1 class="text-lg font-bold leading-none">Monthly Budgets</h1>
                        <p class="text-xs text-indigo-200 leading-none">{{ session('username') }}'s Finance</p>
                    </div>
                </div>
                <nav class="flex items-center gap-1">
                    <a href="/dashboard" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm text-indigo-200 hover:text-white hover:bg-white/10 transition font-medium">
                        <i class="ph ph-chart-line-up text-base"></i>Dashboard
                    </a>
                    <a href="/savings-goal" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm text-indigo-200 hover:text-white hover:bg-white/10 transition font-medium">
                        <i class="ph ph-target text-base"></i>Savings Goal
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        <!-- Add Expense Form -->
        <div class="card">
            <h3 class="text-sm font-bold text-gray-700 mb-4">
                <i class="ph ph-plus-circle text-emerald-500 mr-1"></i>Add Monthly Budget
            </h3>
            <form action="{{ route('expenses.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Name</label>
                    <input type="text" name="name" placeholder="Groceries, Rent..." required
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Type</label>
                    <select name="category" class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                        <option value="variable">Variable</option>
                        <option value="fixed">Fixed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Monthly Budget</label>
                    <input type="number" name="allocated_amount" placeholder="0" required min="0"
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Keywords <span class="font-normal text-gray-300">(comma-sep)</span></label>
                    <input type="text" name="keywords" placeholder="gofood, grab, taksi"
                        class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                        <input type="checkbox" name="rollover" class="rounded"> Rollover
                    </label>
                    <button type="submit"
                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold py-3 rounded-2xl transition-all text-sm shadow-sm">
                        <i class="ph ph-plus mr-1"></i>Add
                    </button>
                </div>
            </form>
        </div>

        <!-- Expenses List -->
        @if(empty($expensesWithSpending))
            <div class="card text-center py-12">
                <div class="text-5xl mb-3">📋</div>
                <p class="text-sm font-semibold text-gray-500 mb-1">No monthly budgets yet</p>
                <p class="text-xs text-gray-400">Add a budget above to track your spending against it</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($expensesWithSpending as $item)
                    @php $exp = $item['expense']; @endphp
                    <div class="card {{ !$exp->is_active ? 'opacity-50' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-base font-bold text-gray-900">{{ $exp->name }}</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider
                                        {{ $exp->category === 'fixed' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                                        {{ $exp->category }}
                                    </span>
                                    @if($exp->rollover)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Rollover</span>
                                    @endif
                                    @if(!$exp->is_active)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-500">Paused</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400">
                                    Keywords:
                                    @forelse($exp->keywords ?? [] as $kw)
                                        <span class="inline-block bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-mono mr-1">{{ $kw }}</span>
                                    @empty
                                        <span class="italic text-gray-300">none</span>
                                    @endforelse
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-lg font-black {{ $item['is_over'] ? 'text-red-500' : 'text-gray-900' }}">
                                    Rp {{ number_format($item['spent'], 0) }}
                                    <span class="text-xs font-normal text-gray-400">/ Rp {{ number_format($exp->allocated_amount, 0) }}</span>
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $item['remaining'] > 0 ? 'Rp ' . number_format($item['remaining'], 0) . ' left' : ($item['is_over'] ? 'Rp ' . number_format(abs($item['remaining']), 0) . ' over' : 'On budget') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 {{ $item['is_over'] ? 'bg-red-400' : 'bg-emerald-400' }}"
                                    style="width: {{ min(100, $item['pct']) }}%"></div>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-[10px] text-gray-400">{{ $item['pct'] }}% used</span>
                                @if($exp->rollover && $item['remaining'] > 0)
                                    <span class="text-[10px] text-emerald-500 font-bold">Rollover: Rp {{ number_format($item['remaining'], 0) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-50">
                            <form action="{{ route('expenses.toggle', $exp) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-xl border transition
                                    {{ $exp->is_active ? 'border-red-200 text-red-400 hover:bg-red-50' : 'border-emerald-200 text-emerald-500 hover:bg-emerald-50' }}">
                                    <i class="ph {{ $exp->is_active ? 'ph-pause' : 'ph-play' }} mr-0.5"></i>
                                    {{ $exp->is_active ? 'Pause' : 'Resume' }}
                                </button>
                            </form>
                            <button type="button" onclick="openEditModal({{ $exp->id }}, '{{ $exp->name }}', '{{ $exp->category }}', {{ $exp->allocated_amount }}, {{ json_encode($exp->keywords ?? []) }}, {{ $exp->rollover ? 'true' : 'false' }})"
                                class="text-xs font-bold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                                <i class="ph ph-pencil-simple mr-0.5"></i>Edit
                            </button>
                            <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="inline ml-auto"
                                onsubmit="return confirm('Delete this budget?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-xl border border-red-200 text-red-400 hover:bg-red-50 transition">
                                    <i class="ph ph-trash mr-0.5"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

    <!-- Edit Expense Modal -->
    <div id="editModal" class="modal-overlay" onclick="if(event.target===this)closeEditModal()">
        <div class="modal-box">
            <form id="editForm" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="text-center mb-2">
                    <h2 class="text-xl font-black text-gray-900">Edit Budget</h2>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Name</label>
                    <input type="text" name="name" id="editName" required class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Type</label>
                        <select name="category" id="editCategory" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="variable">Variable</option>
                            <option value="fixed">Fixed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Budget</label>
                        <input type="number" name="allocated_amount" id="editAmount" required min="0" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Keywords <span class="font-normal text-gray-300">(comma-sep)</span></label>
                    <input type="text" name="keywords" id="editKeywords" placeholder="gofood, grab..." class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="rollover" id="editRollover" class="rounded">
                    <label for="editRollover" class="text-sm text-gray-600 cursor-pointer">Rollover unused budget to next month</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-500 text-white text-sm font-bold hover:bg-indigo-600 active:scale-95 transition shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(id, name, category, amount, keywords, rollover) {
        document.getElementById('editForm').action = '/expenses/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editCategory').value = category;
        document.getElementById('editAmount').value = amount;
        document.getElementById('editKeywords').value = (keywords || []).join(', ');
        document.getElementById('editRollover').checked = rollover;
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
    </script>
</body>
</html>
