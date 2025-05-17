<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
   
    /**
     * Show the “Create Your Shop” form (or redirect if exists).
     */
    public function create()
    {
        if (Auth::user()->shop) {
            return redirect()->route('shops.show', Auth::user()->shop);
        }

        return view('shops.create');
    }

    /**
     * Persist a new shop.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:shops,slug',
            'bio'  => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Auto-slug if blank
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            // Guarantee uniqueness
            if (Shop::where('slug', $data['slug'])->exists()) {
                $data['slug'] .= '-' . time();
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                                 ->store('shops/logos', 'public');
        }

        // Create shop via the one-to-one relationship
        $shop = Auth::user()->shop()->create($data);

        return redirect()
            ->route('shops.show', $shop)
            ->with('success', 'Your shop has been created!');
    }

    /**
     * Public view of a single shop (by slug).
     */
    public function show(Shop $shop)
    {
        return view('shops.show', compact('shop'));
    }
}
