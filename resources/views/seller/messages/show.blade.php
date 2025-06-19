@extends('layouts.app')
@section('title', 'Message Details')

@section('content')
<div class="content py-4">
    <div class="container-xxl">
        <h2 class="mb-4">Message Details</h2>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Message ID</dt>
                    <dd class="col-sm-9">{{ $message->id }}</dd>

                    <dt class="col-sm-3">Product</dt>
                    <dd class="col-sm-9">
                        {{ $message->product->name ?? '-' }}<br>
                        <span class="text-muted small">#{{ $message->product_id }}</span>
                    </dd>

                    <dt class="col-sm-3">Sender</dt>
                    <dd class="col-sm-9">
                        {{ $message->sender->name ?? '-' }}<br>
                        <span class="text-muted small">{{ $message->sender->email ?? '' }}</span>
                    </dd>

                    <dt class="col-sm-3">Message</dt>
                    <dd class="col-sm-9">{{ $message->body ?? $message->content ?? '-' }}</dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ $message->created_at ? $message->created_at->format('d M Y, H:i') : '-' }}</dd>
                </dl>
            </div>
        </div>
        <a href="{{ route('seller.messages.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Messages
        </a>
    </div>
</div>
@endsection 