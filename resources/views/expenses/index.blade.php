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
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-7 gap-3 items-end">
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
                        <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Budget</label>
                        <input type="number" name="allocated_amount" placeholder="0" required min="0"
                            class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Frequency</label>
                        <select name="payment_frequency" class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                            <option value="monthly">Monthly</option>
                            <option value="biweekly">Biweekly</option>
                            <option value="weekly">Weekly</option>
                            <option value="onetime">One-time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Due Date <span class="font-normal text-gray-300">(day)</span></label>
                        <input type="number" name="due_date" placeholder="e.g. 15" min="1" max="31"
                            class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Keywords</label>
                        <input type="text" name="keywords" placeholder="gofood, grab..."
                            class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                            <input type="checkbox" name="rollover" class="rounded"> Rollover
                        </label>
                        <button type="submit"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold py-3 rounded-2xl transition-all text-sm shadow-sm">
                            <i class="ph ph-plus mr-1"></i>Add
                        </button>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_credit" id="addIsCredit" class="rounded" onclick="toggleCreditFields('add')">
                        <span class="text-xs font-bold text-gray-600"><i class="ph ph-credit-card mr-1"></i>This is a credit / loan</span>
                    </label>
                    <div id="addCreditFields" class="hidden mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Credit Limit</label>
                            <input type="number" name="credit_limit" placeholder="Max limit" min="0" step="0.01"
                                class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Current Balance</label>
                            <input type="number" name="current_balance" placeholder="Amount owed" min="0" step="0.01"
                                class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Min Payment</label>
                            <input type="number" name="minimum_payment" placeholder="Monthly min" min="0" step="0.01"
                                class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Remind (days before)</label>
                            <input type="number" name="reminder_days" placeholder="3" min="1" max="30" value="3"
                                class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition">
                        </div>
                    </div>
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
            <div class="space-y-4">
                @foreach($expensesWithSpending as $item)
                    @php $exp = $item['expense']; @endphp
                    <div class="card {{ !$exp->is_active ? 'opacity-50' : '' }}">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h4 class="text-base font-bold text-gray-900">{{ $exp->name }}</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider
                                        {{ $exp->category === 'fixed' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                                        {{ $exp->category }}
                                    </span>
                                    @if($exp->rollover)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Rollover</span>
                                    @endif
                                    @if($exp->is_credit)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600"><i class="ph ph-credit-card mr-0.5"></i>Credit</span>
                                    @endif
                                    @if($exp->payment_frequency === 'onetime')
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-600">
                                            One-time
                                        </span>
                                    @elseif(($exp->payment_frequency ?? 'monthly') !== 'monthly')
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-600">
                                            {{ ucfirst($exp->payment_frequency ?? 'monthly') }}
                                        </span>
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
                                    {{ $item['remaining'] > 0 ? 'Rp ' . number_format($item['remaining'], 0) . ' spent' : ($item['is_over'] ? 'Rp ' . number_format(abs($item['remaining']), 0) . ' over' : 'On budget') }}
                                </p>
                            </div>
                        </div>

                        <!-- Due Date + Month Info -->
                        @if($exp->due_date)
                        <div class="mb-3 p-3 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold text-indigo-600 mb-0.5">
                                    <i class="ph ph-calendar-check mr-1"></i>{{ $exp->payment_frequency === 'onetime' ? 'One-time' : 'Payment Due' }}
                                </p>
                                <p class="text-sm font-black text-indigo-800">
                                    {{ $item['next_due_date_formatted'] }} of every month
                                    @if($exp->payment_frequency === 'onetime')
                                        <span class="text-xs font-normal text-teal-600">(one-time)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-indigo-500">
                                    Next due: <strong>{{ $item['next_due_month'] }}</strong>
                                    @if(isset($item['days_until_due']) && $item['days_until_due'] !== null)
                                        @if($item['is_past_due'])
                                            <span class="text-red-500 font-bold ml-1">· OVERDUE!</span>
                                        @elseif($item['is_upcoming'])
                                            <span class="text-amber-500 font-bold ml-1">· {{ $item['days_until_due'] }}d left</span>
                                        @else
                                            <span class="text-gray-400 ml-1">· {{ $item['days_until_due'] }}d left</span>
                                        @endif
                                    @endif
                                </p>
                                @if(isset($item['recommended_daily_save']) && $item['recommended_daily_save'] && $item['days_until_due'] > 0 && $item['payment_remaining'] > 0)
                                    <p class="text-xs text-emerald-600 mt-1">
                                        <i class="ph ph-piggy-bank mr-0.5"></i>Save <strong>Rp {{ number_format($item['recommended_daily_save'], 0) }}/day</strong> to pay it off
                                    </p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] text-indigo-400 uppercase font-bold tracking-wider">
                                    {{ $exp->payment_frequency === 'onetime' ? 'Total Due' : 'Monthly Due' }}
                                </p>
                                <p class="text-xl font-black text-indigo-700">Rp {{ number_format($item['monthly_due_amount'], 0) }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Payment Status -->
                        @if($exp->due_date)
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-500 font-semibold">
                                    Paid this month:
                                    <strong class="{{ $item['is_payment_complete'] ? 'text-emerald-600' : 'text-gray-700' }}">
                                        Rp {{ number_format($item['total_paid'], 0) }}
                                    </strong>
                                </span>
                                <span class="text-xs font-bold {{ $item['payment_remaining'] > 0 ? 'text-amber-500' : 'text-emerald-500' }}">
                                    {{ $item['is_payment_complete'] ? '✓ Paid' : 'Rp ' . number_format($item['payment_remaining'], 0) . ' remaining' }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 {{ $item['is_payment_complete'] ? 'bg-emerald-400' : 'bg-amber-400' }}"
                                    style="width: {{ $item['monthly_due_amount'] > 0 ? min(100, round($item['total_paid'] / $item['monthly_due_amount'] * 100)) : 0 }}%"></div>
                            </div>
                        </div>
                        @endif

                        <!-- Credit Utilization -->
                        @if($exp->is_credit && $exp->credit_limit)
                        <div class="mb-3 p-3 rounded-2xl bg-amber-50 border border-amber-100">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-amber-600 font-semibold">Credit Utilization</span>
                                <span class="text-xs font-bold {{ ($item['credit_utilization'] ?? 0) > 70 ? 'text-red-500' : 'text-emerald-600' }}">
                                    {{ $item['credit_utilization'] ?? 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-amber-200 rounded-full h-2 overflow-hidden mb-1">
                                <div class="h-full rounded-full {{ ($item['credit_utilization'] ?? 0) > 70 ? 'bg-red-400' : 'bg-amber-400' }}"
                                    style="width: {{ min(100, $item['credit_utilization'] ?? 0) }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-amber-600">
                                <span>Owed: <strong>Rp {{ number_format($exp->current_balance ?? 0, 0) }}</strong></span>
                                <span>Limit: <strong>Rp {{ number_format($exp->credit_limit, 0) }}</strong></span>
                                @if($exp->minimum_payment)
                                    <span>Min: <strong>Rp {{ number_format($exp->minimum_payment, 0) }}</strong></span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Payment Form -->
                        @if($exp->due_date || $exp->is_credit)
                        <div class="mb-3 p-3 rounded-2xl bg-gray-50 border border-gray-100">
                            <p class="text-xs font-bold text-gray-500 mb-2">
                                <i class="ph ph-credit-card mr-1"></i>Log a Payment
                            </p>
                            <form action="{{ route('expenses.payments.store', $exp) }}" method="POST" class="flex items-end gap-2 flex-wrap">
                                @csrf
                                <div>
                                    <label class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Amount</label>
                                    <input type="number" name="amount" placeholder="Rp 0" required min="0.01" step="0.01"
                                        class="w-36 border border-gray-200 p-2 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Date</label>
                                    <input type="date" name="payment_date" required
                                        value="{{ date('Y-m-d') }}"
                                        class="w-36 border border-gray-200 p-2 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Notes <span class="text-gray-300">(opt)</span></label>
                                    <input type="text" name="notes" placeholder="e.g. Full payment"
                                        class="w-32 border border-gray-200 p-2 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <button type="submit"
                                    class="bg-indigo-500 hover:bg-indigo-600 active:scale-95 text-white font-bold px-4 py-2 rounded-xl transition-all text-sm shadow-sm">
                                    <i class="ph ph-check mr-0.5"></i>Log Payment
                                </button>
                            </form>

                            <!-- Recent Payments -->
                            @if($item['recent_payments']->count() > 0)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Recent Payments</p>
                                <div class="space-y-1.5">
                                    @foreach($item['recent_payments'] as $payment)
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <i class="ph ph-check-circle text-emerald-500 text-sm"></i>
                                                <span class="text-gray-600 font-semibold">Rp {{ number_format($payment->amount, 0) }}</span>
                                                <span class="text-gray-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M j') }}</span>
                                                @if($payment->notes)
                                                    <span class="text-gray-300 italic">{{ $payment->notes }}</span>
                                                @endif
                                            </div>
                                            <form action="{{ route('expenses.payments.destroy', [$exp, $payment]) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Delete this payment?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-300 hover:text-red-500 transition ml-2">
                                                    <i class="ph ph-x text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-50">
                            <form action="{{ route('expenses.toggle', $exp) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-xl border transition
                                    {{ $exp->is_active ? 'border-red-200 text-red-400 hover:bg-red-50' : 'border-emerald-200 text-emerald-500 hover:bg-emerald-50' }}">
                                    <i class="ph {{ $exp->is_active ? 'ph-pause' : 'ph-play' }} mr-0.5"></i>
                                    {{ $exp->is_active ? 'Pause' : 'Resume' }}
                                </button>
                            </form>
                            <button type="button" onclick="openEditModal({{ $exp->id }}, '{{ addslashes($exp->name) }}', '{{ $exp->category }}', {{ $exp->allocated_amount }}, {{ json_encode($exp->keywords ?? []) }}, {{ $exp->rollover ? 'true' : 'false' }}, {{ $exp->due_date ?? 'null' }}, {{ $exp->is_credit ? 'true' : 'false' }}, {{ $exp->credit_limit ?? 'null' }}, {{ $exp->current_balance ?? 'null' }}, {{ $exp->minimum_payment ?? 'null' }}, {{ $exp->reminder_days ?? 3 }}, '{{ $exp->payment_frequency ?? 'monthly' }}')"
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
        <div class="modal-box max-w-lg">
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
                        <input type="number" name="allocated_amount" id="editAmount" required min="0" step="0.01" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Due Date <span class="font-normal text-gray-300">(day 1-31)</span></label>
                        <input type="number" name="due_date" id="editDueDate" min="1" max="31" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Frequency</label>
                        <select name="payment_frequency" id="editFrequency" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="monthly">Monthly</option>
                            <option value="biweekly">Biweekly</option>
                            <option value="weekly">Weekly</option>
                            <option value="onetime">One-time</option>
                        </select>
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
                <div class="border-t border-gray-100 pt-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_credit" id="editIsCredit" class="rounded" onclick="toggleCreditFields('edit')">
                        <span class="text-xs font-bold text-gray-600"><i class="ph ph-credit-card mr-1"></i>This is a credit / loan</span>
                    </label>
                    <div id="editCreditFields" class="hidden mt-3 grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Credit Limit</label>
                            <input type="number" name="credit_limit" id="editCreditLimit" min="0" step="0.01" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Current Balance</label>
                            <input type="number" name="current_balance" id="editCurrentBalance" min="0" step="0.01" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Min Payment</label>
                            <input type="number" name="minimum_payment" id="editMinimumPayment" min="0" step="0.01" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Remind (days before)</label>
                            <input type="number" name="reminder_days" id="editReminderDays" min="1" max="30" value="3" class="w-full border border-gray-200 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-400 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-500 text-white text-sm font-bold hover:bg-indigo-600 active:scale-95 transition shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(id, name, category, amount, keywords, rollover, dueDate, isCredit, creditLimit, currentBalance, minPayment, reminderDays, frequency) {
        document.getElementById('editForm').action = '/expenses/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editCategory').value = category;
        // Use String() to avoid precision loss with large floats
        document.getElementById('editAmount').value = amount !== null && amount !== undefined ? String(amount) : '';
        document.getElementById('editKeywords').value = (keywords || []).join(', ');
        document.getElementById('editRollover').checked = !!rollover;
        document.getElementById('editDueDate').value = dueDate !== null && dueDate !== undefined ? String(dueDate) : '';
        document.getElementById('editIsCredit').checked = !!isCredit;
        document.getElementById('editCreditLimit').value = creditLimit !== null && creditLimit !== undefined ? String(creditLimit) : '';
        document.getElementById('editCurrentBalance').value = currentBalance !== null && currentBalance !== undefined ? String(currentBalance) : '';
        document.getElementById('editMinimumPayment').value = minPayment !== null && minPayment !== undefined ? String(minPayment) : '';
        document.getElementById('editReminderDays').value = reminderDays !== null && reminderDays !== undefined ? String(reminderDays) : '3';
        // Ensure frequency is a valid option
        const validFreqs = ['monthly', 'biweekly', 'weekly', 'onetime'];
        document.getElementById('editFrequency').value = (frequency && validFreqs.includes(frequency)) ? frequency : 'monthly';
        toggleCreditFields('edit');
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
    function toggleCreditFields(prefix) {
        const checkbox = document.getElementById(prefix + 'IsCredit');
        const fields = document.getElementById(prefix + 'CreditFields');
        if (checkbox && fields) {
            fields.classList.toggle('hidden', !checkbox.checked);
        }
    }
    </script>
</body>
</html>
