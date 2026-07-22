<x-app-layout>
    <x-slot name="title">Pengaturan Keluarga</x-slot>

    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Pengaturan Keluarga</h1>
            <p class="text-slate-400 text-sm mt-0.5">Kelola anggota dan atur kepemilikan keluarga.</p>
        </div>

        @livewire('family-settings')
        
        <div class="mt-8 border-t border-slate-700/50 pt-8">
            @livewire('limit-manager')
        </div>
    </div>
</x-app-layout>
