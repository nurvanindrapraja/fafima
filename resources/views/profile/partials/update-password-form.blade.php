<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            Perbarui Password
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            Pastikan akun Anda menggunakan password acak yang panjang agar tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-300">Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="input-dark mt-1" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-rose-400" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-300">Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="input-dark mt-1" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-rose-400" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-300">Konfirmasi Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="input-dark mt-1" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-rose-400" />
        </div>

        <div class="flex items-center gap-4">
            <button class="btn-primary px-4 py-2 rounded-xl text-sm font-semibold">Simpan</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-400"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
