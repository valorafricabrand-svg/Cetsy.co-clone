{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">{{ __('Edit User') }}</h2>
@endsection

@section('content')
<div class="content">
    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle me-1"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="form-control"
                            placeholder="Full Name"
                            required
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="form-control"
                            placeholder="user@example.com"
                            required
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="user_type" class="form-label">User Type</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
                        <select id="user_type" name="user_type" class="form-select" required>
                            <option value="admin"  {{ old('user_type', $user->user_type)=='admin'  ? 'selected' : '' }}>Admin</option>
                            <option value="seller" {{ old('user_type', $user->user_type)=='seller' ? 'selected' : '' }}>Seller</option>
                            <option value="buyer"  {{ old('user_type', $user->user_type)=='buyer'  ? 'selected' : '' }}>Buyer</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="New Password"
                            >
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Confirm New Password"
                            >
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
