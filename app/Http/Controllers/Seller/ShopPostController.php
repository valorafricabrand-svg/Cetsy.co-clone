<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopPost;

class ShopPostController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function create()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function show(ShopPost $shopPost)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function edit(ShopPost $shopPost)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function update(Request $request, ShopPost $shopPost)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function destroy(ShopPost $shopPost)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}

