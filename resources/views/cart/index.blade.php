{{-- resources/views/cart/index.blade.php --}}
@extends('theme.layouts.main')

@section('main')
<div class="container py-5">
  <h1 class="h3 mb-4">Your Cart</h1>

  @if($items->isEmpty())
    <div class="alert alert-info">
      Your cart is empty.
    </div>
    <a href="{{ route('listings') }}" class="btn btn-primary">
      Browse Products
    </a>
  @else
    <div class="table-responsive shadow-sm rounded bg-white">
      <table class="table mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col">Product</th>
            <th scope="col">Price</th>
            <th scope="col" style="width:120px">Quantity</th>
            <th scope="col">Total</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $item)
            <tr>
              <td class="align-middle">
                <div class="d-flex align-items-center">
                  @if($item->image)
                    <img
                      src="{{ asset('storage/'.$item->image) }}"
                      alt="{{ $item->name }}"
                      class="img-thumbnail rounded me-3"
                      style="width:60px; height:60px; object-fit:cover;"
                    >
                  @endif
                  <a href="{{ route('products.show', $item->id) }}" class="fw-semibold text-decoration-none">
                    {{ $item->name }}
                  </a>
                </div>
              </td>
              <td class="align-middle">KES {{ $item->price }}</td>
              <td class="align-middle">
                <form
                  action="{{ route('cart.update', $item->id) }}"
                  method="POST"
                  class="d-flex align-items-center"
                >
                  @csrf
                  @method('PATCH')
                  <input
                    type="number"
                    name="quantity"
                    value="{{ $item->qty }}"
                    min="1"
                    class="form-control form-control-sm me-2"
                    style="width: 80px;"
                  >
                  <button type="submit" class="btn btn-link btn-sm p-0">
                    Update
                  </button>
                </form>
              </td>
              <td class="align-middle">KES {{ $item->total }}</td>
              <td class="align-middle text-end">
                <form action="{{ route('cart.destroy', $item->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                    Remove
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <p class="h5 mb-0">Subtotal: KES {{ $subtotal }}</p>
      <a href="{{ route('checkout.index') }}" class="btn btn-success">
        Proceed to Checkout
      </a>
    </div>
  @endif
</div>
@endsection
