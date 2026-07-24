<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Target;
use App\Models\TargetApproval;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class TargetManager extends Component
{
    // Form fields
    public bool $showForm = false;
    public string $name = '';
    public string $target_amount = '';
    public string $target_date = '';

    // Delete confirm
    public ?int $deletingId = null;

    // Funding target
    public bool $showFundForm = false;
    public ?int $fundingTargetId = null;
    public string $fund_amount = '';
    public string $fund_description = '';

    // Topup History Filters & Delete
    public string $filterMonth = '';
    public string $filterDescription = '';
    public string $filterTargetId = '';
    public ?int $deletingTopupId = null;

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
    }

    protected $rules = [
        'name'          => 'required|string|max:255',
        'target_amount' => 'required|numeric|min:1',
        'target_date'   => 'nullable|date|after:today',
    ];

    protected $messages = [
        'name.required'          => 'Nama target wajib diisi.',
        'target_amount.required' => 'Jumlah target wajib diisi.',
        'target_amount.numeric'  => 'Jumlah target harus berupa angka.',
        'target_amount.min'      => 'Jumlah target minimal 1.',
        'target_date.after'      => 'Tenggat waktu harus di masa depan.',
    ];

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $user = Auth::user();

        if ($user->role !== 'owner') {
            $this->addError('form', 'Hanya Owner yang bisa membuat target keuangan.');
            return;
        }

        $this->validate();

        $target = Target::create([
            'family_id'     => $user->family_id,
            'created_by'    => $user->id,
            'name'          => $this->name,
            'target_amount' => $this->target_amount,
            'current_amount'=> 0,
            'target_date'   => $this->target_date ?: null,
            'status'        => 'pending_approval',
        ]);

        // Create approval records for all family members (excluding owner)
        $members = $user->family->members()->where('role', 'member')->get();
        foreach ($members as $member) {
            TargetApproval::create([
                'target_id' => $target->id,
                'user_id'   => $member->id,
                'status'    => 'pending',
            ]);
        }

        // If no members, activate directly
        if ($members->isEmpty()) {
            $target->update(['status' => 'active']);
        }

        $this->resetForm();
        session()->flash('success', 'Target berhasil dibuat! Menunggu persetujuan anggota.');
    }

    public function approve(int $targetId): void
    {
        $user = Auth::user();
        $approval = TargetApproval::where('target_id', $targetId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $approval->update(['status' => 'approved']);
        $this->checkAndActivateTarget($targetId);
        session()->flash('success', 'Target disetujui!');
    }

    public function reject(int $targetId): void
    {
        $user = Auth::user();
        $approval = TargetApproval::where('target_id', $targetId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $approval->update(['status' => 'rejected']);
        session()->flash('info', 'Target ditolak. Owner dapat memperbarui target.');
    }

    public function bypass(int $targetId): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        // Bypass all pending approvals older than 2 days
        TargetApproval::where('target_id', $targetId)
            ->where('status', 'pending')
            ->where('updated_at', '<=', now()->subDays(2))
            ->update(['status' => 'bypassed']);

        $this->checkAndActivateTarget($targetId);
        session()->flash('success', 'Persetujuan di-bypass untuk anggota pasif.');
    }

    private function checkAndActivateTarget(int $targetId): void
    {
        $target = Target::with('approvals')->findOrFail($targetId);
        $pendingCount = $target->approvals->where('status', 'pending')->count();
        $rejectedCount = $target->approvals->where('status', 'rejected')->count();

        if ($rejectedCount > 0) {
            // Stay pending_approval for owner to revise
        } elseif ($pendingCount === 0) {
            $target->update(['status' => 'active']);
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function deleteTarget(): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        Target::where('family_id', $user->family_id)->findOrFail($this->deletingId)->delete();
        $this->deletingId = null;
        session()->flash('success', 'Target berhasil dihapus!');
    }

    public function openFundForm(int $targetId): void
    {
        $this->fundingTargetId = $targetId;
        $this->fund_amount = '';
        $this->fund_description = '';
        $this->showFundForm = true;
    }

    public function closeFundForm(): void
    {
        $this->showFundForm = false;
        $this->fundingTargetId = null;
        $this->fund_amount = '';
        $this->fund_description = '';
    }

    public function fundTarget(): void
    {
        $this->validate([
            'fund_amount' => 'required|numeric|min:1',
            'fund_description' => 'nullable|string|max:255',
        ], [
            'fund_amount.required' => 'Jumlah dana wajib diisi.',
            'fund_amount.numeric'  => 'Jumlah dana harus berupa angka.',
            'fund_amount.min'      => 'Jumlah dana minimal 1.',
            'fund_description.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        $user = Auth::user();
        $target = Target::where('family_id', $user->family_id)
            ->where('status', 'active')
            ->findOrFail($this->fundingTargetId);

        $desc = trim($this->fund_description);
        $finalDesc = $desc !== '' ? $desc : ('Pendanaan Target: ' . $target->name);

        // Find or create 'Tabungan' expense category
        $tabunganCategory = Category::where(function ($q) use ($user) {
            $q->where('family_id', $user->family_id)->orWhereNull('family_id');
        })->where('name', 'Tabungan')->where('type', 'expense')->first();

        if (!$tabunganCategory) {
            $tabunganCategory = Category::create([
                'name' => 'Tabungan',
                'type' => 'expense',
                'family_id' => null,
                'is_default' => true,
            ]);
        }

        \App\Models\Transaction::create([
            'family_id' => $user->family_id,
            'user_id' => $user->id,
            'target_id' => $target->id,
            'category_id' => $tabunganCategory->id,
            'type' => 'expense',
            'amount' => $this->fund_amount,
            'date' => now(),
            'description' => $finalDesc,
            'is_target_funding' => true,
        ]);

        $target->increment('current_amount', $this->fund_amount);

        $this->closeFundForm();
        session()->flash('success', 'Berhasil mendanai target!');
    }

    public function confirmDeleteTopup(int $id): void
    {
        $this->deletingTopupId = $id;
    }

    public function cancelDeleteTopup(): void
    {
        $this->deletingTopupId = null;
    }

    public function deleteTopup(): void
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        if ($this->deletingTopupId) {
            $transaction = \App\Models\Transaction::where('family_id', $user->family_id)
                ->where('is_target_funding', true)
                ->findOrFail($this->deletingTopupId);

            if ($transaction->target_id) {
                $target = Target::find($transaction->target_id);
                if ($target) {
                    $target->current_amount = max(0, $target->current_amount - $transaction->amount);
                    $target->save();
                }
            }

            $transaction->delete();
            $this->deletingTopupId = null;
            session()->flash('success', 'Riwayat top up dan transaksi pengeluaran berhasil dihapus!');
        }
    }

    public function resetTopupFilters(): void
    {
        $this->filterMonth = now()->format('Y-m');
        $this->filterDescription = '';
        $this->filterTargetId = '';
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->showForm      = false;
        $this->name          = '';
        $this->target_amount = '';
        $this->target_date   = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user    = Auth::user();
        $targets = Target::with(['approvals.user', 'creator'])
            ->where('family_id', $user->family_id)
            ->orderByDesc('created_at')
            ->get();

        // Get user's pending approvals
        $myPendingApprovals = TargetApproval::with('target')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        // Topup Histories with Filters
        $topupQuery = \App\Models\Transaction::with(['target', 'user'])
            ->where('family_id', $user->family_id)
            ->where('is_target_funding', true);

        if ($this->filterMonth) {
            $parts = explode('-', $this->filterMonth);
            if (count($parts) === 2) {
                $topupQuery->whereYear('date', (int)$parts[0])
                           ->whereMonth('date', (int)$parts[1]);
            }
        }

        if ($this->filterDescription !== '') {
            $topupQuery->where('description', 'like', '%' . $this->filterDescription . '%');
        }

        if ($this->filterTargetId !== '') {
            $topupQuery->where('target_id', $this->filterTargetId);
        }

        $topupHistories = $topupQuery->orderByDesc('date')->orderByDesc('id')->get();

        return view('livewire.target-manager', compact('targets', 'myPendingApprovals', 'topupHistories'));
    }
}
