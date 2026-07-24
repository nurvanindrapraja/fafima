<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use App\Services\OpenAIService;

class TransactionManager extends Component
{
    use WithFileUploads;

    // Form fields
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $type = 'expense';
    public string $amount = '';
    public string $date = '';
    public ?int $category_id = null;
    public string $description = '';

    // Upload Struk
    public $receiptImage;
    public bool $isProcessingReceipt = false;

    // Filter & Search
    public string $search = '';
    public string $filterType = '';
    public string $filterMonth = '';

    // Confirmation delete
    public ?int $deletingId = null;

    protected $rules = [
        'type'        => 'required|in:income,expense',
        'amount'      => 'required|numeric|min:0.01',
        'date'        => 'required|date',
        'category_id' => 'nullable|exists:categories,id',
        'description' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'type.required'   => 'Jenis transaksi wajib diisi.',
        'amount.required' => 'Jumlah wajib diisi.',
        'amount.numeric'  => 'Jumlah harus berupa angka.',
        'amount.min'      => 'Jumlah harus lebih dari 0.',
        'date.required'   => 'Tanggal wajib diisi.',
        'date.date'       => 'Format tanggal tidak valid.',
    ];

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editTransaction(int $id): void
    {
        $user = Auth::user();
        $transaction = Transaction::where('family_id', $user->family_id)->findOrFail($id);

        // Business rule: can only edit within 3 days
        if ($transaction->created_at->diffInDays(now()) > 3 && $user->role !== 'owner') {
            $this->addError('form', 'Transaksi hanya bisa diedit dalam 3 hari setelah dibuat.');
            return;
        }

        $this->editingId     = $transaction->id;
        $this->type          = $transaction->type;
        $this->amount        = (string) $transaction->amount;
        $this->date          = $transaction->date->format('Y-m-d');
        $this->category_id   = $transaction->category_id;
        $this->description   = $transaction->description ?? '';
        $this->showForm      = true;
    }

    public function save(): void
    {
        $this->validate();

        $user   = Auth::user();
        $family = $user->family;

        $data = [
            'family_id'   => $user->family_id,
            'user_id'     => $user->id,
            'type'        => $this->type,
            'amount'      => $this->amount,
            'date'        => $this->date,
            'category_id' => $this->category_id,
            'description' => $this->description,
        ];

        if ($this->receiptImage) {
            $data['receipt_path'] = $this->receiptImage->store('receipts', 'public');
        }

        if ($this->editingId) {
            $transaction = Transaction::where('family_id', $user->family_id)->findOrFail($this->editingId);
            $transaction->update($data);
        } else {
            Transaction::create($data);
        }

        $this->resetForm();
        $this->dispatch('transaction-saved');
        session()->flash('success', 'Transaksi berhasil disimpan!');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function deleteTransaction(): void
    {
        if ($this->deletingId) {
            $user = Auth::user();
            $transaction = Transaction::where('family_id', $user->family_id)->findOrFail($this->deletingId);

            // Business rule: owner can delete anytime, member only within 3 days
            if ($transaction->created_at->diffInDays(now()) > 3 && $user->role !== 'owner') {
                $this->addError('form', 'Transaksi hanya bisa dihapus dalam 3 hari setelah dibuat.');
                $this->deletingId = null;
                return;
            }

            if ($transaction->is_target_funding && $transaction->target_id) {
                $target = \App\Models\Target::find($transaction->target_id);
                if ($target) {
                    $target->current_amount = max(0, $target->current_amount - $transaction->amount);
                    $target->save();
                }
            }

            $transaction->delete();
            $this->deletingId = null;
            session()->flash('success', 'Transaksi berhasil dihapus!');
        }
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->editingId   = null;
        $this->showForm    = false;
        $this->type        = 'expense';
        $this->amount      = '';
        $this->date        = now()->format('Y-m-d');
        $this->category_id = null;
        $this->description = '';
        $this->receiptImage = null;
        $this->resetValidation();
    }

    public function updatedReceiptImage()
    {
        $this->validate([
            'receiptImage' => 'image|max:2048',
        ]);

        $this->isProcessingReceipt = true;
        $user = Auth::user();

        if (!$user->allow_ai_receipt) {
            $this->isProcessingReceipt = false;
            session()->flash('info', 'Gambar struk berhasil dilampirkan. Isi detail manual.');
            $this->dispatch('receipt-processed');
            return;
        }

        try {
            $path = $this->receiptImage->getRealPath();
            $base64 = base64_encode(file_get_contents($path));

            $user = Auth::user();
            $categories = Category::where(function ($q) use ($user) {
                $q->where('family_id', $user->family_id)->orWhereNull('family_id');
            })->pluck('name')->toArray();

            $aiService = new OpenAIService();
            $result = $aiService->parseReceipt($base64, $categories);

            if ($result) {
                if (isset($result['amount'])) {
                    $this->amount = (string) $result['amount'];
                }
                if (isset($result['date'])) {
                    $this->date = $result['date'];
                }
                if (isset($result['description'])) {
                    $this->description = $result['description'];
                }
                if (isset($result['category'])) {
                    $category = Category::where(function ($q) use ($user) {
                        $q->where('family_id', $user->family_id)->orWhereNull('family_id');
                    })->where('name', $result['category'])->first();
                    if ($category) {
                        $this->category_id = $category->id;
                        $this->type = $category->type;
                    }
                }
                session()->flash('info', 'Struk berhasil dipindai oleh AI!');
            } else {
                $this->addError('receiptImage', 'Gagal memindai struk. Pastikan gambar jelas.');
            }
        } catch (\Exception $e) {
            $this->addError('receiptImage', $e->getMessage());
        }

        $this->isProcessingReceipt = false;
        $this->dispatch('receipt-processed');
    }

    public function render()
    {
        $user = Auth::user();

        $query = Transaction::with(['category', 'user'])
            ->where('family_id', $user->family_id);

        // Visibility: non-owner members check family settings
        if ($user->role === 'member' && !$user->family->allow_member_view_all_transactions) {
            $query->where('user_id', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%'.$this->search.'%')
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterMonth) {
            [$year, $month] = explode('-', $this->filterMonth);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $transactions = $query->orderByDesc('date')->orderByDesc('id')->get();

        // Summary stats
        $incomeTotal  = (clone $query)->where('type', 'income')->sum('amount');
        $expenseTotal = (clone $query)->where('type', 'expense')->sum('amount');

        $categories = Category::where(function ($q) use ($user) {
            $q->where('family_id', $user->family_id)->orWhereNull('family_id');
        })->orderBy('name')->get();

        return view('livewire.transaction-manager', compact(
            'transactions', 'categories', 'incomeTotal', 'expenseTotal'
        ));
    }
}
