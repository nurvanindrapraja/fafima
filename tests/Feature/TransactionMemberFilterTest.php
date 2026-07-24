<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionMemberFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_transactions_by_family_member()
    {
        $family = Family::create(['name' => 'Keluarga Filter User', 'code' => 'MEMBER1']);
        $owner = User::factory()->create(['family_id' => $family->id, 'role' => 'owner', 'name' => 'Ayah']);
        $member = User::factory()->create(['family_id' => $family->id, 'role' => 'member', 'name' => 'Ibu']);

        $txOwner = Transaction::create([
            'family_id' => $family->id,
            'user_id' => $owner->id,
            'type' => 'expense',
            'amount' => 150000,
            'date' => now(),
            'description' => 'Bensin Ayah',
        ]);

        $txMember = Transaction::create([
            'family_id' => $family->id,
            'user_id' => $member->id,
            'type' => 'expense',
            'amount' => 250000,
            'date' => now(),
            'description' => 'Belanja Sayur Ibu',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\TransactionManager::class)
            ->assertSee('Bensin Ayah')
            ->assertSee('Belanja Sayur Ibu')
            ->set('filterUserId', (string)$owner->id)
            ->assertSee('Bensin Ayah')
            ->assertDontSee('Belanja Sayur Ibu')
            ->set('filterUserId', (string)$member->id)
            ->assertSee('Belanja Sayur Ibu')
            ->assertDontSee('Bensin Ayah');
    }
}
