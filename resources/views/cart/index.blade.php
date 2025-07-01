{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.frontapp')

@section('title', 'Your Cart')

@section('main')
<section class="cart-page py-5 bg-light" x-data>
  <div class="container">
    @if(session('success'))
      <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php $cart = session('cart', []); @endphp

    @if(empty($cart))
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>
    @else
      <form method="POST" action="{{ route('cart.updateShippingSelection') }}">
        @csrf

        <div class="table-responsive mb-4">
          <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Shipping</th>
                <th>Subtotal</th>
                <th>Remove</th>
              </tr>
            </thead>
            <tbody>
              @foreach($cart as $item)
                @php
                  // find the selected profile or default to a zero-rate placeholder
                  $selectedProfile = collect($item['shipping_profiles'])
                                      ->firstWhere('id', $item['selected_shipping_profile_id']);
                  $shippingRate = $selectedProfile['base_rate'] ?? 0;
                  $lineTotal    = ($item['price'] + $shippingRate) * $item['quantity'];
                @endphp

                <tr>
                  <td class="d-flex align-items-center gap-3">
                    @if(! empty($item['photo']))
                      <img src="{{ $item['photo'] }}" alt="" width="60" height="60" class="rounded">
                    @endif
                    <span>{{ $item['name'] }}</span>
                  </td>
                  <td>KES {{ number_format($item['price'], 2) }}</td>
                  <td>
                    <div class="d-flex justify-content-center align-items-center gap-1">
                      <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item['id'] }}">
                        <input type="hidden" name="action" value="decrease">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" @if($item['quantity'] <= 1) disabled @endif>–</button>
                      </form>

                      <span class="px-2">{{ $item['quantity'] }}</span>

                      <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item['id'] }}">
                        <input type="hidden" name="action" value="increase">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                      </form>
                    </div>
                  </td>
                  <td>
                 @php
    // Turn the raw array into a collection
    $profiles = collect($item['shipping_profiles']);

    // Pull out the selected profile
    $selected = $profiles->firstWhere('id', $item['selected_shipping_profile_id']);

    // Everything else
    $others   = $profiles->where('id', '!=', $item['selected_shipping_profile_id']);

    // Build an ordered list: selected first (if it exists), then the rest
    $ordered  = $selected
               ? collect([$selected])->merge($others)
               : $profiles;
@endphp

<select name="shipping_profile_ids[{{ $item['id'] }}]"
        class="form-select form-select-sm">
  @foreach($ordered as $profile)
    <option value="{{ $profile['id'] }}"
      @if($profile['id'] === $item['selected_shipping_profile_id']) selected @endif>
      {{ $profile['name'] }} (KES {{ number_format($profile['base_rate'], 2) }})
    </option>
  @endforeach
</select>

                  </td>
                  <td>KES {{ number_format($lineTotal, 2) }}</td>
                  <td>
                    <form action="{{ route('cart.remove') }}" method="POST">
                      @csrf
                      <input type="hidden" name="id" value="{{ $item['id'] }}">
                      <button type="submit" class="btn btn-sm btn-danger">&times;</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>

            <tfoot class="table-light">
              <tr>
                <th colspan="4" class="text-end">Total:</th>
                <th colspan="2">
                  @php
                    $total = collect($cart)->sum(function($item) {
                      $profile    = collect($item['shipping_profiles'])
                                      ->firstWhere('id', $item['selected_shipping_profile_id']);
                      $rate       = $profile['base_rate'] ?? 0;
                      return ($item['price'] + $rate) * $item['quantity'];
                    });
                  @endphp
                  KES {{ number_format($total, 2) }}
                </th>
              </tr>
            </tfoot>
          </table>
        </div>

        <input type="hidden" name="return_to" value="checkout.show">

        <div class="d-flex justify-content-between">
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Continue Shopping</a>
          <div>
            <button type="submit" class="btn btn-outline-primary me-2">Update Shipping</button>
            <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
          </div>
        </div>
      </form>
    @endif
  </div>
</section>
@endsection
