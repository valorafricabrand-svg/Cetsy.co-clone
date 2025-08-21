@extends('layouts.app')

@section('title', 'Add Notification')

@section('content')
<div class="content">
<div class="container mt-4">
    <h2>Add Notification</h2>
    <form method="POST" action="{{ route('admin.notifications.store') }}">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" id="title" name="title" class="form-control" required value="{{ old('title') }}">
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
            <textarea id="message" name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Notification</button>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
</div>
@endsection