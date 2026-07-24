<div class="space-y-6">

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div x-data x-init="setTimeout(() => $el.remove(), 4000)"
             class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('info'))
        <div x-data x-init="setTimeout(() => $el.remove(), 4000)"
             class="bg-blue-500/20 border border-blue-500/50 text-blue-300 px-4 py-3 rounded-xl text-sm">
            {{ session('info') }}
        </div>
    @endif

    {{-- My Pending Approvals Banner --}}
    @if($myPendingApprovals->count() > 0)
    <div class="bg-yellow-500/10 border border-yellow-500/40 rounded-2xl p-4">
        <h3 class="text-yellow-300 font-semibold text-sm mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Persetujuan Target Menunggu Anda ({{ $myPendingApprovals->count() }})
        </h3>
        <div class="space-y-2">
            @foreach($myPendingApprovals as $approval)
            <div class="flex items-center justify-between bg-slate-800/50 rounded-xl px-4 py-2.5">
                <div>
                    <p class="text-sm font-medium text-white">{{ $approval->target->name }}</p>
                    <p class="text-xs text-slate-400">Target: Rp {{ number_format($approval->target->target_amount, 0, ',', '.') }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="approve({{ $approval->target_id }})" wire:loading.attr="disabled"
                        class="px-3 py-1.5 rounded-lg bg-emerald-600/30 text-emerald-300 hover:bg-emerald-600/50 transition-colors text-xs font-medium flex items-center gap-1">
                        <span wire:loading.remove wire:target="approve({{ $approval->target_id }})">✓ Setuju</span>
                        <span wire:loading wire:target="approve({{ $approval->target_id }})">...</span>
                    </button>
                    <button wire:click="reject({{ $approval->target_id }})" wire:loading.attr="disabled"
                        class="px-3 py-1.5 rounded-lg bg-rose-600/30 text-rose-300 hover:bg-rose-600/50 transition-colors text-xs font-medium flex items-center gap-1">
                        <span wire:loading.remove wire:target="reject({{ $approval->target_id }})">✗ Tolak</span>
                        <span wire:loading wire:target="reject({{ $approval->target_id }})">...</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div></div>
        @if(Auth::user()->role === 'owner')
        <button wire:click="openForm" wire:loading.attr="disabled"
            class="btn-primary flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm disabled:opacity-50"
            id="btn-tambah-target">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Buat Target Baru
        </button>
        @endif
    </div>

    {{-- Loading Bar --}}
    <div wire:loading wire:target="save, deleteTarget, approve, reject, bypass" class="w-full h-1 bg-slate-700 rounded overflow-hidden">
        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 animate-pulse rounded"></div>
    </div>

    {{-- Create Target Form Modal --}}
    @if ($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-white">Buat Target Keuangan Baru</h3>
                <button wire:click="resetForm" class="text-slate-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            @error('form') <div class="mb-4 text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 px-4 py-2 rounded-lg">{{ $message }}</div> @enderror

            <div class="space-y-4">
                <div>
                    <label for="target-name" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Nama Target</label>
                    <input id="target-name" wire:model="name" type="text" placeholder="Cth: Umroh, Liburan, Dana Darurat..."
                        class="input-dark w-full" wire:loading.attr="disabled">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="target-amount" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Jumlah Target (Rp)</label>
                    <input id="target-amount" x-data="currencyInput(@entangle('target_amount'))" x-model="display" @input="updateValue" type="text" placeholder="0"
                        class="input-dark w-full text-lg font-semibold" wire:loading.attr="disabled">
                    @error('target_amount') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="target-date" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Tenggat Waktu (Opsional)</label>
                    <input id="target-date" wire:model="target_date" type="date"
                        class="input-dark w-full" wire:loading.attr="disabled">
                    @error('target_date') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">* Target akan aktif setelah disetujui semua anggota keluarga.</p>

            <div class="flex gap-3 mt-5">
                <button wire:click="resetForm" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="save" wire:loading.attr="disabled" id="btn-simpan-target"
                    class="flex-1 btn-primary py-2 rounded-xl text-sm font-semibold disabled:opacity-50 flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="save">Buat Target</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($deletingId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl text-center">
            <div class="w-12 h-12 bg-rose-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Hapus Target?</h3>
            <p class="text-slate-400 text-sm mb-6">Semua data terkait target ini akan dihapus permanen.</p>
            <div class="flex gap-3">
                <button wire:click="cancelDelete" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="deleteTarget" wire:loading.attr="disabled" id="btn-konfirmasi-hapus-target"
                    class="flex-1 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="deleteTarget">Hapus</span>
                    <span wire:loading wire:target="deleteTarget">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Targets Grid --}}
    @if ($targets->isEmpty())
        <div class="card-glass rounded-2xl border border-slate-700/50 text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            <p class="text-slate-500 text-sm">Belum ada target keuangan.</p>
            @if(Auth::user()->role === 'owner')
                <p class="text-slate-600 text-xs mt-1">Klik "Buat Target Baru" untuk memulai.</p>
            @endif
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($targets as $target)
        @php
            $pct = $target->target_amount > 0 ? min(100, ($target->current_amount / $target->target_amount) * 100) : 0;
            $approvedCount  = $target->approvals->where('status', 'approved')->count();
            $totalMembers   = $target->approvals->count();
            $pendingCount   = $target->approvals->where('status', 'pending')->count();
            $rejectedCount  = $target->approvals->where('status', 'rejected')->count();
            $statusConfig = match($target->status) {
                'active'           => ['text-blue-300', 'bg-blue-500/20', 'border-blue-500/30', 'Aktif'],
                'achieved'         => ['text-emerald-300', 'bg-emerald-500/20', 'border-emerald-500/30', 'Tercapai 🎉'],
                'pending_approval' => ['text-yellow-300', 'bg-yellow-500/20', 'border-yellow-500/30', 'Menunggu Persetujuan'],
                default            => ['text-rose-300', 'bg-rose-500/20', 'border-rose-500/30', 'Gagal'],
            };
        @endphp
        <div class="card-glass rounded-2xl border border-slate-700/40 p-5 space-y-4 group">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-white truncate">{{ $target->name }}</h3>
                    @if($target->target_date)
                    <p class="text-xs text-slate-500 mt-0.5">Tenggat: {{ $target->target_date->format('d M Y') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 ml-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusConfig[0] }} {{ $statusConfig[1] }} {{ $statusConfig[2] }} whitespace-nowrap">
                        {{ $statusConfig[3] }}
                    </span>
                    @if(Auth::user()->role === 'owner')
                    <button wire:click="confirmDelete({{ $target->id }})"
                        class="p-1 rounded-lg text-slate-600 hover:text-rose-400 hover:bg-rose-600/20 transition-colors opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Progress --}}
            <div>
                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                    <span class="font-medium text-white">Rp {{ number_format($target->current_amount, 0, ',', '.') }}</span>
                    <span>{{ number_format($pct, 1) }}%</span>
                </div>
                <div class="w-full h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 {{ $pct >= 100 ? 'bg-emerald-400' : 'bg-gradient-to-r from-blue-500 to-cyan-400' }}"
                        style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <p class="text-xs text-slate-500">Target: Rp {{ number_format($target->target_amount, 0, ',', '.') }}</p>
                    @if($target->status === 'active')
                    <button wire:click="openFundForm({{ $target->id }})" class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded-md transition-colors">
                        Isi Dana
                    </button>
                    @endif
                </div>
            </div>

            {{-- Approval Status --}}
            @if ($target->status === 'pending_approval' && $target->approvals->count() > 0)
            <div class="pt-2 border-t border-slate-700/40">
                <p class="text-xs text-slate-400 mb-2">Persetujuan: {{ $approvedCount }}/{{ $totalMembers }} anggota</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($target->approvals as $approval)
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium flex items-center gap-1
                        {{ $approval->status === 'approved' ? 'bg-emerald-500/20 text-emerald-300' : ($approval->status === 'rejected' ? 'bg-rose-500/20 text-rose-300' : ($approval->status === 'bypassed' ? 'bg-slate-500/20 text-slate-400' : 'bg-yellow-500/20 text-yellow-300')) }}">
                        {{ $approval->status === 'approved' ? '✓' : ($approval->status === 'rejected' ? '✗' : ($approval->status === 'bypassed' ? '⤵' : '⏳')) }}
                        {{ $approval->user->name }}
                    </span>
                    @endforeach
                </div>
                @if(Auth::user()->role === 'owner' && $pendingCount > 0)
                <button wire:click="bypass({{ $target->id }})" wire:loading.attr="disabled"
                    class="mt-2 text-xs text-slate-400 hover:text-yellow-400 underline transition-colors">
                    Bypass anggota pasif (min. 2 hari)
                </button>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
    
    {{-- Funding Form Modal --}}
    @if ($showFundForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-bold text-lg">Top Up Dana Target</h3>
                <button wire:click="closeFundForm" class="text-slate-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="space-y-4 mb-4">
                <div>
                    <label class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Jumlah Dana (Rp)</label>
                    <input x-data="currencyInput(@entangle('fund_amount'))" x-model="display" @input="updateValue" type="text" class="input-dark w-full text-lg font-semibold" placeholder="Contoh: 500000">
                    @error('fund_amount') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fund-description" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Keterangan (Opsional)</label>
                    <input id="fund-description" wire:model="fund_description" type="text" class="input-dark w-full text-sm" placeholder="Contoh: Tabungan gaji bulan Juli">
                    @error('fund_description') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <p class="text-[10px] text-slate-500 mb-5">Dana akan diambil dari Saldo Keluarga dan dicatat sebagai pengeluaran pendanaan target.</p>
            
            <div class="flex gap-3">
                <button wire:click="closeFundForm" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="fundTarget" wire:loading.attr="disabled"
                    class="flex-1 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="fundTarget">Simpan</span>
                    <span wire:loading wire:target="fundTarget">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Topup History Confirmation Modal --}}
    @if ($deletingTopupId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl text-center">
            <div class="w-12 h-12 bg-rose-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Hapus Riwayat Top Up?</h3>
            <p class="text-slate-400 text-sm mb-6">Data transaksi pengeluaran di saldo keluarga dan dana terkumpul target juga akan otomatis dikurangi/dihapus.</p>
            <div class="flex gap-3">
                <button wire:click="cancelDeleteTopup" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="deleteTopup" wire:loading.attr="disabled" id="btn-konfirmasi-hapus-topup"
                    class="flex-1 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="deleteTopup">Hapus</span>
                    <span wire:loading wire:target="deleteTopup">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Up History Section --}}
    <div class="pt-8 border-t border-slate-700/50 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Top Up Dana Target
                </h2>
                <p class="text-slate-400 text-xs mt-0.5">Daftar transaksi pendanaan target keuangan keluarga.</p>
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="card-glass p-4 rounded-2xl border border-slate-700/50 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs text-slate-400 font-medium block mb-1">Bulan & Tahun</label>
                    <input type="month" wire:model.live="filterMonth" class="input-dark text-sm py-2 px-3 w-full">
                </div>
                <div>
                    <label class="text-xs text-slate-400 font-medium block mb-1">Target Keuangan</label>
                    <select wire:model.live="filterTargetId" class="input-dark text-sm py-2 px-3 w-full">
                        <option value="">Semua Target</option>
                        @foreach($targets as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-400 font-medium block mb-1">Cari Keterangan</label>
                    <input type="text" wire:model.live.debounce.300ms="filterDescription" placeholder="Kata kunci keterangan..." class="input-dark text-sm py-2 px-3 w-full">
                </div>
            </div>
            @if($filterMonth !== now()->format('Y-m') || $filterDescription !== '' || $filterTargetId !== '')
                <div class="flex justify-end">
                    <button wire:click="resetTopupFilters" class="text-xs text-blue-400 hover:text-blue-300 underline transition-colors">
                        🔄 Reset Filter
                    </button>
                </div>
            @endif
        </div>

        {{-- Loading Bar for Filter AJAX --}}
        <div wire:loading wire:target="filterMonth, filterDescription, filterTargetId, resetTopupFilters" class="w-full h-1 bg-slate-700 rounded overflow-hidden">
            <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 animate-pulse rounded"></div>
        </div>

        {{-- History Content Container --}}
        <div wire:loading.class="opacity-50 transition-opacity" wire:target="filterMonth, filterDescription, filterTargetId, resetTopupFilters">
            @if($topupHistories->isEmpty())
                <div class="card-glass rounded-2xl border border-slate-700/50 text-center py-10">
                    <p class="text-slate-500 text-sm">Tidak ada riwayat top up dana target yang ditemukan.</p>
                </div>
            @else
                {{-- Desktop Table View --}}
                <div class="hidden md:block card-glass rounded-2xl border border-slate-700/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-300">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-900/60 border-b border-slate-700/50">
                                <tr>
                                    <th class="px-5 py-3.5">Tanggal</th>
                                    <th class="px-5 py-3.5">Target</th>
                                    <th class="px-5 py-3.5">Jumlah Top Up</th>
                                    <th class="px-5 py-3.5">Keterangan</th>
                                    <th class="px-5 py-3.5">Oleh</th>
                                    @if(Auth::user()->role === 'owner')
                                        <th class="px-5 py-3.5 text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/40">
                                @foreach($topupHistories as $history)
                                    <tr class="hover:bg-slate-700/20 transition-colors">
                                        <td class="px-5 py-3.5 whitespace-nowrap text-slate-300 font-mono text-xs">
                                            {{ $history->date->format('d M Y') }}
                                        </td>
                                        <td class="px-5 py-3.5 font-medium text-white">
                                            {{ $history->target?->name ?? 'Target Dihapus' }}
                                        </td>
                                        <td class="px-5 py-3.5 font-semibold text-emerald-400 whitespace-nowrap">
                                            +Rp {{ number_format($history->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-3.5 text-slate-300">
                                            {{ $history->description ?: '-' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-slate-400 text-xs">
                                            {{ $history->user?->name ?? '-' }}
                                        </td>
                                        @if(Auth::user()->role === 'owner')
                                            <td class="px-5 py-3.5 text-right">
                                                <button wire:click="confirmDeleteTopup({{ $history->id }})" 
                                                    class="px-2.5 py-1 rounded-lg text-xs bg-rose-600/20 text-rose-300 hover:bg-rose-600/40 border border-rose-500/30 transition-colors">
                                                    Hapus
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile Card View --}}
                <div class="block md:hidden space-y-3">
                    @foreach($topupHistories as $history)
                        <div class="card-glass rounded-2xl border border-slate-700/50 p-4 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-white text-sm">{{ $history->target?->name ?? 'Target Dihapus' }}</h4>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ $history->date->format('d M Y') }} · {{ $history->user?->name }}</p>
                                </div>
                                <span class="font-bold text-emerald-400 text-sm whitespace-nowrap">
                                    +Rp {{ number_format($history->amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-300 bg-slate-900/40 p-2.5 rounded-xl border border-slate-700/30">
                                <span class="text-slate-500 block text-[10px] uppercase font-medium">Keterangan:</span>
                                {{ $history->description ?: '-' }}
                            </div>
                            @if(Auth::user()->role === 'owner')
                                <div class="flex justify-end pt-1">
                                    <button wire:click="confirmDeleteTopup({{ $history->id }})"
                                        class="px-3 py-1.5 rounded-lg text-xs bg-rose-600/20 text-rose-300 hover:bg-rose-600/40 border border-rose-500/30 transition-colors">
                                        Hapus Riwayat
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
