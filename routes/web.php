<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TargetController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/invite/{code}', function ($code) {
    session(['invitation_code' => $code]);
    
    if (Auth::check()) {
        return redirect()->route('onboarding.index');
    }
    return redirect()->route('register');
})->name('family.invite');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Onboarding (No family required)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/family/create', [FamilyController::class, 'store'])->name('family.create');
    Route::post('/family/join', [FamilyController::class, 'join'])->name('family.join');

    // App core (Family required)
    Route::middleware('family')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Transactions
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Targets
        Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');

        // Family Settings & Members
        Route::get('/family/settings', function () {
            return view('family.settings');
        })->name('family.settings');
    });
});

require __DIR__.'/auth.php';
