<x-guest-layout>
    <div x-data="{
        token: '{{ $request->route('token') }}',
        email: '{{ old('email', $request->email) }}',
        password: '',
        password_confirmation: '',
        loading: false,
        success: false,
        error: '{{ $errors->first('email') }}',
        
        async submitForm() {
            if (!this.email || !this.password || !this.password_confirmation) return;
            
            this.loading = true;
            this.success = false;
            this.error = '';
            
            try {
                const response = await fetch('{{ route('password.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        token: this.token,
                        email: this.email,
                        password: this.password,
                        password_confirmation: this.password_confirmation
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.success = true;
                    window.location.href = data.redirect || '{{ route('login') }}';
                } else {
                    this.error = data.errors?.email?.[0] || data.errors?.password?.[0] || data.message || 'Terjadi kesalahan saat memproses permintaan.';
                }
            } catch (err) {
                this.error = 'Koneksi terputus. Silakan periksa jaringan Anda.';
            } finally {
                this.loading = false;
            }
        }
    }">

        <div x-show="success" x-cloak class="mb-4 font-medium text-sm text-green-400">
            Password Anda telah berhasil diatur ulang! Mengarahkan ke halaman login...
        </div>
        
        <div x-show="error" x-cloak class="mb-4 p-4 rounded-md bg-red-900/30 border border-red-500/50">
            <p class="font-medium text-sm text-red-400" x-text="(error.toLowerCase().includes('invalid') || error.toLowerCase().includes('token')) ? 'Tautan reset password ini sudah tidak valid atau telah kadaluarsa.' : error"></p>
            <a x-show="(error.toLowerCase().includes('invalid') || error.toLowerCase().includes('token'))" href="{{ route('password.request') }}" class="inline-block mt-2 text-sm text-blue-400 hover:text-blue-300 underline transition-colors">
                Kirim ulang email reset password
            </a>
        </div>

        <form @submit.prevent="submitForm">
            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-white" />
                <x-text-input id="email" class="block mt-1 w-full bg-slate-900 border-slate-600 text-white" type="email" name="email" x-model="email" required autofocus autocomplete="username" x-bind:disabled="loading" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" class="text-white" />
                <x-text-input id="password" class="block mt-1 w-full bg-slate-900 border-slate-600 text-white" type="password" name="password" x-model="password" required autocomplete="new-password" x-bind:disabled="loading" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-white" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full bg-slate-900 border-slate-600 text-white" type="password" name="password_confirmation" x-model="password_confirmation" required autocomplete="new-password" x-bind:disabled="loading" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="bg-blue-600 hover:bg-blue-500 relative transition-all" x-bind:disabled="loading" x-bind:class="{ 'opacity-75 cursor-not-allowed': loading }">
                    <span x-show="!loading">Reset Password</span>
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
