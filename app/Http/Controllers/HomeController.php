<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with top‐level categories and latest products.
     */
    public function index()
    {
        // Top‐level categories (no parent)
        $categories = Category::whereNull('parent_id')
                              ->orderBy('name')
                              ->get();

        // Latest 8 active products
        $featuredProducts = Product::where('is_active', '1')
                                   ->latest()
                                   ->with('media')
                                   ->take(8)
                                   ->get();

        return view('theme.index', compact('categories', 'featuredProducts'));
    }
}
