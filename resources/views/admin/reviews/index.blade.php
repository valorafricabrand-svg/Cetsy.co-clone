@extends('layouts.app')

@section('title', 'All Reviews')

@section('content')
<div class="content">
    <h2 class="h4 mb-4 fw-semibold">All Reviews</h2>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Shop</th>
                            <th>User</th>
                            <th>Order</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Approved</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr>
                                <td>{{ $review->id }}</td>
                                <td>{{ $review->shop ? $review->shop->name : '-' }}</td>
                                <td>{{ $review->user ? $review->user->name : '-' }}</td>
                                <td>{{ $review->order ? $review->order->id : '-' }}</td>
                                <td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }} text-warning"></i>
                                    @endfor
                                </td>
                                <td>{{ $review->comment }}</td>
                                <td>
                                    @if($review->approved)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $review->created_at ? $review->created_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No reviews found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection 