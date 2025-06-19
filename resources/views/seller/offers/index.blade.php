@extends('layouts.app')
@section('title', 'My Offers')

@section('content')
<div class="content">
    <h1 class="h4 mb-4">My Offers</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Buyer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td>{{ $offer->id }}</td>
                            <td>
                                {{ $offer->product->name ?? '-' }}<br>
                                <span class="text-muted small">#{{ $offer->product_id }}</span>
                            </td>
                            <td>
                                {{ $offer->user->name ?? '-' }}<br>
                                <span class="text-muted small">{{ $offer->user->email ?? '' }}</span>
                            </td>
                            <td>
                                {{ get_currency() }} {{ number_format($offer->offer_price, 2) }}
                            </td>
                            <td class="text-capitalize">{{ $offer->status ?? '-' }}</td>
                            <td>{{ $offer->created_at ? $offer->created_at->format('d M Y, H:i') : '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('seller.offers.show', $offer->id) }}" class="btn btn-outline-secondary btn-sm">
                                    View
                                </a>
                                <!-- <a href="{{ route('seller.offers.edit', $offer->id) }}" class="btn btn-outline-primary btn-sm">
                                    Edit
                                </a>
                                <form action="{{ route('seller.offers.destroy', $offer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this offer?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                                </form> -->
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                No offers found for your products.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- If you paginate: --}}
        {{-- <div class="card-footer">{{ $offers->links() }}</div> --}}
    </div>
</div>
@endsection 