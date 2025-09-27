<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()->where('type', Activity::TYPE_PRODUCT)->latest();

        if ($pid = $request->input('product_id')) {
            $query->where('related_id', (int) $pid);
        }
        if ($uid = $request->input('user_id')) {
            $query->where('user_id', (int) $uid);
        }
        if ($section = $request->input('section')) {
            $query->whereJsonContains('properties->section', $section);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $activities = $query->paginate(20)->appends($request->all());

        $productOptions = Product::orderBy('id', 'desc')->limit(50)->get(['id', 'name']);
        $userOptions    = User::orderBy('id', 'desc')->limit(50)->get(['id', 'name']);

        return view('admin.product-activities.index', compact('activities', 'productOptions', 'userOptions'));
    }

    public function show(Activity $activity)
    {
        abort_unless($activity->type === Activity::TYPE_PRODUCT, 404);
        $activity->loadMissing('user');
        $product = null;
        if (($activity->related_type ?? null) === 'product' && $activity->related_id) {
            $product = Product::find($activity->related_id);
        }
        return view('admin.product-activities.show', compact('activity', 'product'));
    }
}

