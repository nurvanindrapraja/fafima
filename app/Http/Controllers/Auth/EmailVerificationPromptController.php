<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $url = $request->user()->role === 'admin'
            ? route('admin.users.index', absolute: false)
            : route('dashboard', absolute: false);

        return $request->user()->hasVerifiedEmail()
                    ? redirect($url)
                    : view('auth.verify-email');
    }
}
