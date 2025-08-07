<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShippingProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductShippingController extends Controller
{
    /**
     * Add one shipping row (modal “Add”).
     */
    public function storeShippingRow(Product $product, Request $request)
    {
        $shopId = $product->shop_id ?? optional($request->user())->shop_id;
        abort_if(! $shopId, 403, 'Shop not resolved for this product.');

        // Profile-level fields (inherit on every row)
        $profileName      = $request->input('profile_name', 'Standard shipping');
        $isDefault        = (bool) $request->input('set_default', false);
        $countryId        = $request->input('country_id');
        $originPostal     = $request->input('origin_postal_code');
        $processingTimeId = $request->input('processing_time_id');
        $processingMin    = $request->input('processing_custom_min');
        $processingMax    = $request->input('processing_custom_max');

        // Row payload
        $row              = $request->input('row', []);
        $locationType     = $row['location_type']  ?? 'country';          // country | everywhere_else
        $destCountryId    = $locationType === 'country' ? ($row['country_id'] ?? null) : null;
        $service          = $row['service']        ?? 'Other';
        $daysMin          = $row['days_min']       ?? null;
        $daysMax          = $row['days_max']       ?? null;
        $chargeType       = $row['charge_type']    ?? 'fixed';            // fixed | free
        $baseRate         = $chargeType === 'free' ? 0 : (float) ($row['price_one']       ?? 0);
        $additionalRate   = $chargeType === 'free' ? 0 : (float) ($row['price_additional']?? 0);

        $profile = ShippingProfile::create([
            'shop_id'               => $shopId,
            'product_id'            => $product->id,
            'profile_name'          => $profileName,
            'name'                  => $profileName,
            'is_default'            => $isDefault,

            'country_id'            => $countryId,
            'origin_postal_code'    => $originPostal,

            'processing_time_id'    => $processingTimeId,
            'processing_custom_min' => $processingMin,
            'processing_custom_max' => $processingMax,

            'dest_location_type'    => $locationType,     // NEW ENUM
            'dest_country_id'       => $destCountryId,

            'service'               => $service,
            'days_min'              => $daysMin,
            'days_max'              => $daysMax,

            'charge_type'           => $chargeType,
            'base_rate'             => $baseRate,
            'additional_rate'       => $additionalRate,
        ]);

        // Keep only one default row per product
        if ($isDefault && Schema::hasColumn('shipping_profiles', 'is_default')) {
            ShippingProfile::where('shop_id', $shopId)
                ->where('product_id', $product->id)
                ->where('id', '!=', $profile->id)
                ->update(['is_default' => false]);
        }

        return back()->with('success', 'Shipping row added.');
    }

    /**
     * Update one shipping row (modal “Edit”).
     */
    public function updateShippingRow(Product $product, ShippingProfile $row, Request $request)
    {
        $shopId = $product->shop_id ?? optional($request->user())->shop_id;
        abort_if(! $shopId || $row->shop_id !== $shopId || $row->product_id !== $product->id, 404);

        $payload         = $request->input('row', []);
        $locationType    = $payload['location_type'] ?? $row->dest_location_type;
        $destCountryId   = $locationType === 'country' ? ($payload['country_id'] ?? null) : null;
        $chargeType      = $payload['charge_type']    ?? $row->charge_type;
        $baseRate        = $chargeType === 'free' ? 0 : (float) ($payload['price_one']        ?? $row->base_rate);
        $additionalRate  = $chargeType === 'free' ? 0 : (float) ($payload['price_additional'] ?? $row->additional_rate);

        $row->update([
            'dest_location_type' => $locationType,
            'dest_country_id'    => $destCountryId,
            'service'            => $payload['service']   ?? $row->service,
            'days_min'           => $payload['days_min']  ?? $row->days_min,
            'days_max'           => $payload['days_max']  ?? $row->days_max,
            'charge_type'        => $chargeType,
            'base_rate'          => $baseRate,
            'additional_rate'    => $additionalRate,
        ]);

        return back()->with('success', 'Shipping row updated.');
    }

    /**
     * Delete one shipping row.
     */
    public function destroyShippingRow(Product $product, ShippingProfile $row)
    {
        $shopId = $product->shop_id ?? optional(request()->user())->shop_id;
        abort_if($row->shop_id !== $shopId || $row->product_id !== $product->id, 404);

        $row->delete();
        return back()->with('success', 'Shipping row deleted.');
    }
}
