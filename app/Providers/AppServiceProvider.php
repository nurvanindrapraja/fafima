<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                $event->user->update([
                    'last_login_at' => now()
                ]);
            }
        );
        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Verifikasi Alamat Email Anda')
                ->greeting('Halo!')
                ->line('Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.')
                ->action('Verifikasi Alamat Email', $url)
                ->line('Jika Anda tidak membuat akun, tidak ada tindakan lebih lanjut yang diperlukan.')
                ->line('---')
                ->line('FaFiMa (Family Finance Management) adalah aplikasi untuk mengelola keuangan keluarga maupun kelompok, seperti tim kerja, organisasi, komunitas, dan kepanitiaan, dengan pencatatan pemasukan, pengeluaran, anggaran, serta laporan keuangan yang mudah dipahami.');
        });
    }
}
