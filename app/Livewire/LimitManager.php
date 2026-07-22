<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Limit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LimitManager extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    
    // Form fields
    public $user_id = ''; // Empty string means Family Limit, otherwise specific member ID
    public string $limit_type = 'fixed'; // 'fixed' or 'percentage'
    public string $amount = '';
    public string $percentage = '';

    protected function rules()
    {
        return [
            'user_id'    => 'nullable|exists:users,id',
            'limit_type' => 'required|in:fixed,percentage',
            'amount'     => 'required_if:limit_type,fixed|nullable|numeric|min:1',
            'percentage' => 'required_if:limit_type,percentage|nullable|numeric|min:1|max:100',
        ];
    }

    protected $messages = [
        'limit_type.required' => 'Tipe limit wajib diisi.',
        'amount.required_if'  => 'Nominal wajib diisi untuk tipe limit tetap.',
        'percentage.required_if' => 'Persentase wajib diisi untuk tipe limit persentase.',
        'percentage.max'      => 'Persentase maksimal 100%.',
    ];

    public function openForm(?int $limitId = null)
    {
        $this->resetValidation();
        $this->reset(['user_id', 'limit_type', 'amount', 'percentage', 'editingId']);
        
        if ($limitId) {
            $user = Auth::user();
            $limit = Limit::where('family_id', $user->family_id)->findOrFail($limitId);
            $this->editingId = $limit->id;
            $this->user_id = $limit->user_id ?? '';
            $this->limit_type = $limit->limit_type;
            $this->amount = $limit->amount ? (string) $limit->amount : '';
            $this->percentage = $limit->percentage ? (string) $limit->percentage : '';
        }
        
        $this->showForm = true;
    }

    public function save()
    {
        $user = Auth::user();
        if ($user->role !== 'owner') {
            abort(403, 'Unauthorized');
        }

        $this->validate();

        $data = [
            'family_id'  => $user->family_id,
            'user_id'    => $this->user_id === '' ? null : $this->user_id,
            'limit_type' => $this->limit_type,
            'amount'     => $this->limit_type === 'fixed' ? $this->amount : null,
            'percentage' => $this->limit_type === 'percentage' ? $this->percentage : null,
        ];

        // Check if limit already exists for this scope (family or specific user) to avoid duplicates
        $query = Limit::where('family_id', $user->family_id);
        if ($data['user_id']) {
            $query->where('user_id', $data['user_id']);
        } else {
            $query->whereNull('user_id');
        }
        
        $existing = $query->first();

        if ($this->editingId) {
            $limit = Limit::where('family_id', $user->family_id)->findOrFail($this->editingId);
            $limit->update($data);
            session()->flash('success', 'Limit berhasil diperbarui!');
        } else {
            if ($existing) {
                $this->addError('user_id', 'Limit untuk ' . ($data['user_id'] ? 'anggota ini' : 'Keluarga') . ' sudah ada. Silakan edit limit yang sudah ada.');
                return;
            }
            Limit::create($data);
            session()->flash('success', 'Limit baru berhasil dibuat!');
        }

        $this->showForm = false;
    }

    public function delete(int $id)
    {
        $user = Auth::user();
        if ($user->role !== 'owner') return;

        Limit::where('family_id', $user->family_id)->findOrFail($id)->delete();
        session()->flash('success', 'Limit berhasil dihapus.');
    }

    public function render()
    {
        $user = Auth::user();
        
        $limits = Limit::with('user')
            ->where('family_id', $user->family_id)
            ->get();
            
        $familyMembers = User::where('family_id', $user->family_id)->orderBy('name')->get();

        return view('livewire.limit-manager', compact('limits', 'familyMembers'));
    }
}
