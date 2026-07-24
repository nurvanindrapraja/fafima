<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SmartAdvisorVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_smart_advisor_is_visible_when_ai_is_enabled()
    {
        $family = Family::create([
            'name' => 'Keluarga Test',
            'code' => 'TEST12',
        ]);

        $user = User::factory()->create([
            'family_id' => $family->id,
            'role' => 'owner',
            'allow_ai_receipt' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Smart Advisor AI');

        Livewire::actingAs($user)
            ->test(\App\Livewire\SmartAdvisor::class)
            ->assertSet('allowAi', true)
            ->assertSee('Smart Advisor AI');
    }

    public function test_smart_advisor_is_hidden_when_ai_is_disabled()
    {
        $family = Family::create([
            'name' => 'Keluarga Test',
            'code' => 'TEST12',
        ]);

        $user = User::factory()->create([
            'family_id' => $family->id,
            'role' => 'member',
            'allow_ai_receipt' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('Smart Advisor AI');

        Livewire::actingAs($user)
            ->test(\App\Livewire\SmartAdvisor::class)
            ->assertSet('allowAi', false)
            ->assertDontSee('Smart Advisor AI');
    }
}
