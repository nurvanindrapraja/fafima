<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">Selamat datang, {{ Auth::user()->name }}! 👋</p>
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="flex-1 sm:flex-none">
                    <input type="month" value="{{ sprintf('%04d-%02d', request('year', now()->year), request('month', now()->month)) }}" 
                        class="input-dark text-sm py-1.5 px-3 w-full" 
                        onchange="if(this.value) { const [y, m] = this.value.split('-'); window.location.href = '?year=' + y + '&month=' + m; }">
                </div>
                <div class="text-right hidden sm:block border-l border-slate-700/50 pl-4">
                    <p class="text-xs text-slate-500">Keluarga</p>
                    <p class="text-sm font-semibold text-blue-300">{{ Auth::user()->family?->name }}</p>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">Kode: <span class="text-blue-400">{{ Auth::user()->family?->code }}</span></p>
                </div>
            </div>
        </div>

        {{-- Smart Advisor --}}
        <livewire:smart-advisor />

        {{-- Quick Stats Row --}}
        @php
            $familyId = Auth::user()->family_id;
            $month = (int) request('month', now()->month);
            $year = (int) request('year', now()->year);
            $periodText = ($month == now()->month && $year == now()->year) ? 'Bulan ini' : \Carbon\Carbon::create()->day(1)->month($month)->year($year)->translatedFormat('F Y');
            $income  = \App\Models\Transaction::where('family_id', $familyId)->where('type','income')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $expense = \App\Models\Transaction::where('family_id', $familyId)->where('type','expense')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $balance = $income - $expense;
            $txCount = \App\Models\Transaction::where('family_id', $familyId)->whereMonth('date', $month)->whereYear('date', $year)->count();
            
            // Limits
            $familyLimit = \App\Models\Limit::where('family_id', $familyId)->whereNull('user_id')->first();
            $myLimit = \App\Models\Limit::where('family_id', $familyId)->where('user_id', Auth::id())->first();
            
            $familyLimitValue = $familyLimit ? ($familyLimit->limit_type === 'fixed' ? $familyLimit->amount : ($income * $familyLimit->percentage / 100)) : 0;
            $myLimitValue = $myLimit ? ($myLimit->limit_type === 'fixed' ? $myLimit->amount : ($income * $myLimit->percentage / 100)) : 0;
            
            $myExpense = \App\Models\Transaction::where('family_id', $familyId)->where('user_id', Auth::id())->where('type','expense')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');

            // Charts Data
            $expensesByCategory = \App\Models\Transaction::with('category')
                ->where('family_id', $familyId)
                ->where('type', 'expense')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->groupBy('category_id')
                ->map(function ($transactions) {
                    return [
                        'name' => $transactions->first()->category->name ?? 'Lainnya',
                        'total' => $transactions->sum('amount')
                    ];
                })->values();
                
            $incomeByCategory = \App\Models\Transaction::with('category')
                ->where('family_id', $familyId)
                ->where('type', 'income')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->groupBy('category_id')
                ->map(function ($transactions) {
                    return [
                        'name' => $transactions->first()->category->name ?? 'Lainnya',
                        'total' => $transactions->sum('amount')
                    ];
                })->values();

            $allExpensesThisYear = \App\Models\Transaction::where('family_id', $familyId)
                ->where('type', 'expense')
                ->whereYear('date', $year)
                ->get();
            
            $dailyData = $allExpensesThisYear->where(function($item) use ($month) {
                return \Carbon\Carbon::parse($item->date)->month == $month;
            })->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->date)->format('d M');
            })->map->sum('amount');
            // Sort by key as date is not easy when format is 'd M' if across months, but it's only 1 month.
            // Let's use format('Y-m-d') for grouping, then map to 'd M' for labels.
            $dailyData = $allExpensesThisYear->where(function($item) use ($month) {
                return \Carbon\Carbon::parse($item->date)->month == $month;
            })->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            })->map->sum('amount')->sortKeys();
            
            $weeklyData = $allExpensesThisYear->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->date)->startOfWeek()->format('Y-m-d');
            })->map->sum('amount')->sortKeys();
            
            $monthlyData = $allExpensesThisYear->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m');
            })->map->sum('amount')->sortKeys();
            
            $timeseriesData = [
                'daily' => [
                    'labels' => collect($dailyData->keys())->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
                    'series' => $dailyData->values()->toArray(),
                ],
                'weekly' => [
                    'labels' => collect($weeklyData->keys())->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
                    'series' => $weeklyData->values()->toArray(),
                ],
                'monthly' => [
                    'labels' => collect($monthlyData->keys())->map(fn($d) => \Carbon\Carbon::parse($d)->format('M Y'))->toArray(),
                    'series' => $monthlyData->values()->toArray(),
                ]
            ];
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card-glass p-5 rounded-2xl border border-blue-500/20 glow-blue">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Saldo Bersih</span>
                </div>
                <p class="text-xl font-bold {{ $balance >= 0 ? 'text-blue-300' : 'text-rose-400' }}">
                    {{ $balance >= 0 ? '' : '-' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 mt-1">{{ $periodText }}</p>
            </div>

            <div class="card-glass p-5 rounded-2xl border border-emerald-500/20">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                    </div>
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Pemasukan</span>
                </div>
                <p class="text-xl font-bold text-emerald-400">Rp {{ number_format($income, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $periodText }}</p>
            </div>

            <div class="card-glass p-5 rounded-2xl border border-rose-500/20">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-600/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                    </div>
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Pengeluaran</span>
                </div>
                <p class="text-xl font-bold text-rose-400">Rp {{ number_format($expense, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $periodText }}</p>
            </div>

            <div class="card-glass p-5 rounded-2xl border border-violet-500/20">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-violet-600/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Transaksi</span>
                </div>
                <p class="text-xl font-bold text-violet-300">{{ $txCount }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $periodText }}</p>
            </div>
        </div>

        {{-- Limit Monitoring --}}
        @if($familyLimitValue > 0 || $myLimitValue > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($familyLimitValue > 0)
            @php
                $familyPct = min(100, ($expense / $familyLimitValue) * 100);
                if ($familyPct >= 100) {
                    $familyStatus = 'Melebihi Limit';
                    $familyBadge = 'bg-rose-500/20 text-rose-400 border-rose-500/30';
                    $familyBar = 'from-rose-500 to-rose-400';
                } elseif ($familyPct >= 90) {
                    $familyStatus = 'Hampir Melebihi';
                    $familyBadge = 'bg-orange-500/20 text-orange-400 border-orange-500/30';
                    $familyBar = 'from-orange-500 to-orange-400';
                } elseif ($familyPct >= 80) {
                    $familyStatus = 'Waspada';
                    $familyBadge = 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
                    $familyBar = 'from-yellow-500 to-yellow-400';
                } else {
                    $familyStatus = 'Aman';
                    $familyBadge = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
                    $familyBar = 'from-emerald-500 to-emerald-400';
                }
            @endphp
            <div class="card-glass rounded-2xl border border-slate-700/50 p-5">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-semibold text-white">Limit Pengeluaran Keluarga</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $familyBadge }}">
                        {{ $familyStatus }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                    <span class="font-medium text-white">Rp {{ number_format($expense, 0, ',', '.') }}</span>
                    <span>{{ number_format($familyPct, 1) }}%</span>
                </div>
                <div class="w-full h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 bg-gradient-to-r {{ $familyBar }}"
                        style="width: {{ $familyPct }}%"></div>
                </div>
                <p class="text-xs text-slate-500 mt-2">Batas Limit: Rp {{ number_format($familyLimitValue, 0, ',', '.') }}</p>
            </div>
            @endif

            @if($myLimitValue > 0)
            @php
                $myPct = min(100, ($myExpense / $myLimitValue) * 100);
                if ($myPct >= 100) {
                    $myStatus = 'Melebihi Limit';
                    $myBadge = 'bg-rose-500/20 text-rose-400 border-rose-500/30';
                    $myBar = 'from-rose-500 to-rose-400';
                } elseif ($myPct >= 90) {
                    $myStatus = 'Hampir Melebihi';
                    $myBadge = 'bg-orange-500/20 text-orange-400 border-orange-500/30';
                    $myBar = 'from-orange-500 to-orange-400';
                } elseif ($myPct >= 80) {
                    $myStatus = 'Waspada';
                    $myBadge = 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
                    $myBar = 'from-yellow-500 to-yellow-400';
                } else {
                    $myStatus = 'Aman';
                    $myBadge = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
                    $myBar = 'from-emerald-500 to-emerald-400';
                }
            @endphp
            <div class="card-glass rounded-2xl border border-slate-700/50 p-5">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="font-semibold text-white">Limit Pengeluaran Saya</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $myBadge }}">
                        {{ $myStatus }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                    <span class="font-medium text-white">Rp {{ number_format($myExpense, 0, ',', '.') }}</span>
                    <span>{{ number_format($myPct, 1) }}%</span>
                </div>
                <div class="w-full h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 bg-gradient-to-r {{ $myBar }}"
                        style="width: {{ $myPct }}%"></div>
                </div>
                <p class="text-xs text-slate-500 mt-2">Batas Limit: Rp {{ number_format($myLimitValue, 0, ',', '.') }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="card-glass rounded-2xl border border-slate-700/50 p-5">
                <h2 class="font-semibold text-white mb-4 text-center">Proporsi Pengeluaran ({{ $periodText }})</h2>
                <div id="chart-expense-pie" class="flex justify-center min-h-[300px]"></div>
            </div>
            <div class="card-glass rounded-2xl border border-slate-700/50 p-5">
                <h2 class="font-semibold text-white mb-4 text-center">Proporsi Pemasukan ({{ $periodText }})</h2>
                <div id="chart-income-pie" class="flex justify-center min-h-[300px]"></div>
            </div>
            
            <div class="card-glass rounded-2xl border border-slate-700/50 p-5 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                     <h2 class="font-semibold text-white">Tren Pengeluaran</h2>
                     <select id="timeseries-filter" class="input-dark text-sm !w-auto py-1 px-3" style="width: auto;">
                         <option value="daily">Harian ({{ $periodText }})</option>
                         <option value="weekly">Mingguan (Tahun Ini)</option>
                         <option value="monthly">Bulanan (Tahun Ini)</option>
                     </select>
                 </div>
                 <div id="chart-timeseries" class="min-h-[350px]"></div>
            </div>
        </div>

        {{-- Recent Transactions (last 5) --}}
        @php
            $recentTx = \App\Models\Transaction::with(['category','user'])
                ->where('family_id', $familyId)
                ->orderByDesc('date')->orderByDesc('id')
                ->limit(5)->get();
        @endphp

        <div class="card-glass rounded-2xl border border-slate-700/50 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700/40">
                <h2 class="font-semibold text-white">Transaksi Terbaru</h2>
                <a href="{{ route('transactions.index') }}" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">Lihat semua →</a>
            </div>
            @if($recentTx->isEmpty())
                <div class="text-center py-10 text-slate-500 text-sm">Belum ada transaksi {{ strtolower($periodText) }}.</div>
            @else
                <div class="divide-y divide-slate-700/30">
                    @foreach($recentTx as $tx)
                    <div class="flex items-center gap-4 px-6 py-3 hover:bg-slate-700/10 transition-colors">
                        <div class="w-8 h-8 rounded-lg {{ $tx->type === 'income' ? 'bg-emerald-600/20' : 'bg-rose-600/20' }} flex items-center justify-center flex-shrink-0">
                            @if($tx->type === 'income')
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                            @else
                                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-200 truncate">{{ $tx->description ?? $tx->category?->name ?? ($tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}</p>
                            <p class="text-xs text-slate-500">{{ $tx->date->format('d M Y') }} · {{ $tx->user->name }}</p>
                        </div>
                        <p class="text-sm font-semibold {{ $tx->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }} flex-shrink-0">
                            {{ $tx->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Family Code Card --}}
        <div class="card-glass rounded-2xl border border-blue-500/20 p-5">
            <h2 class="font-semibold text-white mb-3">Undang Anggota Keluarga</h2>
            <p class="text-sm text-slate-400 mb-3">Bagikan kode berikut kepada anggota keluarga Anda agar mereka bisa bergabung:</p>
            <div class="flex items-center gap-3">
                <code class="text-2xl font-mono font-bold tracking-[0.3em] text-blue-300 bg-blue-900/30 px-5 py-2 rounded-xl border border-blue-500/30">
                    {{ Auth::user()->family?->code }}
                </code>
                <button onclick="navigator.clipboard.writeText('{{ Auth::user()->family?->code }}').then(() => { this.textContent = '✓ Disalin!'; setTimeout(()=>{ this.textContent='Salin Kode'; }, 2000) })"
                    class="btn-primary px-4 py-2 rounded-xl text-sm font-medium" id="btn-salin-kode">
                    Salin Kode
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const formatCurrency = (value) => {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        };

        const expensesPieData = @json($expensesByCategory);
        const incomePieData = @json($incomeByCategory);
        const tsData = @json($timeseriesData);

        const getPieOptions = (data, isExpense) => ({
            series: data.length > 0 ? data.map(item => item.total) : [1],
            labels: data.length > 0 ? data.map(item => item.name) : ['Tidak ada data'],
            chart: {
                type: 'donut',
                height: 320,
                background: 'transparent'
            },
            theme: { mode: 'dark' },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true, color: '#94a3b8' },
                            value: { 
                                show: true, 
                                color: isExpense ? '#f43f5e' : '#34d399',
                                formatter: function (val) {
                                    if(data.length === 0) return formatCurrency(0);
                                    return formatCurrency(val)
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: { formatter: function(val) { 
                    if(data.length === 0) return formatCurrency(0);
                    return formatCurrency(val) 
                } }
            },
            stroke: { show: true, colors: ['rgba(15, 23, 42, 0.6)'] },
            legend: { position: 'bottom' },
            colors: data.length === 0 ? ['#334155'] : undefined
        });

        if (document.querySelector("#chart-expense-pie")) {
            new ApexCharts(document.querySelector("#chart-expense-pie"), getPieOptions(expensesPieData, true)).render();
        }
        if (document.querySelector("#chart-income-pie")) {
            new ApexCharts(document.querySelector("#chart-income-pie"), getPieOptions(incomePieData, false)).render();
        }

        let currentFilter = 'daily';
        const tsOptions = {
            series: [{
                name: 'Pengeluaran',
                data: tsData[currentFilter].series
            }],
            chart: {
                type: 'area',
                height: 350,
                background: 'transparent',
                toolbar: { show: false }
            },
            theme: { mode: 'dark' },
            colors: ['#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: tsData[currentFilter].labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8' } }
            },
            yaxis: {
                labels: { 
                    formatter: (val) => 'Rp ' + (val/1000).toFixed(0) + 'k',
                    style: { colors: '#94a3b8' } 
                }
            },
            tooltip: {
                theme: 'dark',
                y: { formatter: (val) => formatCurrency(val) }
            },
            grid: {
                borderColor: 'rgba(100, 116, 139, 0.2)',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } }
            }
        };

        let tsChart = null;
        if (document.querySelector("#chart-timeseries")) {
            tsChart = new ApexCharts(document.querySelector("#chart-timeseries"), tsOptions);
            tsChart.render();
        }

        const filterEl = document.getElementById('timeseries-filter');
        if (filterEl && tsChart) {
            filterEl.addEventListener('change', function(e) {
                currentFilter = e.target.value;
                tsChart.updateOptions({
                    xaxis: { categories: tsData[currentFilter].labels }
                });
                tsChart.updateSeries([{
                    name: 'Pengeluaran',
                    data: tsData[currentFilter].series
                }]);
            });
        }
    });
    </script>
</x-app-layout>
