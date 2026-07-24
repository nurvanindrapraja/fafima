<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            'Makanan & Minuman', 'Transport', 'Belanja Bulanan', 'Tagihan Listrik',
            'Tagihan Air', 'Tagihan Internet', 'Kesehatan', 'Pendidikan',
            'Hiburan', 'Pakaian', 'Perawatan Rumah', 'Olahraga', 'Tabungan', 'Lain-lain',
        ];

        $incomeCategories = [
            'Gaji', 'Bonus', 'Freelance', 'Investasi', 'Bisnis', 'Hadiah', 'Lain-lain (Pemasukan)',
        ];

        foreach ($expenseCategories as $name) {
            DB::table('categories')->insertOrIgnore([
                'name'       => $name,
                'type'       => 'expense',
                'family_id'  => null,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($incomeCategories as $name) {
            DB::table('categories')->insertOrIgnore([
                'name'       => $name,
                'type'       => 'income',
                'family_id'  => null,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
