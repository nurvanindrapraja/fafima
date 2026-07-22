<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $categories = Category::where(function ($q) use ($user) {
            $q->where('family_id', $user->family_id)->orWhereNull('family_id');
        })->orderBy('type')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
        ]);

        $user = Auth::user();

        Category::create([
            'family_id'  => $user->family_id,
            'name'       => $request->name,
            'type'       => $request->type,
            'created_by' => $user->id,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function destroy(Category $category)
    {
        $user = Auth::user();

        // Only family member can delete
        if ($category->family_id !== $user->family_id) {
            abort(403);
        }

        // Cannot delete if used in transactions
        if ($category->transactions()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena sudah digunakan di transaksi.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
