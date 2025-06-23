@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        Conversation with {{ $message->sender->name ?? 'Seller' }}
        @if($message->product)
            <span class="badge bg-secondary ms-2">{{ $message->product->name }}</span>
        @endif
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow mb-4">
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @forelse($messages as $msg)
                            <div class="mb-3 {{ $msg->sender_id == auth()->id() ? 'text-end' : 'text-start' }}">
                                <div class="d-inline-block p-2 rounded {{ $msg->sender_id == auth()->id() ? 'bg-success text-white' : 'bg-light' }}">
                                    <strong>{{ $msg->sender->name ?? 'Unknown' }}:</strong>
                                    <span>{{ $msg->body }}</span>
                                    <div class="small text-muted">{{ $msg->created_at->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">No messages yet.</div>
                        @endforelse
                    </div>
                </div>
                <form method="POST" action="{{ route('messages.store') }}">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $message->sender_id }}">
                    <input type="hidden" name="product_id" value="{{ $message->product_id }}">
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
