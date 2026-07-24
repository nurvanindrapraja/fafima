<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Target;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TargetTopupHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_topup_target_with_keterangan_and_view_in_history()
    {
        $family = Family::create(['name' => 'Keluarga Topup', 'code' => 'TOPUP1']);
        $owner = User::factory()->create(['family_id' => $family->id, 'role' => 'owner']);

        $target = Target::create([
            'family_id' => $family->id,
            'created_by' => $owner->id,
            'name' => 'Beli Mobil',
            'target_amount' => 100000000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\TargetManager::class)
            ->call('openFundForm', $target->id)
            ->set('fund_amount', '500000')
            ->set('fund_description', 'Nabung bonus kerja Juli')
            ->call('fundTarget')
            ->assertHasNoErrors()
            ->assertSee('Riwayat Top Up Dana Target')
            ->assertSee('Nabung bonus kerja Juli');

        $this->assertDatabaseHas('transactions', [
            'family_id' => $family->id,
            'target_id' => $target->id,
            'amount' => 500000,
            'description' => 'Nabung bonus kerja Juli',
            'is_target_funding' => true,
        ]);

        $this->assertEquals(500000, $target->fresh()->current_amount);
    }

    public function test_filter_topup_history_by_month_keterangan_and_target()
    {
        $family = Family::create(['name' => 'Keluarga Filter', 'code' => 'FILT1']);
        $owner = User::factory()->create(['family_id' => $family->id, 'role' => 'owner']);

        $target1 = Target::create([
            'family_id' => $family->id,
            'created_by' => $owner->id,
            'name' => 'Liburan Bali',
            'target_amount' => 10000000,
            'current_amount' => 2000000,
            'status' => 'active',
        ]);

        $target2 = Target::create([
            'family_id' => $family->id,
            'created_by' => $owner->id,
            'name' => 'Beli Laptop',
            'target_amount' => 15000000,
            'current_amount' => 1000000,
            'status' => 'active',
        ]);

        Transaction::create([
            'family_id' => $family->id,
            'user_id' => $owner->id,
            'target_id' => $target1->id,
            'type' => 'expense',
            'amount' => 2000000,
            'date' => now(),
            'description' => 'DP Hotel Bali',
            'is_target_funding' => true,
        ]);

        Transaction::create([
            'family_id' => $family->id,
            'user_id' => $owner->id,
            'target_id' => $target2->id,
            'type' => 'expense',
            'amount' => 1000000,
            'date' => now(),
            'description' => 'Cicilan Macbook',
            'is_target_funding' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\TargetManager::class)
            ->set('filterDescription', 'Hotel')
            ->assertSee('DP Hotel Bali')
            ->assertDontSee('Cicilan Macbook')
            ->set('filterDescription', '')
            ->set('filterTargetId', (string)$target2->id)
            ->assertSee('Cicilan Macbook')
            ->assertDontSee('DP Hotel Bali');
    }

    public function test_owner_can_delete_topup_history_and_revert_target_amount_and_transaction()
    {
        $family = Family::create(['name' => 'Keluarga Hapus', 'code' => 'HAPUS1']);
        $owner = User::factory()->create(['family_id' => $family->id, 'role' => 'owner']);

        $target = Target::create([
            'family_id' => $family->id,
            'created_by' => $owner->id,
            'name' => 'Dana Darurat',
            'target_amount' => 5000000,
            'current_amount' => 1500000,
            'status' => 'active',
        ]);

        $topup = Transaction::create([
            'family_id' => $family->id,
            'user_id' => $owner->id,
            'target_id' => $target->id,
            'type' => 'expense',
            'amount' => 1500000,
            'date' => now(),
            'description' => 'Topup Dana Darurat',
            'is_target_funding' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\TargetManager::class)
            ->call('confirmDeleteTopup', $topup->id)
            ->call('deleteTopup')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('transactions', [
            'id' => $topup->id,
        ]);

        $this->assertEquals(0, $target->fresh()->current_amount);
    }
}
