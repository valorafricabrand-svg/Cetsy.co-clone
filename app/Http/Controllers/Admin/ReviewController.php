<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of all reviews.
     */
    public function index(Request $request)
    {
        $reviews = Review::with(['shop', 'user', 'order'])
            ->orderByDesc('id')
            ->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }
    public function destroy($id)
{
    $review = \App\Models\Review::findOrFail($id);
    $review->delete();

    return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
}
} 