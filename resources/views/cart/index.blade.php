{{-- resources/views/cart/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title','Your Cart')

@section('main')
<section class="cart-page py-5 bg-light">
  <div class="container">
    {{-- Flash --}}
    <div id="flash-container">
      @if(session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
    </div>

    @php
      $cart     = session('cart', []);
      $currency = get_currency();
    @endphp

    @if($cart === [])
      <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Continue Shopping</a>
      </div>
    @else
      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Product</th><th>Variation</th><th>Price</th>
              <th>Quantity</th><th>Shipping Profile</th>
              <th>Line&nbsp;Total</th><th>Remove</th>
            </tr>
          </thead>
          <tbody>
          @foreach($cart as $rowId => $item)
            @php
              $qty       = $item['quantity'];
              $unitPrice = $item['price'];
              // You already store profiles in session; you can reuse them or query fresh:
              $profiles  = \App\Models\ShippingProfile::where('product_id',$item['product_id'])->orderBy('name')->get();
              $selected  = $profiles->firstWhere('id',$item['selected_shipping_profile_id'] ?? null);
              $rate      = $selected->base_rate ?? 0;
              $lineTotal = ($unitPrice + $rate) * $qty;
              $photoUrl  = isset($item['photo']) && filter_var($item['photo'],FILTER_VALIDATE_URL)
                              ? $item['photo']
                              : (isset($item['photo']) ? asset('storage/'.$item['photo']) : null);
            @endphp
            <tr
              data-row-id="{{ $rowId }}"
              data-unit-price="{{ $unitPrice }}"
              data-quantity="{{ $qty }}"
            >
              {{-- Product --}}
              <td class="text-start">
                <div class="d-flex gap-3 align-items-center">
                  @if($photoUrl)
                    <img src="{{ $photoUrl }}" width="60" height="60" class="rounded object-fit-cover" alt="">
                  @endif
                  <span class="fw-semibold">{{ $item['name'] }}</span>
                </div>
              </td>

              {{-- Variation --}}
              <td>{{ $item['variation_summary'] ?? '—' }}</td>

              {{-- Price --}}
              <td>{{ $currency }} {{ number_format($unitPrice,2) }}</td>

              {{-- Quantity --}}
              <td>
                <div class="d-flex gap-1 justify-content-center align-items-center">
                  <button
                    class="btn btn-sm btn-outline-secondary js-qty-btn"
                    data-action="decrease"
                    @disabled($qty<=1)
                  >&minus;</button>
                  <span class="quantity">{{ $qty }}</span>
                  <button
                    class="btn btn-sm btn-outline-secondary js-qty-btn"
                    data-action="increase"
                  >+</button>
                </div>
              </td>

              {{-- Shipping select --}}
              <td>
                <select
                  name="shipping_profile_ids[{{ $rowId }}]"
                  class="form-select form-select-sm js-shipping-select"
                >
                  @foreach($profiles as $profile)
                    @php
                      $label = $profile->dest_location_type==='everywhere_else'
                               ? 'Everywhere'
                               : ($profile->destCountry? 'Ship to '.$profile->destCountry->name : $profile->name);
                    @endphp
                    <option
                      value="{{ $profile->id }}"
                      data-base-rate="{{ $profile->base_rate }}"
                      @selected($profile->id == ($item['selected_shipping_profile_id'] ?? null))
                    >
                      {{ $label }} ({{ $currency }} {{ number_format($profile->base_rate,2) }})
                    </option>
                  @endforeach
                </select>
              </td>

              {{-- Line total --}}
              <td class="line-total">{{ $currency }} {{ number_format($lineTotal,2) }}</td>

              {{-- Remove --}}
              <td>
                <form action="{{ route('cart.remove') }}" method="POST" class="d-inline js-remove-form">
                  @csrf
                  <input type="hidden" name="row_id" value="{{ $rowId }}">
                  <button class="btn btn-sm btn-danger">&times;</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="5" class="text-end">Total:</th>
              <th colspan="2" id="grand-total">
                {{ $currency }}
                {{ number_format(
                     collect($cart)->sum(fn($i)=>
                       (($i['price'] ?? 0) +
                        (collect($i['shipping_profiles'] ?? [])
                          ->firstWhere('id',$i['selected_shipping_profile_id'] ?? null)['base_rate'] ?? 0)
                       ) * ($i['quantity'] ?? 1)
                     ),2) }}
              </th>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- hidden form (kept for progressive enhancement; not used by JS) --}}
      <form id="shipping-form" method="POST" action="{{ route('cart.shipping') }}">
        @csrf
      </form>

      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Continue Shopping</a>
        <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const CSRF  = '{{ csrf_token() }}';
  const cur   = '{{ $currency }}';
  const flash = document.getElementById('flash-container');

  function money(n){ return cur+' '+(+n).toFixed(2); }

  function rowTotal($tr){
    const unit = +$tr.dataset.unitPrice;
    const qty  = +$tr.dataset.quantity;
    const rate = +$tr.querySelector('.js-shipping-select')
                    .selectedOptions[0]
                    .dataset.baseRate;
    return (unit+rate)*qty;
  }

  function refreshRow($tr){
    $tr.querySelector('.line-total').textContent = money(rowTotal($tr));
  }

  function refreshGrand(){
    let sum=0;
    document.querySelectorAll('tbody tr').forEach($tr=>{
      sum+=rowTotal($tr);
    });
    document.getElementById('grand-total').textContent = money(sum);
  }

  function notify(type,msg){
    flash.innerHTML=`<div class="alert alert-${type}">${msg}</div>`;
  }

  /* quantity buttons */
  document.querySelectorAll('.js-qty-btn').forEach(btn=>{
    btn.addEventListener('click',e=>{
      const $tr   = btn.closest('tr');
      const rowId = $tr.dataset.rowId;
      const act   = btn.dataset.action;

      fetch('{{ route("cart.update") }}',{
        method:'POST',
        headers:{
          'X-CSRF-TOKEN':CSRF,'Accept':'application/json',
          'Content-Type':'application/json'
        },
        body:JSON.stringify({row_id:rowId,action:act})
      })
      .then(r=>r.json())
      .then(json=>{
        const qty=json.cart[rowId].quantity;
        $tr.dataset.quantity=qty;
        $tr.querySelector('.quantity').textContent=qty;
        $tr.querySelectorAll('.js-qty-btn[data-action="decrease"]')
           .forEach(b=>b.disabled=qty<=1);
        refreshRow($tr); refreshGrand();
        notify('success','Quantity updated.');
      })
      .catch(()=>notify('danger','Failed to update quantity.'));
    });
  });

  /* shipping select */
  document.querySelectorAll('.js-shipping-select').forEach(sel=>{
    sel.addEventListener('change',()=>{
      const $tr  = sel.closest('tr');
      const rowId= $tr.dataset.rowId;

      // update UI totals immediately
      refreshRow($tr); refreshGrand();

      fetch('{{ route("cart.shipping") }}',{
        method:'POST',
        headers:{
          'X-CSRF-TOKEN':CSRF,'Accept':'application/json',
          'Content-Type':'application/json'
        },
        body:JSON.stringify({shipping_profile_ids:{[rowId]:sel.value}})
      })
      .then(r=>r.json())
      .then(j=>notify('success',j.message || 'Shipping updated.'))
      .catch(()=>notify('danger','Failed to update shipping.'));
    });
  });

  /* remove */
  document.querySelectorAll('.js-remove-form').forEach(frm=>{
    frm.addEventListener('submit',e=>{
      e.preventDefault();
      fetch(frm.action,{
        method:'POST',
        headers:{
          'X-CSRF-TOKEN':CSRF,'Accept':'application/json',
          'Content-Type':'application/json'
        },
        body:JSON.stringify({row_id:frm.row_id.value})
      }).then(()=>location.reload());
    });
  });

  // initial totals
  refreshGrand();
});
</script>
@endpush
