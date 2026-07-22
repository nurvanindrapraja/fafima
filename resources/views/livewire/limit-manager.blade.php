<div class="space-y-4">
    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl text-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-white">Aturan Limit Pengeluaran</h3>
        @if(Auth::user()->role === 'owner')
        <button wire:click="openForm" class="btn-primary px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Limit
        </button>
        @endif
    </div>

    {{-- Limit List --}}
    @if($limits->isEmpty())
        <div class="card-glass rounded-2xl border border-slate-700/50 text-center py-8">
            <p class="text-slate-500 text-sm">Belum ada aturan limit pengeluaran.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($limits as $limit)
            <div class="card-glass rounded-2xl border border-slate-700/40 p-4 relative group">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $limit->user_id ? 'bg-indigo-500/20 text-indigo-300' : 'bg-blue-500/20 text-blue-300' }}">
                            {{ $limit->user_id ? 'Individu: ' . $limit->user->name : 'Limit Keluarga' }}
                        </span>
                    </div>
                    @if(Auth::user()->role === 'owner')
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openForm({{ $limit->id }})" class="text-blue-400 hover:text-blue-300" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </button>
                        <button wire:click="delete({{ $limit->id }})" wire:confirm="Yakin ingin menghapus limit ini?" class="text-rose-400 hover:text-rose-300" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                    @endif
                </div>
                
                <div class="mt-4">
                    <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">Maksimal Pengeluaran</p>
                    <p class="text-xl font-bold text-white">
                        @if($limit->limit_type === 'fixed')
                            Rp {{ number_format($limit->amount, 0, ',', '.') }}
                        @else
                            {{ $limit->percentage }}% <span class="text-sm text-slate-400 font-normal">dari Pemasukan</span>
                        @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Limit Form Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-5">{{ $editingId ? 'Edit Limit' : 'Tambah Limit Baru' }}</h3>
            
            <div class="space-y-4">
                {{-- Scope --}}
                <div>
                    <label class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Berlaku Untuk</label>
                    <select wire:model="user_id" class="input-dark w-full">
                        <option value="">Keluarga (Semua Anggota Gabungan)</option>
                        <optgroup label="Anggota Individu">
                            @foreach($familyMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    @error('user_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Type --}}
                <div>
                    <label class="text-xs text-slate-400 uppercase tracking-wider mb-2 block">Tipe Limit</label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl cursor-pointer border transition-all
                            {{ $limit_type === 'fixed' ? 'bg-blue-600/30 border-blue-500 text-blue-300' : 'border-slate-600 text-slate-400 hover:border-slate-500' }}">
                            <input type="radio" wire:model.live="limit_type" value="fixed" class="sr-only"> Nominal Tetap
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl cursor-pointer border transition-all
                            {{ $limit_type === 'percentage' ? 'bg-blue-600/30 border-blue-500 text-blue-300' : 'border-slate-600 text-slate-400 hover:border-slate-500' }}">
                            <input type="radio" wire:model.live="limit_type" value="percentage" class="sr-only"> Persentase
                        </label>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">* Anggota tanpa penghasilan wajib menggunakan Nominal Tetap.</p>
                </div>

                {{-- Value --}}
                @if($limit_type === 'fixed')
                <div>
                    <label class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Nominal Limit (Rp)</label>
                    <input x-data="currencyInput(@entangle('amount'))" x-model="display" @input="updateValue" type="text" class="input-dark w-full text-lg font-semibold" placeholder="Contoh: 1500000">
                    @error('amount') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                @else
                <div>
                    <label class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Persentase dari Total Pemasukan (%)</label>
                    <input wire:model="percentage" type="number" min="1" max="100" class="input-dark w-full text-lg font-semibold" placeholder="Contoh: 80">
                    @error('percentage') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showForm', false)" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="save" class="flex-1 btn-primary py-2 rounded-xl text-sm font-semibold flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
