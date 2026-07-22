<div class="space-y-6" x-data="transactionManager()">

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div x-show="true" x-init="setTimeout(() => $el.remove(), 3000)"
             class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card-glass p-5 rounded-2xl border border-emerald-500/30">
            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Total Pemasukan</p>
            <p class="text-2xl font-bold text-emerald-400">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card-glass p-5 rounded-2xl border border-rose-500/30">
            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-rose-400">Rp {{ number_format($expenseTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card-glass p-5 rounded-2xl border border-blue-500/30">
            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Saldo Bersih</p>
            @php $balance = $incomeTotal - $expenseTotal; @endphp
            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-blue-300' : 'text-rose-400' }}">
                Rp {{ number_format(abs($balance), 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Header + Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <h2 class="text-xl font-bold text-white">Daftar Transaksi</h2>
        <button wire:click="openForm" wire:loading.attr="disabled"
            class="btn-primary flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm transition-all disabled:opacity-50"
            id="btn-tambah-transaksi">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Transaksi
        </button>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="🔍 Cari deskripsi atau kategori..."
            class="input-dark col-span-1 sm:col-span-1 text-sm" id="input-search-transaksi">
        <select wire:model.live="filterType" class="input-dark text-sm" id="filter-jenis">
            <option value="">Semua Jenis</option>
            <option value="income">Pemasukan</option>
            <option value="expense">Pengeluaran</option>
        </select>
        <input wire:model.live="filterMonth" type="month" class="input-dark text-sm" id="filter-bulan">
    </div>

    {{-- Loading Bar --}}
    <div wire:loading class="w-full h-1 bg-slate-700 rounded overflow-hidden">
        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 animate-pulse rounded"></div>
    </div>

    {{-- Transaction Form Modal --}}
    @if ($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Edit Transaksi' : 'Tambah Transaksi' }}</h3>
                <button wire:click="resetForm" class="text-slate-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            @error('form') <div class="mb-4 text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 px-4 py-2 rounded-lg">{{ $message }}</div> @enderror

            <div class="space-y-4">
                {{-- Upload Struk (OCR) --}}
                <div x-data="imageCompressor()" @receipt-processed.window="isLoading = false" class="p-4 border border-dashed border-slate-600 rounded-xl bg-slate-800/30 text-center relative overflow-hidden">
                    <div x-show="isLoading" style="display: none;" class="absolute inset-0 bg-slate-900/80 flex flex-col items-center justify-center z-10 backdrop-blur-sm">
                        <svg class="animate-spin h-6 w-6 text-blue-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4a8 8 0 01-8 8 8 8 0 01-8-8z"></path></svg>
                        <span class="text-xs text-blue-300 font-medium" x-text="loadingText"></span>
                    </div>
                    
                    <input type="file" @change="handleUpload" id="receipt-upload" class="hidden" accept="image/*" capture="environment">
                    <label for="receipt-upload" class="cursor-pointer flex flex-col items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400 mb-2 group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="text-sm font-medium text-slate-300 hover:text-blue-400 transition-colors">Foto / Upload Struk</span>
                        <span class="text-xs text-slate-500 mt-1">Otomatis di-compress (Maks 2MB setelah kompresi)</span>
                    </label>
                    @error('receiptImage') <p class="text-xs text-rose-400 mt-2">{{ $message }}</p> @enderror
                    @if (session()->has('info')) <p class="text-xs text-emerald-400 mt-2 font-medium">{{ session('info') }}</p> @endif
                </div>

                {{-- Type --}}
                <div>
                    <label class="text-xs text-slate-400 uppercase tracking-wider mb-2 block">Jenis Transaksi</label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl cursor-pointer border transition-all
                            {{ $type === 'income' ? 'bg-emerald-600/30 border-emerald-500 text-emerald-300' : 'border-slate-600 text-slate-400 hover:border-slate-500' }}">
                            <input type="radio" wire:model.live="type" value="income" class="sr-only"> Pemasukan
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl cursor-pointer border transition-all
                            {{ $type === 'expense' ? 'bg-rose-600/30 border-rose-500 text-rose-300' : 'border-slate-600 text-slate-400 hover:border-slate-500' }}">
                            <input type="radio" wire:model.live="type" value="expense" class="sr-only"> Pengeluaran
                        </label>
                    </div>
                    @error('type') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label for="form-amount" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Jumlah (Rp)</label>
                    <input id="form-amount" x-data="currencyInput(@entangle('amount'))" x-model="display" @input="updateValue" type="text" placeholder="0"
                        class="input-dark w-full text-lg font-semibold" wire:loading.attr="disabled">
                    @error('amount') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Date --}}
                <div>
                    <label for="form-date" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Tanggal</label>
                    <input id="form-date" wire:model="date" type="date" class="input-dark w-full" wire:loading.attr="disabled">
                    @error('date') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="form-category" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Kategori (Opsional)</label>
                    <select id="form-category" wire:model="category_id" class="input-dark w-full" wire:loading.attr="disabled">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            @if ($cat->type === $type)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label for="form-desc" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Deskripsi (Opsional)</label>
                    <input id="form-desc" wire:model="description" type="text" placeholder="Cth: Makan siang, Belanja bulanan..."
                        class="input-dark w-full" wire:loading.attr="disabled">
                    @error('description') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="resetForm" wire:loading.attr="disabled"
                    class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:border-slate-500 hover:text-white transition-all text-sm font-medium">
                    Batal
                </button>
                <button wire:click="save" wire:loading.attr="disabled" id="btn-simpan-transaksi"
                    class="flex-1 btn-primary py-2 rounded-xl text-sm font-semibold disabled:opacity-50 flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Simpan' }}</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4a8 8 0 01-8 8 8 8 0 01-8-8z"></path></svg>
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
            <h3 class="text-white font-bold text-lg mb-2">Hapus Transaksi?</h3>
            <p class="text-slate-400 text-sm mb-6">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button wire:click="cancelDelete" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="deleteTransaction" wire:loading.attr="disabled" id="btn-konfirmasi-hapus"
                    class="flex-1 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="deleteTransaction">Hapus</span>
                    <span wire:loading wire:target="deleteTransaction">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Transactions Table --}}
    <div class="card-glass rounded-2xl overflow-hidden border border-slate-700/50">
        @if ($transactions->isEmpty())
            <div class="text-center py-16 text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <p class="text-sm">Belum ada transaksi ditemukan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                {{-- Desktop Table View --}}
                <table class="w-full text-sm hidden md:table">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 uppercase tracking-widest bg-slate-800/50">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Deskripsi</th>
                            <th class="px-5 py-3">Anggota</th>
                            <th class="px-5 py-3 text-right">Jumlah</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/40">
                        @foreach ($transactions as $tx)
                        <tr class="hover:bg-slate-700/20 transition-colors group">
                            <td class="px-5 py-3 text-slate-300 whitespace-nowrap">{{ $tx->date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $tx->type === 'income' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    {{ $tx->category?->name ?? ($tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-300">
                                {{ $tx->description ?? '-' }}
                                @if($tx->receipt_path)
                                    <button type="button" @click="receiptModalUrl = '{{ asset('storage/' . $tx->receipt_path) }}'" class="text-blue-400 hover:text-blue-300 ml-2 inline-flex items-center gap-1 cursor-pointer focus:outline-none" title="Lihat Struk">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    </button>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $tx->user->name }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $tx->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editTransaction({{ $tx->id }})" wire:loading.attr="disabled"
                                        class="p-1.5 rounded-lg bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 transition-colors"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $tx->id }})"
                                        class="p-1.5 rounded-lg bg-rose-600/20 text-rose-400 hover:bg-rose-600/40 transition-colors"
                                        title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Mobile Card View --}}
                <div class="grid grid-cols-1 gap-3 md:hidden">
                    @foreach ($transactions as $tx)
                    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4 flex flex-col gap-3 relative overflow-hidden shadow-sm">
                        <div class="flex justify-between items-start">
                            <div class="flex flex-col">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium w-fit mb-1.5
                                    {{ $tx->type === 'income' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    {{ $tx->category?->name ?? ($tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">{{ $tx->date->format('d M Y') }} • {{ $tx->user->name }}</span>
                            </div>
                            <span class="font-semibold text-right {{ $tx->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="text-slate-300 text-sm flex items-start justify-between">
                            <span class="flex-1">{{ $tx->description ?? '-' }}</span>
                            @if($tx->receipt_path)
                                <button type="button" @click="receiptModalUrl = '{{ asset('storage/' . $tx->receipt_path) }}'" class="text-blue-400 hover:text-blue-300 ml-2 p-1.5 bg-blue-500/10 rounded-lg shrink-0" title="Lihat Struk">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-2 mt-1 pt-3 border-t border-slate-700/50">
                            <button wire:click="editTransaction({{ $tx->id }})" wire:loading.attr="disabled"
                                class="px-3 py-1.5 rounded-lg bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 transition-colors flex-1 flex justify-center items-center gap-1.5 text-xs font-medium"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                Edit
                            </button>
                            <button wire:click="confirmDelete({{ $tx->id }})"
                                class="px-3 py-1.5 rounded-lg bg-rose-600/20 text-rose-400 hover:bg-rose-600/40 transition-colors flex-1 flex justify-center items-center gap-1.5 text-xs font-medium"
                                title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Receipt Modal --}}
    <div x-show="receiptModalUrl" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 transition-opacity" style="display: none;" @click="receiptModalUrl = null" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative max-w-3xl w-full flex flex-col items-center justify-center" @click.stop x-show="receiptModalUrl" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <button @click="receiptModalUrl = null" type="button" class="absolute -top-12 right-0 text-slate-300 hover:text-white bg-slate-800/50 hover:bg-slate-700 p-2 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <img :src="receiptModalUrl" class="max-w-full max-h-[85vh] rounded-xl object-contain shadow-2xl border border-slate-700/50" alt="Bukti Struk">
        </div>
    </div>
</div>

<script>
function transactionManager() {
    return {
        receiptModalUrl: null,
        init() {
            // listen for wire loading state
        }
    }
}

function imageCompressor() {
    return {
        isLoading: false,
        loadingText: '',
        handleUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.isLoading = true;
            this.loadingText = 'Menyiapkan Gambar...';
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1200;
                    const MAX_HEIGHT = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        this.loadingText = 'Mengunggah & Membaca Struk (AI)...';
                        let filename = file.name;
                        if (!filename.toLowerCase().endsWith('.jpg') && !filename.toLowerCase().endsWith('.jpeg')) {
                            filename = filename.replace(/\.[^/.]+$/, "") + ".jpg";
                        }
                        const newFile = new File([blob], filename, { type: 'image/jpeg' });
                        
                        @this.upload('receiptImage', newFile, 
                            (uploadedFilename) => {
                                // Upload finished, AI reading started on the backend.
                                // We keep isLoading = true until @receipt-processed fires
                            },
                            () => {
                                this.isLoading = false;
                                alert('Terjadi kesalahan saat mengunggah gambar.');
                            },
                            (progressEvent) => {
                                // optional progress handling
                            }
                        );
                    }, 'image/jpeg', 0.8);
                };
                img.onerror = () => {
                    this.isLoading = false;
                    alert('Gagal membaca gambar.');
                };
            };
        }
    }
}
</script>
