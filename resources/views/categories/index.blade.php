<x-app-layout>
    <x-slot name="title">Kategori</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Manajemen Kategori</h1>
                <p class="text-slate-400 text-sm mt-0.5">Kelola kategori pemasukan dan pengeluaran.</p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-rose-500/20 border border-rose-500/50 text-rose-300 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Add Category Form --}}
            <div class="card-glass rounded-2xl border border-slate-700/50 p-6">
                <h2 class="font-semibold text-white mb-4">Tambah Kategori Baru</h2>
                <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="cat-name" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Nama Kategori</label>
                        <input id="cat-name" name="name" type="text" :value="old('name')" required placeholder="Cth: Makan, Transport..."
                            class="input-dark w-full">
                        @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cat-type" class="text-xs text-slate-400 uppercase tracking-wider mb-1 block">Jenis</label>
                        <select id="cat-type" name="type" class="input-dark w-full">
                            <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                            <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        </select>
                    </div>
                    <button type="submit" id="btn-tambah-kategori" class="btn-primary w-full py-2 rounded-xl text-sm font-semibold">
                        Tambah Kategori
                    </button>
                </form>
            </div>

            {{-- Categories List --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Income --}}
                <div class="card-glass rounded-2xl border border-emerald-500/20 overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-700/40">
                        <h3 class="font-semibold text-emerald-400 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                            Kategori Pemasukan
                        </h3>
                    </div>
                    @php $incomeCategories = $categories->where('type', 'income'); @endphp
                    @if($incomeCategories->isEmpty())
                        <div class="px-5 py-4 text-sm text-slate-500">Belum ada kategori pemasukan.</div>
                    @else
                        <div class="divide-y divide-slate-700/30">
                            @foreach($incomeCategories as $cat)
                            <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-700/10 transition-colors group">
                                <span class="text-sm text-slate-200">{{ $cat->name }}</span>
                                @if($cat->family_id && (Auth::user()->role === 'owner' || $cat->created_by === Auth::id()))
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="opacity-0 group-hover:opacity-100 transition-opacity"
                                    onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-600/20 text-rose-400 hover:bg-rose-600/40 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Expense --}}
                <div class="card-glass rounded-2xl border border-rose-500/20 overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-700/40">
                        <h3 class="font-semibold text-rose-400 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                            Kategori Pengeluaran
                        </h3>
                    </div>
                    @php $expenseCategories = $categories->where('type', 'expense'); @endphp
                    @if($expenseCategories->isEmpty())
                        <div class="px-5 py-4 text-sm text-slate-500">Belum ada kategori pengeluaran.</div>
                    @else
                        <div class="divide-y divide-slate-700/30">
                            @foreach($expenseCategories as $cat)
                            <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-700/10 transition-colors group">
                                <span class="text-sm text-slate-200">{{ $cat->name }}</span>
                                @if($cat->family_id && (Auth::user()->role === 'owner' || $cat->created_by === Auth::id()))
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="opacity-0 group-hover:opacity-100 transition-opacity"
                                    onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-600/20 text-rose-400 hover:bg-rose-600/40 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
