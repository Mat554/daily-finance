<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import 'tailwindcss';
        .dist-cat-btn {
            border: 2px solid #e5e7eb;
            color: #9ca3af;
            transition: all 0.2s;
            cursor: pointer;
        }
        .dist-cat-btn.active {
            border-style: solid;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-4">

    <div class="max-w-md mx-auto bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-black">Edit Transaction</h1>
                    <p class="text-xs text-blue-200 mt-0.5">
                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j, Y') }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                    <i class="ph ph-pencil-simple text-white text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('update', $transaction->id) }}" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="ph ph-calendar mr-1"></i>Date
                </label>
                <input type="date" name="transaction_date" value="{{ $transaction->transaction_date }}"
                    class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="ph ph-pencil-simple mr-1"></i>Description
                </label>
                <input type="text" name="description" value="{{ $transaction->description }}"
                    class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="ph ph-coins mr-1"></i>Amount (Rp)
                </label>
                <input type="number" name="amount" value="{{ $transaction->amount }}"
                    class="w-full border border-gray-100 p-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50" required>
            </div>

            <!-- Type toggle -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    <i class="ph ph-arrows-vertical mr-1"></i>Type
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="submit" name="type" value="in"
                        class="py-3 rounded-2xl font-bold text-sm transition-all {{ $transaction->type == 'in' ? 'bg-green-500 text-white shadow-green-200 shadow-md' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                        <i class="ph ph-arrow-circle-down mr-1"></i>Money In
                    </button>
                    <button type="submit" name="type" value="out"
                        class="py-3 rounded-2xl font-bold text-sm transition-all {{ $transaction->type == 'out' ? 'bg-red-500 text-white shadow-red-200 shadow-md' : 'bg-red-50 text-red-600 hover:bg-red-100' }}">
                        <i class="ph ph-arrow-circle-up mr-1"></i>Money Out
                    </button>
                </div>
            </div>

            <!-- Income Split Section -->
            @if($transaction->type === 'in')
            <div id="splitSection" class="border border-emerald-200 rounded-2xl p-4 bg-emerald-50/50">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-700 flex items-center gap-1">
                        <i class="ph ph-scales text-emerald-500"></i>Save vs Spend Split
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="isSplitToggle" name="is_split" value="1"
                            {{ $transaction->is_split ? 'checked' : '' }}
                            class="w-4 h-4 rounded accent-emerald-500"
                            onchange="toggleSplitSection(this.checked)">
                        <span class="text-xs font-semibold text-gray-500">Enabled</span>
                    </label>
                </div>

                <div id="splitFields" class="{{ $transaction->is_split ? '' : 'hidden' }}">
                    <!-- Slider -->
                    <div class="mb-3">
                        <div class="flex justify-between text-xs font-bold text-gray-400 mb-2">
                            <span>💰 SAVE</span>
                            <span>🛒 SPEND</span>
                        </div>
                        <div class="relative h-4 bg-gray-100 rounded-full">
                            <div id="editSliderFill" class="absolute left-0 top-0 h-full rounded-full transition-all duration-75"
                                style="width:{{ $transaction->save_pct ?? 50 }}%; background: linear-gradient(to right, #22c55e, #3b82f6);"></div>
                            <input type="range" id="editSplitSlider" name="save_pct"
                                min="0" max="100" value="{{ $transaction->save_pct ?? 50 }}"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                oninput="updateEditSplit(this.value)">
                        </div>
                        <div class="flex justify-between text-xs font-bold text-gray-400 mt-1">
                            <span id="editSavePctLabel" class="text-emerald-600">{{ $transaction->save_pct ?? 50 }}%</span>
                            <span id="editSpendPctLabel" class="text-blue-600">{{ 100 - ($transaction->save_pct ?? 50) }}%</span>
                        </div>
                    </div>

                    <!-- Amounts preview -->
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-emerald-100 rounded-xl p-2.5 text-center">
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Saved</p>
                            <p id="editSaveAmtLabel" class="text-sm font-black text-emerald-700">
                                Rp {{ number_format($transaction->saved_amount ?? ($transaction->amount * ($transaction->save_pct ?? 50) / 100), 0) }}
                            </p>
                        </div>
                        <div class="bg-blue-100 rounded-xl p-2.5 text-center">
                            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Allocated</p>
                            <p id="editSpendAmtLabel" class="text-sm font-black text-blue-700">
                                Rp {{ number_format($transaction->spent_amount ?? ($transaction->amount * (100 - ($transaction->save_pct ?? 50)) / 100), 0) }}
                            </p>
                        </div>
                    </div>

                    <!-- Savings Distribution -->
                    <div class="border-t border-emerald-200 pt-3 mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1">
                                <i class="ph ph-vault"></i> Savings Distribution
                            </p>
                            <span class="text-[10px] text-gray-400">optional</span>
                        </div>

                        @php
                            $savedAmt = $transaction->saved_amount ?? 0;
                            $dist = $transaction->savings_distribution;
                            if (is_string($dist)) { $dist = json_decode($dist, true); }
                            $dist = $dist ?? [];

                            $DIST_CATS = ['Emergency Fund' => '#22c55e', 'Investment' => '#3b82f6', 'Goals' => '#f59e0b', 'Buffer' => '#a78bfa'];
                            $DIST_ICONS = ['Emergency Fund' => '🛡️', 'Investment' => '📈', 'Goals' => '🎯', 'Buffer' => '💼'];

                            $distPcts = [];
                            foreach ($DIST_CATS as $cat => $color) {
                                $distPcts[$cat] = ['pct' => 0, 'amount' => 0];
                            }
                            foreach ($dist as $item) {
                                $cat = $item['category'] ?? '';
                                if (isset($distPcts[$cat])) {
                                    $distPcts[$cat] = ['pct' => (int)($item['pct'] ?? 0), 'amount' => (float)($item['amount'] ?? 0)];
                                }
                            }
                            $totalDistPct = array_sum(array_column($distPcts, 'pct'));
                        @endphp

                        <!-- Category toggles -->
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            @foreach($DIST_CATS as $cat => $color)
                                @php $isActive = $distPcts[$cat]['pct'] > 0; @endphp
                                <button type="button"
                                    class="dist-cat-btn px-2.5 py-1 rounded-xl text-[11px] font-bold active"
                                    style="border-color: {{ $color }}; color: {{ $color }}; background: {{ $isActive ? $color.'20' : 'transparent' }};"
                                    data-cat="{{ $cat }}"
                                    onclick="toggleEditDistCat(this, '{{ $cat }}')">
                                    {{ $DIST_ICONS[$cat] }} {{ $cat }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Sliders -->
                        <div id="editDistSliders" class="space-y-2 mb-3">
                            @foreach($DIST_CATS as $cat => $color)
                                @php
                                    $pct = $distPcts[$cat]['pct'];
                                    $amt = $distPcts[$cat]['amount'];
                                @endphp
                                <div class="edit-dist-row flex items-center gap-2" data-cat="{{ $cat }}" style="{{ $pct > 0 ? '' : 'display:none' }}">
                                    <span class="text-xs font-bold w-24 text-gray-600">{{ $cat }}</span>
                                    <input type="range" name="dist_pct[{{ $cat }}]" min="0" max="100" value="{{ $pct }}"
                                        class="flex-1 h-1.5 rounded-full cursor-pointer"
                                        style="accent-color:{{ $color }}"
                                        oninput="updateEditDistPct('{{ $cat }}', this.value, '{{ $color }}')">
                                    <span class="text-xs font-bold text-gray-500 w-7 text-right">{{ $pct }}%</span>
                                    <span class="text-xs font-bold w-24 text-right" style="color:{{ $color }}">Rp {{ number_format($amt, 0) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Bar preview -->
                        <div class="mb-2">
                            <div class="flex gap-1 h-2.5 rounded-full overflow-hidden bg-gray-100">
                                @foreach($DIST_CATS as $cat => $color)
                                    @php $pct = $distPcts[$cat]['pct']; @endphp
                                    @if($pct > 0)
                                        <div style="width:{{ $pct }}%;background:{{ $color }}" class="h-full transition-all"></div>
                                    @endif
                                @endforeach
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 text-right">{{ $totalDistPct }}% / 100%</p>
                        </div>

                        <!-- Hidden field for distribution JSON -->
                        <input type="hidden" name="savings_distribution_json" id="editDistJson"
                            value="{{ $transaction->savings_distribution ?? '[]' }}">
                    </div>
                </div>
            </div>
            @endif

            <!-- Expense Category Section -->
            @if($transaction->type === 'out')
            <div class="border border-pink-200 rounded-2xl p-4 bg-pink-50/50">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-1">
                    <i class="ph ph-tag text-pink-500"></i>Expense Category
                </h3>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" id="btnNeed"
                        class="nw-btn need py-3 rounded-2xl text-sm font-bold transition-all {{ $transaction->need_or_want === 'need' ? 'border-emerald-500 bg-emerald-100' : 'border-transparent bg-white' }}"
                        onclick="selectEditNeedWant('need')">
                        🏠 Need
                    </button>
                    <button type="button" id="btnWant"
                        class="nw-btn want py-3 rounded-2xl text-sm font-bold transition-all {{ $transaction->need_or_want === 'want' ? 'border-pink-500 bg-pink-100' : 'border-transparent bg-white' }}"
                        onclick="selectEditNeedWant('want')">
                        🎉 Want
                    </button>
                </div>
                <input type="hidden" name="need_or_want" id="editNeedOrWant" value="{{ $transaction->need_or_want ?? '' }}">
            </div>
            @endif

            <!-- Save button -->
            <button type="submit"
                class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-blue-200 hover:shadow-blue-300 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] transition-all">
                <i class="ph ph-check-circle mr-1"></i>Save Changes
            </button>
        </form>

        <!-- Delete -->
        <form action="/transaction/{{ $transaction->id }}" method="POST" class="px-5 pb-5">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="w-full py-2.5 rounded-2xl border-2 border-red-200 text-red-400 font-bold text-sm hover:bg-red-50 transition"
                onclick="return confirm('Delete this transaction? This cannot be undone.')">
                <i class="ph ph-trash mr-1"></i>Delete Transaction
            </button>
        </form>

        <!-- Back link -->
        <div class="px-5 pb-5 text-center">
            @if(strtolower(session('username', '')) === 'matius')
                <a href="/dashboard" class="text-xs text-gray-400 hover:text-gray-600 transition">← Back to Dashboard</a>
            @else
                <a href="/" class="text-xs text-gray-400 hover:text-gray-600 transition">← Back to Tracker</a>
            @endif
        </div>
    </div>

    <script>
        const editAmount = {{ $transaction->amount }};
        const editSavedAmt = {{ $transaction->saved_amount ?? 0 }};

        function toggleSplitSection(enabled) {
            document.getElementById('splitFields').classList.toggle('hidden', !enabled);
        }

        function updateEditSplit(pct) {
            pct = parseInt(pct);
            document.getElementById('editSliderFill').style.width = pct + '%';
            document.getElementById('editSavePctLabel').textContent = pct + '%';
            document.getElementById('editSpendPctLabel').textContent = (100 - pct) + '%';
            const savedAmt = Math.round(editAmount * pct / 100);
            document.getElementById('editSaveAmtLabel').textContent = 'Rp ' + savedAmt.toLocaleString('id-ID');
            document.getElementById('editSpendAmtLabel').textContent = 'Rp ' + (editAmount - savedAmt).toLocaleString('id-ID');
            // Update distribution amounts too
            document.querySelectorAll('.edit-dist-row').forEach(row => {
                const cat = row.dataset.cat;
                const pctInput = row.querySelector('input[type=range]');
                const amtLabel = row.querySelectorAll('span')[1];
                amtLabel.textContent = 'Rp ' + Math.round(savedAmt * parseInt(pctInput.value) / 100).toLocaleString('id-ID');
            });
            buildEditDistJson();
        }

        function toggleEditDistCat(btn, catName) {
            const isActive = btn.classList.contains('active');
            const color = {
                'Emergency Fund': '#22c55e',
                'Investment': '#3b82f6',
                'Goals': '#f59e0b',
                'Buffer': '#a78bfa',
            }[catName];
            if (isActive) {
                btn.classList.remove('active');
                btn.style.background = 'transparent';
                btn.style.color = '#9ca3af';
                btn.style.borderColor = '#e5e7eb';
                const row = document.querySelector(`.edit-dist-row[data-cat="${catName}"]`);
                if (row) row.style.display = 'none';
                const slider = row.querySelector('input[type=range]');
                if (slider) slider.value = 0;
            } else {
                btn.classList.add('active');
                btn.style.background = color + '20';
                btn.style.color = color;
                btn.style.borderColor = color;
                const row = document.querySelector(`.edit-dist-row[data-cat="${catName}"]`);
                if (row) {
                    row.style.display = '';
                    const slider = row.querySelector('input[type=range]');
                    if (slider && parseInt(slider.value) === 0) slider.value = 25;
                }
            }
            updateEditDistBar();
            buildEditDistJson();
        }

        function updateEditDistPct(catName, val, color) {
            const savedAmt = editSavedAmt || editAmount * (parseInt(document.getElementById('editSplitSlider')?.value || 50)) / 100;
            const row = document.querySelector(`.edit-dist-row[data-cat="${catName}"]`);
            const pctLabel = row.querySelectorAll('span')[0];
            const amtLabel = row.querySelectorAll('span')[1];
            pctLabel.textContent = val + '%';
            amtLabel.textContent = 'Rp ' + Math.round(savedAmt * val / 100).toLocaleString('id-ID');
            updateEditDistBar();
            buildEditDistJson();
        }

        function updateEditDistBar() {
            const totalPct = Array.from(document.querySelectorAll('.edit-dist-row'))
                .map(row => parseInt(row.querySelector('input[type=range]').value))
                .reduce((a, b) => a + b, 0);
            const bar = document.querySelector('.flex.gap-1.h-2\\.5.rounded-full') || document.querySelector('.flex.gap-1.h-2\\.5');
            const pctText = document.querySelector('.text-\\[10px\\].text-gray-400.mt-1.text-right');
            if (pctText) pctText.textContent = totalPct + '% / 100%';
        }

        function buildEditDistJson() {
            const dist = [];
            const savedAmt = editSavedAmt || editAmount * (parseInt(document.getElementById('editSplitSlider')?.value || 50)) / 100;
            document.querySelectorAll('.edit-dist-row').forEach(row => {
                const cat = row.dataset.cat;
                const pct = parseInt(row.querySelector('input[type=range]').value);
                if (pct > 0) {
                    dist.push({ category: cat, pct: pct, amount: Math.round(savedAmt * pct / 100) });
                }
            });
            document.getElementById('editDistJson').value = JSON.stringify(dist);
        }

        function selectEditNeedWant(value) {
            document.getElementById('editNeedOrWant').value = value;
            document.getElementById('btnNeed').classList.toggle('border-emerald-500', value === 'need');
            document.getElementById('btnNeed').classList.toggle('bg-emerald-100', value === 'need');
            document.getElementById('btnNeed').classList.toggle('border-transparent', value !== 'need');
            document.getElementById('btnNeed').classList.toggle('bg-white', value !== 'need');
            document.getElementById('btnWant').classList.toggle('border-pink-500', value === 'want');
            document.getElementById('btnWant').classList.toggle('bg-pink-100', value === 'want');
            document.getElementById('btnWant').classList.toggle('border-transparent', value !== 'want');
            document.getElementById('btnWant').classList.toggle('bg-white', value !== 'want');
        }

        // Initialize
        buildEditDistJson();
    </script>

</body>
</html>
