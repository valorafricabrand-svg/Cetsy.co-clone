<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Contracts\View\View;

class InventoryReportController extends Controller
{
    public function index(): View
    {
        // Critical issues
        $productsNegative = Product::with(['shop'])
            ->whereNotNull('stock')
            ->where('stock', '<', 0)
            ->orderBy('id', 'desc')
            ->get();

        $variantsNegative = Variant::with(['product.shop'])
            ->whereNotNull('stock')
            ->where('stock', '<', 0)
            ->orderBy('id', 'desc')
            ->get();

        // Warnings / hygiene
        $nonPhysicalWithStock = Product::with('shop')
            ->whereIn('type', ['digital', 'service'])
            ->whereNotNull('stock')
            ->where('stock', '>', 0)
            ->orderBy('id', 'desc')
            ->get();

        $physicalUntracked = Product::with('shop')
            ->where('type', 'physical')
            ->whereNull('stock')
            ->doesntHave('variations')
            ->orderBy('id', 'desc')
            ->get();

        // Active but effectively out of stock (computed)
        $activePhysical = Product::with(['variations', 'shop'])
            ->where('type', 'physical')
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->get();

        $activeButOut = $activePhysical->filter(function (Product $p) {
            // Sum variant stocks if any numeric stock is set; else use product stock
            $variantStocks = optional($p->variations)->pluck('stock')->filter(fn($v) => $v !== null)->map(fn($v) => (int) $v);
            if ($variantStocks && $variantStocks->count() > 0) {
                $total = $variantStocks->sum();
                return $total <= 0;
            }
            // If product stock is null -> unlimited => not out of stock
            if ($p->stock === null) return false;
            return ((int) $p->stock) <= 0;
        })->values();

        return view('admin.reports.inventory', compact(
            'productsNegative',
            'variantsNegative',
            'nonPhysicalWithStock',
            'physicalUntracked',
            'activeButOut'
        ));
    }
}

