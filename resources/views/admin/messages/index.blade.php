@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="content">
    <div class="container py-5">
        <h1 class="mb-4">All Messages</h1>

        <!-- Check if there are any messages -->
        @if ($messages->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You have no messages.
            </div>
        @else
            <!-- Loop through messages and display each one -->
            <div class="list-group">
                @foreach ($messages as $message)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="message-info">
                            <strong>{{ $message->sender->name }}</strong>
                            <p class="text-muted mb-1">{{ Str::limit($message->body, 100) }}</p>
                            <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                        </div>
                        <div>
                            <!-- Button to view the full message and reply form -->
                            <button class="btn btn-sm btn-primary view-message-btn" data-message-id="{{ $message->id }}">View</button>
                        </div>
                    </div>

                    <!-- Full message and reply form (hidden by default) -->
                    <div class="message-detail" id="message-detail-{{ $message->id }}" style="display: none; padding-top: 20px;">
                        <div class="card">
                            <div class="card-body">
                                <h5>Message from: {{ $message->sender->name }}</h5>
                                <p><strong>Subject:</strong> {{ $message->subject ?? 'No Subject' }}</p>
                                <p><strong>Message:</strong> {{ $message->body }}</p>

                                <!-- Reply Section -->
                                <form action="{{ route('admin.messages.reply', $message->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="reply" class="form-label">Your Reply</label>
                                        <textarea class="form-control" name="reply" id="reply" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">Send Reply</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all 'View' buttons
        const viewButtons = document.querySelectorAll('.view-message-btn');

        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get the message ID from the button's data attribute
                const messageId = this.getAttribute('data-message-id');
                const messageDetail = document.getElementById(`message-detail-${messageId}`);

                // Toggle the visibility of the message detail and reply section
                if (messageDetail.style.display === 'none' || messageDetail.style.display === '') {
                    messageDetail.style.display = 'block'; // Show the message and reply form
                } else {
                    messageDetail.style.display = 'none'; // Hide the message and reply form
                }
            });
        });
    });
</script>
@endpush
