<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->family_id) {
            return redirect()->route('dashboard');
        }
        
        return view('onboarding');
    }
}
