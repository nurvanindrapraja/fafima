<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Family;
use Illuminate\Support\Str;

class FamilyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $family = Family::create([
            'name' => $request->name,
            'code' => strtoupper(Str::random(8)),
        ]);

        $request->user()->update([
            'family_id' => $family->id,
            'role' => 'owner',
        ]);

        return redirect()->route('dashboard');
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $family = Family::where('code', strtoupper($request->code))->first();

        if (!$family) {
            return back()->withErrors(['code' => 'Kode Keluarga tidak ditemukan.']);
        }

        $request->user()->update([
            'family_id' => $family->id,
            'role' => 'member',
        ]);

        return redirect()->route('dashboard');
    }
}
