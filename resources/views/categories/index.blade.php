@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Categories') }}
</h2>
@endsection

@section('content')
<div class="content">
  <div class="py-6">
    <div class="container-lg">

      @if(session('success'))
        <div class="alert alert-success mb-4" role="alert">
          {{ session('success') }}
        </div>
      @endif

      <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
          + New Category
        </a>
      </div>

      @if($categories->isEmpty())
        <p class="text-muted">No categories found.</p>
      @else
        <div class="card shadow-sm">
          <div class="card-body">
            <table class="table table-bordered table-striped">
              <thead class="table-light">
                <tr>
                  <th scope="col">Image</th>
                  <th scope="col">Name</th>
                  <th scope="col">Slug</th>
                  <th scope="col">Parent</th>
                  <th scope="col">listing fee</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
              @foreach($categories as $cat)
                <tr>
                  <td class="text-center">
                    @if($cat->image)
                      <img src="{{ asset('storage/'.$cat->image) }}"
                           class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $cat->name }}</td>
                  <td>{{ $cat->slug }}</td>
                  <td>{{ $cat->parent?->name ?? '—' }}</td>
                  <td>{{ $cat->listing_fee }}</td>
                  <td class="text-end">
                    <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-warning">
                      Edit
                    </a>
                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">
                        Delete
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif

    </div>
  </div>
</div>
@endsection
