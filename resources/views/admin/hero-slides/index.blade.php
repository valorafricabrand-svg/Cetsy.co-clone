@extends('admin.layout')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Homepage Hero Slides</h1>
    <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> New Slide
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:80px;">Image</th>
              <th>Title</th>
              <th>Tag</th>
              <th>Button</th>
              <th>Sort</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($slides as $slide)
              <tr>
                <td>
                  @if($slide->image_path)
                    <img src="{{ asset('storage/'.$slide->image_path) }}" alt="" class="img-fluid rounded" style="max-height:48px;object-fit:cover;">
                  @else
                    <span class="text-muted small">No image</span>
                  @endif
                </td>
                <td>{{ $slide->title }}</td>
                <td>{{ $slide->tag ?: '—' }}</td>
                <td>{{ $slide->button_label ?: '—' }}</td>
                <td>{{ $slide->sort_order }}</td>
                <td>
                  @if($slide->is_active)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Hidden</span>
                  @endif
                </td>
                <td class="text-end">
                  <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slide?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No hero slides yet. Click “New Slide” to add one.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($slides->hasPages())
        <div class="card-footer border-0">
          {{ $slides->links() }}
        </div>
      @endif
    </div>
  </div>
@endsection

