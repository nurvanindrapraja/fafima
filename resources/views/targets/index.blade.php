<x-app-layout>
    <x-slot name="title">Target Keuangan</x-slot>

    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Target Keuangan</h1>
            <p class="text-slate-400 text-sm mt-0.5">Buat dan pantau tujuan keuangan bersama keluarga.</p>
        </div>

        @livewire('target-manager')
    </div>
</x-app-layout>
