{{-- resources/views/admin/policies/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4 mb-0">User Agreement Sections</h2>
@endsection

@section('content')
<div class="content">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Section</th>
            <th class="d-none d-md-table-cell">Slug</th>
            <th>Status</th>
            <th>Updated</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
          <tr>
            <td>{{ $row['label'] }}</td>
            <td class="text-muted d-none d-md-table-cell">{{ $row['slug'] }}</td>
            <td>
              @if($row['has_content'])
                <span class="badge bg-success">Customized</span>
              @else
                <span class="badge bg-secondary">Default</span>
              @endif
            </td>
            <td class="text-muted">{{ $row['updated_at'] ? $row['updated_at']->diffForHumans() : '—' }}</td>
            <td class="text-end">
              <a href="{{ route('admin.policies.edit', $row['slug']) }}" class="btn btn-sm btn-primary">Edit</a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

