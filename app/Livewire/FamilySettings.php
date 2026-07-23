<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FamilySettings extends Component
{
    public ?int $kickingId = null;
    public ?int $transferingToId = null;
    public bool $showTransferConfirm = false;
    public bool $allowMemberViewAllTransactions = true;
    public string $familyName = '';

    public function mount()
    {
        $user = Auth::user();
        if ($user->family) {
            $this->allowMemberViewAllTransactions = $user->family->allow_member_view_all_transactions;
            $this->familyName = $user->family->name;
        }
    }

    public function updatedAllowMemberViewAllTransactions($value)
    {
        $user = Auth::user();
        if ($user->role === 'owner') {
            $user->family->update(['allow_member_view_all_transactions' => $value]);
            session()->flash('success', 'Pengaturan visibilitas berhasil diperbarui!');
        }
    }

    public function updateFamilyName()
    {
        $user = Auth::user();
        if ($user->role === 'owner') {
            $this->validate([
                'familyName' => 'required|string|max:255',
            ]);
            $user->family->update(['name' => $this->familyName]);
            session()->flash('success', 'Nama keluarga berhasil diperbarui!');
        }
    }

    public function kickMember(): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        if ($this->kickingId) {
            $member = User::where('family_id', $user->family_id)
                ->where('id', $this->kickingId)
                ->where('role', 'member')
                ->firstOrFail();

            // Remove from family (start fresh if they join another)
            $member->update(['family_id' => null, 'role' => 'member']);
            $this->kickingId = null;
            session()->flash('success', "Anggota {$member->name} telah dikeluarkan dari keluarga.");
        }
    }

    public function confirmTransfer(int $userId): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        $this->transferingToId = $userId;
        $this->showTransferConfirm = true;
    }

    public function transferOwnership(): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner' || !$this->transferingToId) return;

        $newOwner = User::where('family_id', $user->family_id)
            ->where('id', $this->transferingToId)
            ->firstOrFail();

        // Swap roles
        $user->update(['role' => 'member']);
        $newOwner->update(['role' => 'owner']);

        $this->showTransferConfirm = false;
        $this->transferingToId = null;
        session()->flash('success', "Kepemilikan berhasil dipindahkan ke {$newOwner->name}. Anda sekarang adalah Member.");
    }

    public function cancelTransfer(): void
    {
        $this->showTransferConfirm = false;
        $this->transferingToId = null;
    }

    public function confirmKick(int $userId): void
    {
        $this->kickingId = $userId;
    }

    public function cancelKick(): void
    {
        $this->kickingId = null;
    }

    public function regenerateCode(): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        $user->family->update(['code' => strtoupper(Str::random(8))]);
        session()->flash('success', 'Kode undangan berhasil diperbarui!');
    }

    public function render()
    {
        $user    = Auth::user();
        $family  = $user->family()->with('members')->first();
        $members = $family?->members()->orderBy('role')->orderBy('name')->get();
        $newOwner = $this->transferingToId
            ? User::find($this->transferingToId)
            : null;

        return view('livewire.family-settings', compact('family', 'members', 'newOwner'));
    }
}
