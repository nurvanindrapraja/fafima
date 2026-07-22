<x-app-layout>
    <x-slot name="title">Transaksi</x-slot>

    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen Transaksi</h1>
            <p class="text-slate-400 text-sm mt-0.5">Catat pemasukan dan pengeluaran keluarga Anda.</p>
        </div>

        @livewire('transaction-manager')
    </div>
</x-app-layout>
