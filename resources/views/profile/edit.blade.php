<x-app-layout>
    <x-slot name="title">
        Profil Saya
    </x-slot>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Profil Saya</h1>
            <p class="text-slate-400 text-sm mt-0.5">Kelola informasi akun dan pengaturan keamanan Anda.</p>
        </div>

        <div class="space-y-6">
            <div class="card-glass p-4 sm:p-8 rounded-2xl border border-slate-700/50">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card-glass p-4 sm:p-8 rounded-2xl border border-slate-700/50">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card-glass p-4 sm:p-8 rounded-2xl border border-rose-500/20">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
