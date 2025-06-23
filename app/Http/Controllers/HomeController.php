<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with top‐level categories and latest products.
     */
public function index()
{
    // Top-level categories (no parent)
    $categories = Category::whereNull('parent_id')
                          ->orderBy('name')
                          ->get();

    // Latest 8 active physical products
    $featuredProducts = Product::where('is_active', 1)
                               ->where('type', 'physical')
                               ->latest()
                               ->with('media')
                               ->take(8)
                               ->get();

    // Latest 8 active service products
    $services = Product::where('is_active', 1)
                       ->where('type', 'service')
                       ->latest()
                       ->with('media')
                       ->take(8)
                       ->get();

    // Latest 8 active shops
    $shops = Shop::latest()
                 ->take(8)
                 ->get();

    return themed_view('index', compact('categories', 'featuredProducts', 'shops', 'services'));
}

}
