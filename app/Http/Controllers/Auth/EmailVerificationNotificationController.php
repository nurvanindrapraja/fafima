<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $url = $request->user()->role === 'admin'
                ? route('admin.users.index', absolute: false)
                : route('dashboard', absolute: false);

            return $request->wantsJson()
                ? response()->json(['message' => 'Already verified'], 200)
                : redirect()->intended($url);
        }

        $request->user()->sendEmailVerificationNotification();

        return $request->wantsJson()
            ? response()->json(['status' => 'verification-link-sent'], 200)
            : back()->with('status', 'verification-link-sent');
    }
}
