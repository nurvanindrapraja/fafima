<x-guest-layout>
    <div class="mb-4 text-sm text-slate-200">
        Lupa password Anda? Tidak masalah. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan reset password yang memungkinkan Anda memilih password baru.
    </div>

    <div x-data="{
        email: '{{ old('email') }}',
        loading: false,
        success: {{ session('status') ? 'true' : 'false' }},
        error: '{{ $errors->first('email') }}',
        async sendResetLink() {
            if (!this.email) return;
            
            this.loading = true;
            this.success = false;
            this.error = '';
            
            try {
                const response = await fetch('{{ route('password.email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: this.email })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.success = true;
                    this.email = '';
                } else {
                    this.error = data.errors?.email?.[0] || data.message || 'Terjadi kesalahan saat mengirim email.';
                }
            } catch (err) {
                this.error = 'Koneksi terputus. Silakan periksa jaringan Anda.';
            } finally {
                this.loading = false;
            }
        }
    }">
    
        <div x-show="success" x-cloak class="mb-4 font-medium text-sm text-green-400">
            Email tautan reset password telah berhasil dikirim!
        </div>
        
        <div x-show="error" x-cloak class="mb-4 font-medium text-sm text-red-400" x-text="error"></div>

        <form @submit.prevent="sendResetLink">
            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-slate-300" />
                <x-text-input id="email" class="block mt-1 w-full bg-slate-900 border-slate-600 text-white" type="email" name="email" x-model="email" required autofocus x-bind:disabled="loading" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <a class="underline text-sm text-slate-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-800" href="{{ route('login') }}">
                    Kembali ke halaman login
                </a>
                
                <x-primary-button class="bg-blue-600 hover:bg-blue-500 relative transition-all" x-bind:disabled="loading" x-bind:class="{ 'opacity-75 cursor-not-allowed': loading }">
                    <span x-show="!loading">Kirim Tautan Reset</span>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sedang proses...
                    </span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
