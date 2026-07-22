<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Family;
use App\Models\User;
use App\Models\Limit;
use App\Models\Transaction;
use App\Notifications\LimitWarningNotification;
use Carbon\Carbon;

class FafimaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fafima:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek limit pengeluaran dan kirim notifikasi ke user via WebPush';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking limits...');
        $month = now()->month;
        $year = now()->year;

        // Cek Limit Keluarga
        $familyLimits = Limit::whereNull('user_id')->get();
        foreach ($familyLimits as $limit) {
            $familyId = $limit->family_id;
            
            $income = Transaction::where('family_id', $familyId)
                ->where('type', 'income')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');
                
            $expense = Transaction::where('family_id', $familyId)
                ->where('type', 'expense')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $limitAmount = $limit->limit_type === 'percentage' 
                ? ($income * ($limit->percentage / 100)) 
                : $limit->amount;

            if ($limitAmount > 0) {
                $pct = ($expense / $limitAmount) * 100;
                
                if ($pct >= 90) {
                    $owner = User::where('family_id', $familyId)->where('role', 'owner')->first();
                    if ($owner) {
                        $status = $pct >= 100 ? 'melebihi' : 'hampir melebihi';
                        $title = "Peringatan Limit Keluarga";
                        $message = "Pengeluaran keluarga saat ini {$status} limit bulan ini (" . round($pct, 1) . "%).";
                        
                        $owner->notify(new LimitWarningNotification($title, $message, '/dashboard'));
                    }
                }
            }
        }

        // Cek Limit Individu
        $userLimits = Limit::whereNotNull('user_id')->get();
        foreach ($userLimits as $limit) {
            $userId = $limit->user_id;
            $familyId = $limit->family_id;
            
            $income = Transaction::where('family_id', $familyId)
                ->where('type', 'income')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');
                
            $expense = Transaction::where('family_id', $familyId)
                ->where('user_id', $userId)
                ->where('type', 'expense')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $limitAmount = $limit->limit_type === 'percentage' 
                ? ($income * ($limit->percentage / 100)) 
                : $limit->amount;

            if ($limitAmount > 0) {
                $pct = ($expense / $limitAmount) * 100;
                
                if ($pct >= 90) {
                    $user = User::find($userId);
                    if ($user) {
                        $status = $pct >= 100 ? 'melebihi' : 'hampir melebihi';
                        $title = "Peringatan Limit Pribadi";
                        $message = "Pengeluaran Anda saat ini {$status} limit pribadi bulan ini (" . round($pct, 1) . "%).";
                        
                        $user->notify(new LimitWarningNotification($title, $message, '/dashboard'));
                    }
                }
            }
        }

        $this->info('Reminders sent.');
    }
}
