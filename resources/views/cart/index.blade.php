{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.frontapp')

@section('title','Your Cart')

@section('main')
<section class="cart-page py-5 bg-light" x-data>
  <div class="container">
    {{-- Flash messages --}}
    @if(session('success'))
      <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php $cart = session('cart', []); @endphp

    {{-- EMPTY CART --}}
    @if(count($cart) === 0)
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>

    {{-- POPULATED CART --}}
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
                  // Reliable row identifier
                  $rowId = $item['row_id'];

                  // Shipping profiles & selected
                  $profiles = collect($item['shipping_profiles'] ?? []);
                  $selectedProfile = $profiles->firstWhere('id', $item['selected_shipping_profile_id'] ?? null);
                  $rate = $selectedProfile['base_rate'] ?? 0;

                  // Prices & quantities
                  $unitPrice = $item['price'] ?? 0;
                  $qty       = $item['quantity'] ?? 1;
                  $lineTotal = ($unitPrice + $rate) * $qty;

                  // Photo URL
                  $photo = $item['photo'] ?? null;
                  $photoUrl = $photo
                    ? (filter_var($photo, FILTER_VALIDATE_URL) ? $photo : asset('storage/'.$photo))
                    : null;

                  // Variation summary
                  $summary = $item['variation_summary'] 
                           ?? (isset($item['variations']) 
                                ? collect($item['variations'])
                                    ->map(fn($v) => "{$v['type']}: {$v['value']}")
                                    ->join(', ')
                                : '—');
                @endphp

                <tr>
                  {{-- Product & thumbnail --}}
                  <td class="text-start">
                    <div class="d-flex align-items-center gap-3">
                      @if($photoUrl)
                        <img src="{{ $photoUrl }}"
                             alt=""
                             width="60"
                             height="60"
                             class="rounded object-fit-cover">
                      @endif
                      <span class="fw-semibold">{{ $item['name'] }}</span>
                    </div>
                  </td>

                  {{-- Variation --}}
                  <td>{{ $summary }}</td>

                  {{-- Unit price --}}
                  <td>{{ get_currency() }} {{ number_format($unitPrice, 2) }}</td>

                  {{-- Quantity controls --}}
                  <td>
                    <div class="d-flex justify-content-center align-items-center gap-1">
                      {{-- Decrease --}}
                      <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="row_id" value="{{ $rowId }}">
                        <input type="hidden" name="action" value="decrease">
                        <button type="submit"
                                class="btn btn-sm btn-outline-secondary"
                                @disabled($qty <= 1)
                        >–</button>
                      </form>

                      <span class="px-2">{{ $qty }}</span>

                      {{-- Increase --}}
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
                      // Selected first, then the rest
                      $ordered = $selectedProfile
                        ? collect([$selectedProfile])->merge($profiles->where('id','!=',$selectedProfile['id']))
                        : $profiles;
                    @endphp
                    <select name="shipping_profile_ids[{{ $rowId }}]"
                            class="form-select form-select-sm">
                      @foreach($ordered as $profile)
                        <option value="{{ $profile['id'] }}"
                                @selected($profile['id'] == ($item['selected_shipping_profile_id'] ?? null))>
                          {{ $profile['name'] }}
                          ({{ get_currency() }} {{ number_format($profile['base_rate'],2) }})
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
                $grandTotal = collect($cart)->sum(function($item){
                  $profiles = collect($item['shipping_profiles'] ?? []);
                  $rate = $profiles->firstWhere('id', $item['selected_shipping_profile_id'] ?? null)['base_rate'] ?? 0;
                  $unit = $item['price'] ?? 0;
                  $qty = $item['quantity'] ?? 1;
                  return ($unit + $rate) * $qty;
                });
              @endphp
              <tr>
                <th colspan="5" class="text-end">Total:</th>
                <th colspan="2">{{ get_currency() }} {{ number_format($grandTotal, 2) }}</th>
              </tr>
            </tfoot>
          </table>
        </div>

        {{-- Continue shopping / update shipping & checkout --}}
        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            Continue Shopping
          </a>
          <div>
            <button type="submit" class="btn btn-outline-primary me-2">
              Update Shipping
            </button>
            <a href="{{ route('cart.checkout') }}" class="btn btn-primary">
              Proceed to Checkout
            </a>
          </div>
        </div>
      </form>
    @endif
  </div>
</section>
@endsection
