@extends('layouts.app')

@section('title', 'Blog Categories')

@section('content')
<div class="container-xxl py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Blog Categories</h1>
            <p class="text-muted mb-0">Organize blog posts into curated groups.</p>
        </div>
        <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Category</a>
    </div>

    <form method="GET" action="{{ route('admin.blog-categories.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Name or slug">
            </div>
            <div class="col-12 col-md-2 col-xl-2">
                <button class="btn btn-primary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Posts</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="fw-semibold">{{ $category->name }}</td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>{{ $category->posts()->count() }}</td>
                        <td>{{ $category->updated_at?->diffForHumans() }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.blog-categories.edit', $category) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('admin.blog-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Posts will remain uncategorized.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No categories found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection

