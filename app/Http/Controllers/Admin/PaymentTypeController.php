<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentType;

class PaymentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentTypes = PaymentType::orderBy('id', 'desc')->paginate(10);
        return view('admin.payment-types.index', compact('paymentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.payment-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('payment-types', 'public');
            $validated['image'] = $path;
        }

        // dd($validated);

        // Create the payment type
        PaymentType::create($validated);

        return redirect()
            ->route('admin.payment-types.index')
            ->with('success', 'Payment type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paymentType = PaymentType::findOrFail($id);
        return view('admin.payment-types.show', compact('paymentType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $paymentType = PaymentType::findOrFail($id);
        return view('admin.payment-types.edit', compact('paymentType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paymentType = PaymentType::findOrFail($id);
        
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name,' . $id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($paymentType->image) {
                \Storage::disk('public')->delete($paymentType->image);
            }
            
            $path = $request->file('image')->store('payment-types', 'public');
            $validated['image'] = $path;
        }

        // Update the payment type
        $paymentType->update($validated);

        return redirect()
            ->route('admin.payment-types.index')
            ->with('success', 'Payment type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentType = PaymentType::findOrFail($id);
        // Delete the image if exists
        if ($paymentType->image) {
            \Storage::disk('public')->delete($paymentType->image);
        }
        // Delete the payment type
        $paymentType->delete();
        return redirect()
            ->route('admin.payment-types.index')
            ->with('success', 'Payment type deleted successfully.');
    }
}
