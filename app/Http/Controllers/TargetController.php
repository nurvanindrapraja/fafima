<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Target;
use Illuminate\Support\Facades\Auth;

class TargetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $targets = Target::with(['approvals.user'])
            ->where('family_id', $user->family_id)
            ->orderByDesc('created_at')
            ->get();
        return view('targets.index', compact('targets'));
    }
}
