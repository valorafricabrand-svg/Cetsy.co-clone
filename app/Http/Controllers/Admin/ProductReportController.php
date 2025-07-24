<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductReport::with(['product', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('id', 'desc')->paginate(20);

        // Get status counts for filter
        $statusCounts = ProductReport::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.product-reports.index', compact('reports', 'statusCounts'));
    }

    /**
     * Update report status.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $report = ProductReport::find($id);
        if (!$report) {
            return redirect()->route('admin.product-reports.index')->with('error', 'Report not found.');
        }

        $report->status = $data['status'];
        $report->admin_notes = $data['admin_notes'] ?? null;
        $report->action_by = Auth::id();
        $report->action_at = now();
        $report->save();


        

        return back()->with('success', 'Report status updated successfully.');
    }
}
