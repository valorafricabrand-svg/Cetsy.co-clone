<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class InventoryReportController extends Controller
{
    public function index(): View
    {
        $shopId = optional(Auth::user()->shop)->id;

        $productsNegative = collect();
        $variantsNegative = collect();
        $nonPhysicalWithStock = collect();
        $physicalUntracked = collect();
        $activeButOut = collect();

        if ($shopId) {
            $productsNegative = Product::where('shop_id', $shopId)
                ->whereNotNull('stock')
                ->where('stock', '<', 0)
                ->orderBy('id', 'desc')
                ->get();

            $variantsNegative = Variant::whereHas('product', fn($q) => $q->where('shop_id', $shopId))
                ->whereNotNull('stock')
                ->where('stock', '<', 0)
                ->orderBy('id', 'desc')
                ->get();

            $nonPhysicalWithStock = Product::where('shop_id', $shopId)
                ->whereIn('type', ['digital','service'])
                ->whereNotNull('stock')
                ->where('stock', '>', 0)
                ->orderBy('id', 'desc')
                ->get();

            $physicalUntracked = Product::with('variations')
                ->where('shop_id', $shopId)
                ->where('type', 'physical')
                ->whereNull('stock')
                ->doesntHave('variations')
                ->orderBy('id', 'desc')
                ->get();

            $activePhysical = Product::with('variations')
                ->where('shop_id', $shopId)
                ->where('type', 'physical')
                ->where('is_active', 1)
                ->orderBy('id', 'desc')
                ->get();
            $activeButOut = $activePhysical->filter(function (Product $p) {
                $variantStocks = optional($p->variations)->pluck('stock')->filter(fn($v)=>$v!==null)->map(fn($v)=>(int)$v);
                if ($variantStocks && $variantStocks->count()>0) {
                    return $variantStocks->sum() <= 0;
                }
                if ($p->stock === null) return false; // unlimited
                return ((int)$p->stock) <= 0;
            })->values();
        }

        return view('seller.reports.inventory', compact(
            'shopId',
            'productsNegative',
            'variantsNegative',
            'nonPhysicalWithStock',
            'physicalUntracked',
            'activeButOut'
        ));
    }
}

