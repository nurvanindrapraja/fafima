<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-white">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            Setelah akun Anda dihapus, semua data akan hilang secara permanen. Sebelum menghapus, harap unduh data atau informasi apa pun yang ingin Anda simpan.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors"
    >Hapus Akun</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-[#0f172a]">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-white">
                Apakah Anda yakin ingin menghapus akun?
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Setelah akun Anda dihapus, semua data akan hilang permanen. Silakan masukkan password Anda untuk mengkonfirmasi bahwa Anda ingin menghapus akun secara permanen.
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="input-dark mt-1 block w-3/4"
                    placeholder="Password"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-rose-400" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="py-2 px-4 rounded-xl border border-slate-600 text-slate-300 hover:text-white transition-all text-sm font-semibold">
                    Batal
                </button>

                <button class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
                    Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
