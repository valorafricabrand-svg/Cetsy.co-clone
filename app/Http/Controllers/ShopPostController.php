<?php

namespace App\Http\Controllers;

use App\Models\ShopPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Activity;

class ShopPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shop = Auth::user()->shop;
        $shopPosts = ShopPost::where('shop_id', $shop->id)->get();
        return view('shops.posts.index', compact('shopPosts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shops.posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'image'        => 'nullable|image|max:2048',
            'status'       => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'expired_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data = $request->only(['title', 'description', 'status', 'published_at', 'expired_at']);
        $data['shop_id'] = auth()->user()->shop->id ?? null;

        DB::beginTransaction();
        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('shop_posts', 'public');
            }

            $shopPost = ShopPost::create($data);

            // Create activity record for the seller
            Activity::create([
                'user_id' => Auth::id(),
                'is_read' => false,
                'description' => 'You created a new shop post',
                'type' => \App\Models\Activity::TYPE_SHOP_POST,
                'related_id' => $shopPost->id,
                'related_type' => 'shop_post'
            ]);

            DB::commit();
            return redirect()
                ->route('seller.shop-posts.index')
                ->with('success', 'Shop post created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create shop post. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ShopPost $shopPost)
    {
        return view('shops.posts.show', compact('shopPost'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShopPost $shopPost)
    {
        return view('shops.posts.edit', compact('shopPost'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShopPost $shopPost)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'image'        => 'nullable|image|max:2048',
            'status'       => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'expired_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data = $request->only(['title', 'description', 'status', 'published_at', 'expired_at']);

        DB::beginTransaction();
        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($shopPost->image && Storage::disk('public')->exists($shopPost->image)) {
                    Storage::disk('public')->delete($shopPost->image);
                }
                $data['image'] = $request->file('image')->store('shop_posts', 'public');
            }

            $shopPost->update($data);

            DB::commit();
            return redirect()
                ->route('seller.shop-posts.index')
                ->with('success', 'Shop post updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update shop post. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShopPost $shopPost)
    {
        DB::beginTransaction();
        try {
            if ($shopPost->image && Storage::disk('public')->exists($shopPost->image)) {
                Storage::disk('public')->delete($shopPost->image);
            }
            $shopPost->delete();
            DB::commit();
            return redirect()
                ->route('seller.shop-posts.index')
                ->with('success', 'Shop post deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete shop post. Please try again.');
        }
    }
}
