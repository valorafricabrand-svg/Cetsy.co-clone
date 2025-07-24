{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.frontapp')

@section('title','Your Cart')

@section('main')
<section class="cart-page py-5 bg-light" x-data>
  <div class="container">
    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{!! session('success') !!}</div> @endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

    @php $cart = session('cart', []); @endphp

    {{-- EMPTY CART -------------------------------------------------------------- --}}
    @if(empty($cart))
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>

    {{-- POPULATED CART ---------------------------------------------------------- --}}
    @else
      <form method="POST" action="{{ route('cart.updateShippingSelection') }}">
        @csrf

        <div class="table-responsive mb-4">
          <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th>Variation</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Shipping Profile</th>
                <th>Line&nbsp;Total</th>
                <th>Remove</th>
              </tr>
            </thead>

            <tbody>
            @foreach($cart as $item)
              @php
                // Handle legacy/new keys safely
                $productId             = $item['product_id'] ?? $item['id'] ?? null;
                $productVariationId    = $item['product_variation_id'] ?? $item['variation_id'] ?? null;

                // Build a consistent row identifier
                $rowId = $item['row_id']
                      ?? ($productId && $productVariationId ? $productId.'-'.$productVariationId : ($productId ?? uniqid('row_')));

                $profiles  = collect($item['shipping_profiles'] ?? []);
                $selected  = $profiles->firstWhere('id', $item['selected_shipping_profile_id'] ?? null);
                $rate      = $selected['base_rate'] ?? 0;
                $unitPrice = $item['price'] ?? 0;
                $qty       = $item['quantity'] ?? 1;
                $lineTotal = ($unitPrice + $rate) * $qty;

                $photo     = $item['photo'] ?? null;
                $photoUrl  = $photo
                              ? (filter_var($photo, FILTER_VALIDATE_URL) ? $photo : asset('storage/'.$photo))
                              : null;
              @endphp

              <tr>
                {{-- Product & thumbnail --}}
                <td class="text-start">
                  <div class="d-flex align-items-center gap-3">
                    @if($photoUrl)
                      <img src="{{ $photoUrl }}" alt="" width="60" height="60" class="rounded object-fit-cover">
                    @endif
                    <span class="fw-semibold">{{ $item['name'] ?? 'Item' }}</span>
                  </div>
                </td>

                {{-- Variation --}}
                <td>{{ $item['variation'] ?? '—' }}</td>

                {{-- Unit price --}}
                <td>{{ get_currency() }} {{ number_format($unitPrice, 2) }}</td>

                {{-- Quantity controls --}}
                <td>
                  <div class="d-flex justify-content-center align-items-center gap-1">
                    {{-- – --}}
                    <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                      @csrf
                      <input type="hidden" name="row_id" value="{{ $rowId }}">
                      <input type="hidden" name="action" value="decrease">
                      <button type="submit" class="btn btn-sm btn-outline-secondary" @disabled($qty <= 1)">–</button>
                    </form>

                    <span class="px-2">{{ $qty }}</span>

                    {{-- + --}}
                    <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                      @csrf
                      <input type="hidden" name="row_id" value="{{ $rowId }}">
                      <input type="hidden" name="action" value="increase">
                      <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                    </form>
                  </div>
                </td>

                {{-- Shipping profile selector --}}
                <td>
                  @php
                    $ordered = $selected
                               ? collect([$selected])->merge($profiles->where('id','!=',$selected['id']))
                               : $profiles;
                  @endphp

                  <select name="shipping_profile_ids[{{ $rowId }}]" class="form-select form-select-sm">
                    @foreach($ordered as $profile)
                      <option value="{{ $profile['id'] }}"
                              @selected($profile['id'] == ($item['selected_shipping_profile_id'] ?? null))>
                        {{ $profile['name'] }} ({{ get_currency() }} {{ number_format($profile['base_rate'],2) }})
                      </option>
                    @endforeach
                  </select>
                </td>

                {{-- Line total --}}
                <td>{{ get_currency() }} {{ number_format($lineTotal, 2) }}</td>

                {{-- Remove --}}
                <td>
                  <form action="{{ route('cart.remove') }}" method="POST">
                    @csrf
                    <input type="hidden" name="row_id" value="{{ $rowId }}">
                    <button type="submit" class="btn btn-sm btn-danger">&times;</button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>

            {{-- Cart totals --}}
            <tfoot class="table-light">
              @php
                $grand = collect($cart)->sum(function($i){
                    $profiles = collect($i['shipping_profiles'] ?? []);
                    $rate     = $profiles->firstWhere('id', $i['selected_shipping_profile_id'] ?? null)['base_rate'] ?? 0;
                    return (($i['price'] ?? 0) + $rate) * ($i['quantity'] ?? 1);
                });
              @endphp
              <tr>
                <th colspan="5" class="text-end">Total:</th>
                <th colspan="2">{{ get_currency() }} {{ number_format($grand, 2) }}</th>
              </tr>
            </tfoot>
          </table>
        </div>

        {{-- redirect after saving shipping choices --}}
        <input type="hidden" name="return_to" value="checkout.show">

        <div class="d-flex justify-content-between">
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Continue Shopping</a>
          <div>
            <button type="submit" class="btn btn-outline-primary me-2">
              Update&nbsp;Shipping
            </button>
            <a href="{{ route('cart.checkout') }}" class="btn btn-primary">
              Proceed&nbsp;to&nbsp;Checkout
            </a>
          </div>
        </div>
      </form>
    @endif
  </div>
</section>
@endsection
