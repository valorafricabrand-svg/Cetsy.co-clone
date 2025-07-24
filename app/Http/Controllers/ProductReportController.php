<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReportController extends Controller
{
    /**
     * Store a new product report.
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Please log in to report a listing.');
        }

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'reason' => 'required|in:inappropriate,counterfeit,spam,misleading,other',
            'description' => 'required|string|max:1000',
        ]);

        $report = ProductReport::create([
            'product_id' => $data['product_id'],
            'user_id' => Auth::id(),
            'reason' => $data['reason'],
            'description' => $data['description'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thank you for your report. We will review it within 48-72 hours.');
    }

    /**
     * Show all reports for admin review.
     */
    public function index()
    {
        $reports = ProductReport::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.product-reports.index', compact('reports'));
    }

    /**
     * Update report status (admin only).
     */
    public function update(Request $request, ProductReport $report)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $report->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'action_by' => Auth::id(),
            'action_at' => now(),
        ]);

        return back()->with('success', 'Report status updated successfully.');
    }
} 