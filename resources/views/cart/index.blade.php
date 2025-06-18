{{-- resources/views/cart/index.blade.php --}}
@extends('theme.layouts.main')

@section('main')
<!-- Page Header Start -->
<section class="page-header bg-light py-5">
    <div class="page-header-bg" style="background-image: url('{{ asset('assets/images/backgrounds/page-header-bg.jpg') }}'); background-size: cover; background-position: center;"></div>
    <div class="container">
        <div class="page-header__inner">
            <ul class="breadcrumb list-unstyled d-flex">
                <li class="breadcrumb-item"><a href="/" class="text-dark">Home</a></li>
                <li class="breadcrumb-item active text-dark">Cart</li>
            </ul>
            <h2 class="page-header-title text-dark">Cart</h2>
        </div>
    </div>
</section>
<!-- Page Header End -->

<!-- Start Cart Page -->
<section class="cart-page py-5 bg-light">
    <div class="container">
        @if (session('registration_success'))
            <div class="alert alert-success">{!! session('registration_success') !!}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($cart && count($cart) > 0)
            <div class="table-responsive">
                <table class="table table-bordered cart-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach ($cart as $id => $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="img-box me-3">
                                            <img src="{{ url('/') }}/storage/{{ $item['photo'] }}" alt="" class="img-fluid" width="100">
                                        </div>
                                        <h5 class="mb-0"><a href="#" class="text-dark">{{ $item['name'] }}</a></h5>
                                    </div>
                                </td>
                                <td>{{ get_currency() }} {{ number_format($item['price'], 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <form action="{{ route('cart.update') }}" method="POST" class="d-flex">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $id }}">
                                            <button type="submit" name="action" value="decrease" class="btn btn-sm btn-outline-secondary me-2">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control text-center w-50 border-secondary" />
                                            <button type="submit" name="action" value="increase" class="btn btn-sm btn-outline-secondary ms-2">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>{{ get_currency() }} {{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                <td>
                                    <form action="{{ route('cart.remove') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $id }}">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @php $subtotal += $item['price'] * $item['quantity']; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-lg-8"></div>
                <div class="col-lg-4">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span>{{ get_currency() }} {{ number_format($subtotal, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Shipping Cost</span>
                            <span>{{ get_currency() }} 0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong>{{ get_currency() }} {{ number_format($subtotal, 2) }}</strong>
                        </li>
                    </ul>
                    <div class="mt-3 d-flex justify-content-between">
                        @auth
                            <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
                        @else
                            <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
                        @endauth
                    </div>
                </div>
            </div>
        @else
            <div class="text-center">
                <h4>Your cart is empty</h4>
                <a href="{{ route('listings') }}" class="btn btn-primary">Return to Shop</a>
            </div>
        @endif
    </div>
</section>
<!-- End Cart Page -->
@endsection
