@extends('theme.layouts.main')

@section('main')
<style>
    .form-control, .form-select {
        background-color: #ffffff;
        color: #000000;
        border: 1px solid #ced4da;
    }
    .form-control:focus, .form-select:focus {
        background-color: #ffffff;
        color: #000000;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
    }
    .text-danger {
        font-size: 0.875rem;
    }
    @media (min-width: 992px) {
        .sticky-summary {
            position: sticky;
            top: 100px;
            z-index: 10;
        }
    }
</style>

@php
    $user = auth()->user();
@endphp

<section class="checkout-page py-5" style="margin-top: 100px;">
    <div class="container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($cart) > 0)
            <form action="{{ route('store_order') }}" method="POST" class="row gy-5 gy-lg-0" novalidate>
                @csrf

                <!-- Billing & Shipping Details -->
                <div class="col-lg-7 order-lg-1">
                    <h4 class="mb-4">Billing & Shipping Details</h4>
                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                class="form-control"
                                value="{{ old('full_name', $user->name ?? '') }}"
                                required
                            >
                            @error('full_name')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email', $user->email ?? '') }}"
                                required
                            >
                            @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                class="form-control"
                                value="{{ old('phone', $user->phone ?? '') }}"
                                required
                            >
                            @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Country -->
                        <div class="col-md-6">
                            <label for="shipping_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <select id="shipping_country" name="shipping_country" class="form-select" required>
                                <option value="">Select Country</option>
                                @foreach(\App\Models\Country::orderBy('name')->get() as $country)
                                    <option
                                        value="{{ $country->id }}"
                                        @selected(old('shipping_country', $user->country_id ?? '') == $country->id)
                                    >
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shipping_country')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Address Line 1 -->
                        <div class="col-12">
                            <label for="shipping_address_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="shipping_address_1"
                                name="shipping_address_1"
                                class="form-control"
                                value="{{ old('shipping_address_1') }}"
                                required
                            >
                            @error('shipping_address_1')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Address Line 2 -->
                        <div class="col-12">
                            <label for="shipping_address_2" class="form-label">Address Line 2</label>
                            <input
                                type="text"
                                id="shipping_address_2"
                                name="shipping_address_2"
                                class="form-control"
                                value="{{ old('shipping_address_2') }}"
                            >
                            @error('shipping_address_2')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- City -->
                        <div class="col-md-6">
                            <label for="shipping_city" class="form-label">City/Town <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="shipping_city"
                                name="shipping_city"
                                class="form-control"
                                value="{{ old('shipping_city') }}"
                                required
                            >
                            @error('shipping_city')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- State -->
                        <div class="col-md-6">
                            <label for="shipping_state" class="form-label">State/Province/Region</label>
                            <input
                                type="text"
                                id="shipping_state"
                                name="shipping_state"
                                class="form-control"
                                value="{{ old('shipping_state') }}"
                            >
                            @error('shipping_state')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Postal Code -->
                        <div class="col-md-6">
                            <label for="shipping_postal_code" class="form-label">Postal/ZIP Code</label>
                            <input
                                type="text"
                                id="shipping_postal_code"
                                name="shipping_postal_code"
                                class="form-control"
                                value="{{ old('shipping_postal_code') }}"
                            >
                            @error('shipping_postal_code')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Billing Same as Shipping -->
                        <div class="col-12 mt-3">
                            <input type="hidden" name="billing_same_as_shipping" value="0">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="billing_same_as_shipping"
                                    name="billing_same_as_shipping"
                                    value="1"
                                    {{ old('billing_same_as_shipping', '1') == '1' ? 'checked' : '' }}
                                    onchange="toggleBillingAddress(this.checked)"
                                >
                                <label class="form-check-label" for="billing_same_as_shipping">Billing address is the same as shipping address</label>
                            </div>
                        </div>

                        <!-- Billing Address Fields -->
                        <div id="billing_address_fields" style="display: {{ old('billing_same_as_shipping', '1') == '1' ? 'none' : 'block' }};">
                            <hr class="my-4">
                            <h5 class="mb-3">Billing Address</h5>

                            <div class="col-md-6">
                                <label for="billing_country" class="form-label">Billing Country</label>
                                <select id="billing_country" name="billing_country" class="form-select">
                                    <option value="">Select Country</option>
                                    @foreach(\App\Models\Country::orderBy('name')->get() as $country)
                                        <option
                                            value="{{ $country->id }}"
                                            @selected(old('billing_country') == $country->id)
                                        >
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('billing_country')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mt-2">
                                <label for="billing_address_1" class="form-label">Billing Address Line 1</label>
                                <input
                                    type="text"
                                    id="billing_address_1"
                                    name="billing_address_1"
                                    class="form-control"
                                    value="{{ old('billing_address_1') }}"
                                >
                                @error('billing_address_1')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mt-2">
                                <label for="billing_address_2" class="form-label">Billing Address Line 2</label>
                                <input
                                    type="text"
                                    id="billing_address_2"
                                    name="billing_address_2"
                                    class="form-control"
                                    value="{{ old('billing_address_2') }}"
                                >
                                @error('billing_address_2')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mt-2">
                                <label for="billing_city" class="form-label">Billing City</label>
                                <input
                                    type="text"
                                    id="billing_city"
                                    name="billing_city"
                                    class="form-control"
                                    value="{{ old('billing_city') }}"
                                >
                                @error('billing_city')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mt-2">
                                <label for="billing_state" class="form-label">Billing State</label>
                                <input
                                    type="text"
                                    id="billing_state"
                                    name="billing_state"
                                    class="form-control"
                                    value="{{ old('billing_state') }}"
                                >
                                @error('billing_state')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mt-2">
                                <label for="billing_postal_code" class="form-label">Billing Postal Code</label>
                                <input
                                    type="text"
                                    id="billing_postal_code"
                                    name="billing_postal_code"
                                    class="form-control"
                                    value="{{ old('billing_postal_code') }}"
                                >
                                @error('billing_postal_code')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Order Notes -->
                        <div class="col-12 mt-3">
                            <label for="order_notes" class="form-label">Order Notes</label>
                            <textarea id="order_notes" name="order_notes" class="form-control" rows="3">{{ old('order_notes') }}</textarea>
                            @error('order_notes')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Promo Code -->
                        <div class="col-12 mt-3">
                            <label for="promo_code" class="form-label">Promo Code</label>
                            <input type="text" id="promo_code" name="promo_code" class="form-control" value="{{ old('promo_code') }}">
                            @error('promo_code')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <!-- Order Summary (Right Side Sticky) -->
                <div class="col-lg-5 order-lg-2">
                    <div class="sticky-summary bg-white p-4 shadow-sm rounded">
                        <h4 class="mb-4">Your Order</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>Product</th><th class="text-end">Price</th></tr>
                            </thead>
                            <tbody>
                                @php
                                    $subtotal = 0;
                                    $totalShipping = 0;
                                @endphp
                                @foreach ($cart as $item)
                                    @php
                                        $shippingProfiles = $item['shipping_profiles'] ?? [];
                                        $selectedShippingProfileId = $item['selected_shipping_profile_id'] ?? null;
                                        $shippingProfile = collect($shippingProfiles)->firstWhere('id', $selectedShippingProfileId);
                                        $shippingCost = $shippingProfile ? $shippingProfile['base_rate'] * $item['quantity'] : 0;
                                        $totalShipping += $shippingCost;
                                        $subtotal += $item['price'] * $item['quantity'];
                                    @endphp
                                    <tr>
                                        <td>{{ $item['name'] }} x {{ $item['quantity'] }}</td>
                                        <td class="text-end">{{ get_currency() }} {{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                        <input type="hidden" name="product_ids[]" value="{{ $item['id'] }}">
                                        <input type="hidden" name="quantities[]" value="{{ $item['quantity'] }}">
                                        <input type="hidden" name="shipping_profile_ids[]" value="{{ $selectedShippingProfileId }}">
                                    </tr>
                                @endforeach
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td class="text-end"><strong>{{ get_currency() }} {{ number_format($subtotal, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Shipping Cost</strong></td>
                                    <td class="text-end"><strong>{{ get_currency() }} {{ number_format($totalShipping, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>{{ get_currency() }} {{ number_format($subtotal + $totalShipping, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <input type="hidden" name="subtotal" value="{{ $subtotal }}">
                        <input type="hidden" name="shipping_total" value="{{ $totalShipping }}">
                        <input type="hidden" name="total" value="{{ $subtotal + $totalShipping }}">

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Place Your Order</button>
                        </div>
                    </div>
                </div>

            </form>
        @else
            <div class="text-center py-5">
                <h2>Your cart is empty</h2>
                <p class="lead">It looks like you haven't added any products to your cart yet.</p>
                <a href="{{ route('listings') }}" class="btn btn-primary">Continue Shopping</a>
            </div>
        @endif
    </div>
</section>

<script>
    function toggleBillingAddress(checked) {
        document.getElementById('billing_address_fields').style.display = checked ? 'none' : 'block';
    }
</script>
@endsection
