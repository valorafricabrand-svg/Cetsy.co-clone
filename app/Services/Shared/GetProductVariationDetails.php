<?php

namespace App\Services\Shared;

use App\Models\Product\Product;

class GetProductVariationDetails
{

    public $productId;
    public $shipToCountryId;
    public function __construct(int $productId, int $shipToCountryId)
    {

        $this->productId = $productId;
        $this->shipToCountryId = $shipToCountryId;
    }

    public function execute()
    {

        $product = Product::where('id', $this->productId)->first();

        $shippingFee = $product->local_shipping_price;

        if ($product->origin_id != $this->shipToCountryId) {
            $shippingFee = $product->default_shipping_price;
        }

        return number_format($shippingFee, 2);
    }
}
