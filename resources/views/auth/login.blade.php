<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Loading Bar -->
    <div x-data="{ loading: false }" @login-started.window="loading = true" @login-finished.window="loading = false" x-show="loading" style="display: none;" class="w-full h-1 bg-slate-800 rounded overflow-hidden mb-4 relative top-0 left-0">
        <div class="h-full bg-blue-500 animate-[pulse_1s_ease-in-out_infinite] rounded w-full"></div>
    </div>

    <form @submit.prevent="login" x-data="{ 
        email: '{{ old('email') }}', 
        password: '', 
        remember: false, 
        loading: false, 
        errorMessage: '',
        errors: {},
        async login() {
            this.loading = true;
            this.errorMessage = '';
            this.errors = {};
            $dispatch('login-started');
            
            try {
                const response = await fetch('{{ route('login') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                    },
                    body: JSON.stringify({
                        email: this.email,
                        password: this.password,
                        remember: this.remember
                    })
                });

                if (response.ok || response.status === 204) {
                    window.location.href = '{{ route('dashboard') }}';
                } else if (response.status === 422) {
                    const data = await response.json();
                    this.errors = data.errors;
                    this.loading = false;
                    $dispatch('login-finished');
                } else {
                    this.errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                    this.loading = false;
                    $dispatch('login-finished');
                }
            } catch (error) {
                this.errorMessage = 'Gagal terhubung ke server.';
                this.loading = false;
                $dispatch('login-finished');
            }
        }
    }">
        <!-- General Error Message -->
        <div x-show="errorMessage" x-text="errorMessage" class="mb-4 text-sm font-medium text-red-500 bg-red-500/10 rounded-lg p-3" style="display: none;"></div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-300" />
            <x-text-input id="email" x-model="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <template x-if="errors.email">
                <p x-text="errors.email[0]" class="text-sm text-red-500 mt-2"></p>
            </template>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-slate-300" />

            <x-text-input id="password" x-model="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <template x-if="errors.password">
                <p x-text="errors.password[0]" class="text-sm text-red-500 mt-2"></p>
            </template>
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" x-model="remember" type="checkbox" class="rounded border-slate-600 bg-slate-800 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-slate-300">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('register'))
                <a class="underline text-sm text-slate-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-800" href="{{ route('register') }}">
                    Belum punya akun?
                </a>
            @endif

            @if (Route::has('password.request'))
                <a class="underline text-sm text-slate-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-800" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>
        
        <div class="mt-4">
            <x-primary-button class="w-full justify-center bg-blue-600 hover:bg-blue-500 disabled:opacity-50 transition-opacity" x-bind:disabled="loading">
                <span x-show="!loading">{{ __('Log in') }}</span>
                <span x-show="loading" class="flex items-center justify-center gap-2" style="display: none;">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sedang proses...
                </span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
