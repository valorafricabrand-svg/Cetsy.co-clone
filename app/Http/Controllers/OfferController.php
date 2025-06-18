<?php
// app/Http/Controllers/OfferController.php
namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => ['required','exists:products,id'],
            'offer_price' => ['required','numeric','min:1'],
        ]);

        $offer = Offer::updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'buyer_id'   => $request->user()->id,
            ],
            [
                'offer_price' => $data['offer_price'],
                'status'      => 'pending',
            ]
        );

        return back()->with('success', 'Offer submitted! The seller will review it soon.');
    }
}
