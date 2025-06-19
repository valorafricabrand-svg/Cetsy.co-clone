@extends('layouts.app')
@section('title', 'My Messages')

@section('content')
<div class="content">
    <h1 class="h4 mb-4">My Messages</h1>

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
                        <th>Sender</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>{{ $message->id }}</td>
                            <td>
                                {{ $message->product->name ?? '-' }}<br>
                                <span class="text-muted small">#{{ $message->product_id }}</span>
                                <br>
                                <a href="?product={{ $message->product_id }}" class="btn btn-link btn-sm p-0">
                                    View all for this product
                                </a>
                            </td>
                            <td>
                                {{ $message->sender->name ?? '-' }}<br>
                                <span class="text-muted small">{{ $message->sender->email ?? '' }}</span>
                            </td>
                            <td>
                                {{ \Illuminate\Support\Str::limit($message->body ?? $message->content ?? '-', 40) }}
                            </td>
                            <td>{{ $message->created_at ? $message->created_at->format('d M Y, H:i') : '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('seller.messages.show', $message->id) }}" class="btn btn-outline-secondary btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No messages found for your products.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 