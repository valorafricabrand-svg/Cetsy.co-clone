<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethods = PaymentMethod::where('shop_id', $shop->id)
            ->with('paymentType')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('seller.payment-methods.index', compact('paymentMethods','shop'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paymentTypes = PaymentType::where('status', 'active')->get();
        return view('seller.payment-methods.create', compact('paymentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        
        $validated = $request->validate([
            'payment_type_id' => [
                'required',
                'exists:payment_types,id',
                function ($attribute, $value, $fail) use ($shop) {
                    // Check if seller already has a payment method with this payment type
                    $existingPaymentMethod = PaymentMethod::where('shop_id', $shop->id)
                        ->where('payment_type_id', $value)
                        ->first();
                    
                    if ($existingPaymentMethod) {
                        $paymentType = PaymentType::find($value);
                        $fail("You already have a payment method for {$paymentType->name}. Please edit the existing one instead.");
                    }
                }
            ],
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        $validated['shop_id'] = $shop->id;

        PaymentMethod::create($validated);

        // If a redirect target is provided (e.g., from wallet modal), honor it
        $redirectTo = $request->input('redirect_to');
        if ($redirectTo) {
            return redirect($redirectTo)
                ->with('success', 'Payment method created successfully.')
                ->with('open_payout_modal', (bool) $request->boolean('open_payout'));
        }

        return redirect()
            ->route('seller.payment-methods.index')
            ->with('success', 'Payment method created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', $shop->id)
            ->with('paymentType')
            ->findOrFail($id);
            
        return view('seller.payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', $shop->id)->findOrFail($id);
        $paymentTypes = PaymentType::where('status', 'active')->get();
        
        return view('seller.payment-methods.edit', compact('paymentMethod', 'paymentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', $shop->id)->findOrFail($id);
        
        $validated = $request->validate([
            'payment_type_id' => [
                'required',
                'exists:payment_types,id',
                function ($attribute, $value, $fail) use ($shop, $paymentMethod) {
                    // Check if seller already has another payment method with this payment type
                    $existingPaymentMethod = PaymentMethod::where('shop_id', $shop->id)
                        ->where('payment_type_id', $value)
                        ->where('id', '!=', $paymentMethod->id) // Exclude current payment method
                        ->first();
                    
                    if ($existingPaymentMethod) {
                        $paymentType = PaymentType::find($value);
                        $fail("You already have a payment method for {$paymentType->name}. Please edit the existing one instead.");
                    }
                }
            ],
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        $paymentMethod->update($validated);

        return redirect()
            ->route('seller.payment-methods.index')
            ->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', $shop->id)->findOrFail($id);
        
        $paymentMethod->delete();

        return redirect()
            ->route('seller.payment-methods.index')
            ->with('success', 'Payment method deleted successfully.');
    }
} 
