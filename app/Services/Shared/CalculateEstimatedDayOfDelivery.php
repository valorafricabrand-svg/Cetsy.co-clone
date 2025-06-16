<?php

namespace App\Services\Shared;

use Carbon\Carbon;
use App\Models\ProcessingTime;
use App\Models\Product\Product;

class CalculateEstimatedDayOfDelivery
{

    public $productId;
    public $shipToCountryId;

    public function __construct( $productId, $shipToCountryId)
    {

        $this->productId = $productId;
        $this->shipToCountryId = $shipToCountryId;
    }

    public function execute()
    {
        $product = Product::where('id', $this->productId)->first();

        $processingTime = ProcessingTime::where('id', $product->processing_time_id)->first();


        $currentDate = Carbon::today();
        $orderShipLowestEstimate = $currentDate->addDays($processingTime?->start_day);
        $currentDate = Carbon::today();
        $orderShipUpperEstimate = $currentDate->addDays($processingTime?->end_day);


        $delivery_time_start = $product->delivery_time_start;
        $delivery_time_end = $product->delivery_time_end;
        if ($product->origin_id != $this->shipToCountryId) {
            $delivery_time_start = $product->delivery_time_start_other;
            $delivery_time_end = $product->delivery_time_end_other;
        }

        $totalDaysToDeliveryBegin = $processingTime?->start_day + $delivery_time_start;

        $totalDaysToDeliveryEnd = $processingTime?->end_day +  $delivery_time_end;


        $currentDate = Carbon::today();
        $orderShipUpperEstimateFinal = $currentDate->addDays($processingTime?->end_day);
        $productDeliveredLowest = $orderShipUpperEstimateFinal->addDays($totalDaysToDeliveryBegin);
        $currentDate = Carbon::today();
        $orderShipUpperEstimateFinal = $currentDate->addDays($processingTime?->end_day);
        $productDeliveredUpper = $orderShipUpperEstimateFinal->addDays($totalDaysToDeliveryEnd);


        $deliveryDetails = [
            'orderPlaced' => Carbon::today()->format('F j'),
            'orderShippedBegin' => $orderShipLowestEstimate->format('F j'),
            'orderShippedEnd' => $orderShipUpperEstimate->format('F j'),
            'orderDeliveredBegin' => $productDeliveredLowest->format('F j'),
            'orderDeliveredEnd' => $productDeliveredUpper->format('F j'),

        ];

        return $deliveryDetails;
    }
}
