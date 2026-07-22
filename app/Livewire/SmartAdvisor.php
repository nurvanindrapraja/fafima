<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Transaction;
use App\Models\Family;
use App\Services\OpenAIService;

class SmartAdvisor extends Component
{
    public string $ownerAdvice = '';
    public string $memberAdvice = '';
    public bool $isLoading = true;

    public function mount()
    {
        // Don't block render, load data async or fetch via init
        // For simplicity, we can load it in init or render. We will use a wire:init to load it so page loads fast.
    }

    public function loadAdvice()
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        $month = now()->month;
        $year = now()->year;

        // Cache key per family per day to save API costs
        $cacheKey = "smart_advisor_{$familyId}_{$year}_{$month}_" . now()->format('d');

        $advice = Cache::remember($cacheKey, 60 * 60 * 24, function () use ($familyId, $month, $year) {
            $family = Family::find($familyId);
            $income = Transaction::where('family_id', $familyId)->where('type', 'income')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $expense = Transaction::where('family_id', $familyId)->where('type', 'expense')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $balance = $income - $expense;

            $familyLimit = $family->limit_type === 'percentage' 
                ? ($income * ($family->limit_amount / 100)) 
                : $family->limit_amount;
            
            $familyPct = $familyLimit > 0 ? round(($expense / $familyLimit) * 100) : 0;

            $topExpenses = Transaction::where('family_id', $familyId)
                ->where('type', 'expense')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->with('category')
                ->orderByDesc('amount')
                ->take(3)
                ->get()
                ->map(function ($tx) {
                    return ['category' => $tx->category->name ?? 'Lainnya', 'amount' => $tx->amount];
                })->toArray();

            $data = [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance,
                'family_limit' => $familyLimit,
                'family_pct' => $familyPct,
                'top_expenses' => $topExpenses
            ];

            $aiService = new OpenAIService();
            return $aiService->generateAdvisor($data);
        });

        $this->ownerAdvice = $advice['owner_advice'] ?? 'Belum ada saran saat ini.';
        $this->memberAdvice = $advice['member_advice'] ?? 'Belum ada saran saat ini.';
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.smart-advisor', [
            'isOwner' => Auth::user()->role === 'owner'
        ]);
    }
}
