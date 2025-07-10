{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.frontapp')

@section('title','Your Cart')

@section('main')
<section class="cart-page py-5 bg-light" x-data>
  <div class="container">
    {{-- flash messages --}}
    @if(session('success')) <div class="alert alert-success">{!! session('success') !!}</div> @endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

    @php $cart = session('cart', []); @endphp

    {{-- Empty cart --------------------------------------------------------------- --}}
    @if(empty($cart))
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>

    {{-- Populated cart ----------------------------------------------------------- --}}
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
                <th>Shipping Profile</th>
                <th>Line&nbsp;Total</th>
                <th>Remove</th>
              </tr>
            </thead>

            <tbody>
            @foreach($cart as $item)
              @php
                /* ------------------------------------------
                   Work out shipping for the *currently* selected
                   profile so the line-total shows correctly.   */
                $profiles   = collect($item['shipping_profiles']);
                $selected   = $profiles->firstWhere('id', $item['selected_shipping_profile_id']);
                $rate       = $selected['base_rate'] ?? 0;                // single-unit rate
                $lineTotal  = ($item['price'] + $rate) * $item['quantity'];
              @endphp

              <tr>
                {{-- Product & thumbnail --}}
                <td class="d-flex align-items-center gap-3">
                  @if(!empty($item['photo']))
                    <img src="{{ asset('storage/'.$item['photo']) }}" alt="" width="60" height="60" class="rounded">
                  @endif
                  <span>{{ $item['name'] }}</span>
                </td>

                {{-- Unit price --}}
                <td>{{ get_currency() }} {{ number_format($item['price'],2) }}</td>

                {{-- Quantity controls --}}
                <td>
                  <div class="d-flex justify-content-center align-items-center gap-1">

                    {{-- – button --}}
                    <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                      @csrf
                      <input type="hidden" name="id" value="{{ $item['id'] }}">
                      <input type="hidden" name="action" value="decrease">
                      <button type="submit" class="btn btn-sm btn-outline-secondary"
                              @disabled($item['quantity']<=1)>–</button>
                    </form>

                    <span class="px-2">{{ $item['quantity'] }}</span>

                    {{-- + button --}}
                    <form action="{{ route('cart.update') }}" method="POST" class="d-inline">
                      @csrf
                      <input type="hidden" name="id" value="{{ $item['id'] }}">
                      <input type="hidden" name="action" value="increase">
                      <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                    </form>
                  </div>
                </td>

                {{-- Shipping profile selector --}}
                <td>
                  @php
                    /* Put the selected option first for nicer UX */
                    $ordered = $selected
                               ? collect([$selected])->merge($profiles->where('id','!=',$selected['id']))
                               : $profiles;
                  @endphp

                  <select name="shipping_profile_ids[{{ $item['id'] }}]"
                          class="form-select form-select-sm">
                    @foreach($ordered as $profile)
                      <option value="{{ $profile['id'] }}"
                          @selected($profile['id']===$item['selected_shipping_profile_id'])>
                        {{ $profile['name'] }}
                        ({{ get_currency() }} {{ number_format($profile['base_rate'],2) }})
                      </option>
                    @endforeach
                  </select>
                </td>

                {{-- Line total = (price + rate) × qty --}}
                <td>{{ get_currency() }} {{ number_format($lineTotal,2) }}</td>

                {{-- Remove button --}}
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

            {{-- Cart totals --}}
            <tfoot class="table-light">
              @php
                $grand = collect($cart)->sum(fn($item)=>
                  ($item['price']
                   + (collect($item['shipping_profiles'])
                         ->firstWhere('id',$item['selected_shipping_profile_id'])['base_rate'] ?? 0)
                  ) * $item['quantity']
                );
              @endphp
              <tr>
                <th colspan="4" class="text-end">Total:</th>
                <th colspan="2">{{ get_currency() }} {{ number_format($grand,2) }}</th>
              </tr>
            </tfoot>
          </table>
        </div>

        {{-- where to redirect after saving shipping choices --}}
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
