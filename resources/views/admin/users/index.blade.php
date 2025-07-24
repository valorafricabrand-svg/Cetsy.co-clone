{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">{{ __('Manage Users') }}</h2>
@endsection

@section('content')
<div class="content">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0">All Sellers</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Seller
        </a>
    </div>

    @if($users->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Type</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <th scope="row">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </th>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->user_type }}</td>
                            <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="text-end">
                            <a href="{{ route('admin.sellers.login-as', $user->id) }}" 
                                               class="btn btn-sm btn-outline-success me-2"
                                               onclick="return confirm('Are you sure you want to login as this seller?')">
                                                <i class="fas fa-user-secret me-1"></i> Login as Seller
                                            </a>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                @if(!$user->is_active)
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline-block me-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this Seller Account?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="d-inline-block me-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this Seller Account?')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
      

               {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info">
            No users found. <a href="{{ route('admin.users.create') }}" class="alert-link">Create one</a>.
        </div>
    @endif
</div>
@endsection
