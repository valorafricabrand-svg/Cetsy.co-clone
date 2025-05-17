<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the user's shopping cart.
     */
    public function index()
    {
        // For now, we'll just show an empty cart.
        // You can later pull from session or database.
        return view('cart.index');
    }
}
