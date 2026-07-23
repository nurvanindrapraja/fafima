<div class="space-y-6">

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div x-data x-init="setTimeout(() => $el.remove(), 4000)"
             class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Loading Bar --}}
    <div wire:loading class="w-full h-1 bg-slate-700 rounded overflow-hidden">
        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 animate-pulse rounded"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Family Info Card --}}
        <div class="card-glass rounded-2xl border border-blue-500/20 p-6 space-y-4">
            <h2 class="font-semibold text-white">Informasi Keluarga</h2>
            <div class="space-y-3">
                <div class="py-2 border-b border-slate-700/30">
                    <p class="text-xs text-slate-400 mb-0.5">Nama Keluarga</p>
                    <p class="text-sm font-semibold text-white">{{ $family?->name }}</p>
                </div>
                <div class="py-2 border-b border-slate-700/30">
                    <p class="text-xs text-slate-400 mb-1">Kode Undangan</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xl font-mono font-bold text-blue-300 tracking-[0.2em]">{{ $family?->code }}</code>
                    </div>
                </div>
                <div class="py-2">
                    <p class="text-xs text-slate-400 mb-1">Jumlah Anggota</p>
                    <p class="text-sm font-semibold text-white">{{ $members?->count() ?? 0 }} orang</p>
                </div>
                
                @if(Auth::user()->role === 'owner')
                <div class="py-3 border-t border-slate-700/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-300 font-semibold mb-0.5">Visibilitas Data Member</p>
                            <p class="text-[10px] text-slate-500">Izinkan anggota melihat transaksi satu sama lain</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="allowMemberViewAllTransactions" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-500"></div>
                        </label>
                    </div>
                </div>
                @endif
            </div>
            @if(Auth::user()->role === 'owner')
            <div class="pt-2 space-y-2">
                <button onclick="navigator.clipboard.writeText('{{ $family?->code }}').then(() => { this.textContent = '✓ Disalin!'; setTimeout(()=>{this.textContent='Salin Kode Undangan'},2000) })"
                    class="btn-primary w-full py-2 rounded-xl text-sm font-medium" id="btn-salin-kode-settings">
                    Salin Kode Undangan
                </button>
                <button x-data @click="$dispatch('open-qr-modal')" 
                    class="w-full py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white transition-all text-sm font-medium">
                    Tampilkan QR Code
                </button>
                <button wire:click="regenerateCode" wire:loading.attr="disabled"
                    class="w-full py-2 rounded-xl border border-slate-600 text-slate-300 hover:border-slate-500 hover:text-white transition-all text-sm"
                    id="btn-perbarui-kode">
                    <span wire:loading.remove wire:target="regenerateCode">🔄 Perbarui Kode</span>
                    <span wire:loading wire:target="regenerateCode">Memperbarui...</span>
                </button>
            </div>
            @endif
        </div>

        {{-- Members List --}}
        <div class="lg:col-span-2 card-glass rounded-2xl border border-slate-700/50 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-700/40">
                <h2 class="font-semibold text-white">Daftar Anggota</h2>
            </div>
            <div class="divide-y divide-slate-700/30">
                @foreach($members as $member)
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-700/10 transition-colors group">
                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white
                        {{ $member->role === 'owner' ? 'bg-gradient-to-br from-blue-500 to-cyan-400' : 'bg-slate-600' }}">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ $member->name }}
                            @if($member->id === Auth::id()) <span class="text-xs text-blue-400">(Saya)</span> @endif
                        </p>
                        <p class="text-xs text-slate-500 truncate">{{ $member->email }}</p>
                    </div>
                    {{-- Role Badge --}}
                    <span class="px-2.5 py-1 rounded-full text-xs capitalize font-medium flex-shrink-0
                        {{ $member->role === 'owner' ? 'bg-blue-500/20 text-blue-300' : 'bg-slate-500/20 text-slate-300' }}">
                        {{ $member->role === 'owner' ? '👑 Owner' : 'Member' }}
                    </span>
                    {{-- Actions (Owner only, not on self) --}}
                    @if(Auth::user()->role === 'owner' && $member->id !== Auth::id() && $member->role !== 'owner')
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="confirmTransfer({{ $member->id }})"
                            class="px-2.5 py-1 rounded-lg bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 transition-colors text-xs font-medium whitespace-nowrap"
                            title="Transfer Kepemilikan">
                            👑 Jadikan Owner
                        </button>
                        <button wire:click="confirmKick({{ $member->id }})"
                            class="px-2.5 py-1 rounded-lg bg-rose-600/20 text-rose-400 hover:bg-rose-600/40 transition-colors text-xs font-medium"
                            title="Keluarkan Anggota">
                            Keluarkan
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Kick Confirm Modal --}}
    @if ($kickingId)
    @php $kickTarget = $members->find($kickingId); @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl text-center">
            <div class="w-12 h-12 bg-rose-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Keluarkan Anggota?</h3>
            <p class="text-slate-400 text-sm mb-6"><strong class="text-white">{{ $kickTarget?->name }}</strong> akan dikeluarkan dari keluarga ini. Data transaksinya tetap tersimpan.</p>
            <div class="flex gap-3">
                <button wire:click="cancelKick" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="kickMember" wire:loading.attr="disabled" id="btn-konfirmasi-kick"
                    class="flex-1 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-all">
                    <span wire:loading.remove wire:target="kickMember">Ya, Keluarkan</span>
                    <span wire:loading wire:target="kickMember">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Transfer Ownership Confirm Modal --}}
    @if ($showTransferConfirm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm card-glass rounded-2xl p-6 border border-blue-700/60 shadow-2xl text-center">
            <div class="w-12 h-12 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">👑</span>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Transfer Kepemilikan?</h3>
            <p class="text-slate-400 text-sm mb-1"><strong class="text-white">{{ $newOwner?->name }}</strong> akan menjadi Owner baru.</p>
            <p class="text-slate-500 text-xs mb-6">Anda akan otomatis menjadi Member biasa setelah transfer ini.</p>
            <div class="flex gap-3">
                <button wire:click="cancelTransfer" class="flex-1 py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm">Batal</button>
                <button wire:click="transferOwnership" wire:loading.attr="disabled" id="btn-konfirmasi-transfer"
                    class="flex-1 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-all">
                    <span wire:loading.remove wire:target="transferOwnership">Ya, Transfer</span>
                    <span wire:loading wire:target="transferOwnership">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- QR Code Modal --}}
    <div x-data="{ open: false }" 
         x-show="open" 
         @open-qr-modal.window="open = true" 
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div @click.away="open = false" class="w-full max-w-sm card-glass rounded-2xl p-6 border border-slate-700/60 shadow-2xl text-center">
            <h3 class="text-white font-bold text-lg mb-2">QR Code Undangan</h3>
            <p class="text-slate-400 text-sm mb-6">Minta anggota keluarga memindai QR Code ini untuk bergabung.</p>
            
            <div class="bg-white p-4 rounded-xl inline-block mb-6">
                @if($family?->code)
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('family.invite', $family->code)) }}" alt="QR Code" class="w-48 h-48 mx-auto">
                @endif
            </div>

            <button @click="open = false" class="w-full py-2 rounded-xl border border-slate-600 text-slate-300 hover:text-white hover:bg-slate-700 transition-all text-sm">
                Tutup
            </button>
        </div>
    </div>

</div>
