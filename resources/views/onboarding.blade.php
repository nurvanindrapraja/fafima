<x-guest-layout maxWidth="sm:max-w-2xl">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-white mb-2">Selamat Datang di Family Finance Management</h2>
                    <p class="text-blue-200">Untuk memulai, silakan buat keluarga baru atau bergabung menggunakan kode undangan.</p>
                </div>

    <div class="flex flex-col gap-8 w-full mx-auto">
        <!-- Join Family -->
                    <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-700/50">
                        <h3 class="text-xl font-semibold text-white mb-4">Bergabung ke Keluarga</h3>
                        <p class="text-sm text-slate-300 mb-6">Masukkan kode unik yang diberikan oleh Kepala Keluarga Anda.</p>
                        
                        <form method="POST" action="{{ route('family.join') }}">
                            @csrf
                            <div>
                                <x-input-label for="code" value="Kode Undangan" class="text-slate-300" />
                                <x-text-input id="code" class="block mt-1 w-full bg-slate-800 border-slate-600 text-white focus:border-blue-500 focus:ring-blue-500 uppercase" type="text" name="code" :value="old('code', session('invitation_code'))" required placeholder="Contoh: ABCDEF12" />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div class="mt-6">
                                <x-primary-button class="w-full justify-center bg-teal-600 hover:bg-teal-500">
                                    Gabung Keluarga
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="relative flex items-center py-2">
                        <div class="flex-grow border-t border-slate-700/50"></div>
                        <span class="flex-shrink-0 mx-4 text-slate-500 text-sm font-medium uppercase tracking-wider">Atau</span>
                        <div class="flex-grow border-t border-slate-700/50"></div>
                    </div>

                    <!-- Create Family -->
                    <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-700/50">
                        <h3 class="text-xl font-semibold text-white mb-4">Buat Keluarga Baru</h3>
                        <p class="text-sm text-slate-300 mb-6">Jadilah Kepala Keluarga (Owner) dan undang anggota lain untuk bergabung.</p>
                        
                        <form method="POST" action="{{ route('family.create') }}">
                            @csrf
                            <div>
                                <x-input-label for="name" value="Nama Keluarga" class="text-slate-300" />
                                <x-text-input id="name" class="block mt-1 w-full bg-slate-800 border-slate-600 text-white focus:border-blue-500 focus:ring-blue-500" type="text" name="name" :value="old('name')" required placeholder="Contoh: Keluarga Cemara" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="mt-6">
                                <x-primary-button class="w-full justify-center bg-blue-600 hover:bg-blue-500">
                                    Buat Keluarga
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
    </div>
</x-guest-layout>
