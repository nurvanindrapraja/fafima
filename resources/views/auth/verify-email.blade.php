<x-guest-layout>
    <div class="mb-4 text-sm text-slate-200">
        Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda? Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan yang baru.
    </div>

    <div x-data="{ 
        loading: false, 
        cooldown: 0,
        timer: null,
        success: {{ session('status') == 'verification-link-sent' ? 'true' : 'false' }}, 
        error: false, 
        sendVerification() {
            if (this.cooldown > 0 || this.loading) return;
            this.loading = true;
            this.success = false;
            this.error = false;
            fetch('{{ route('verification.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            }).then(response => {
                if(response.ok) {
                    this.success = true;
                } else {
                    this.error = true;
                }
            }).catch(() => {
                this.error = true;
            }).finally(() => {
                this.loading = false;
                this.startCooldown(30);
            });
        },
        startCooldown(seconds) {
            this.cooldown = seconds;
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.cooldown--;
                if (this.cooldown <= 0) {
                    clearInterval(this.timer);
                }
            }, 1000);
        } 
    }">
        <div x-show="success" x-transition x-cloak class="mb-4 font-medium text-sm text-green-400">
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
        </div>
        
        <div x-show="error" x-transition x-cloak class="mb-4 font-medium text-sm text-red-400">
            Terjadi kesalahan saat mengirim ulang tautan verifikasi. Silakan coba lagi.
        </div>

        <div class="mt-4 flex items-center justify-between">
            <form @submit.prevent="sendVerification">
                <div>
                    <x-primary-button class="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 transition-opacity" x-bind:disabled="loading || cooldown > 0">
                        <span x-show="!loading && cooldown <= 0">Kirim Ulang Email Verifikasi</span>
                        <span x-show="!loading && cooldown > 0" x-cloak x-text="`Kirim Ulang dalam ${cooldown} detik`"></span>
                        <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sedang proses...
                        </span>
                    </x-primary-button>
                </div>
            </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-slate-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-800">
                Keluar
            </button>
        </form>
    </div>
    </div>
</x-guest-layout>
