{{-- resources/views/admin/categories/index.blade.php --}}
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

      {{-- flash --}}
      @includeWhen(session('success'), 'partials.alert-success', ['msg'=>session('success')])

      {{-- TOP BAR ------------------------------------------------------------ --}}
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        {{-- search --}}
        <form action="{{ route('admin.categories.index') }}" method="GET" class="w-100 w-md-auto mb-3 mb-md-0">
          <div class="input-group">
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Search categories…"
                   class="form-control"
                   autocomplete="off">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
            @if(request()->filled('q'))
              <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-danger">×</a>
            @endif
          </div>
        </form>

        {{-- new category --}}
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary ms-md-3">
          + New Category
        </a>
      </div>
      {{-- /TOP BAR ----------------------------------------------------------- --}}

      @if($parents->isEmpty())
        <p class="text-muted">No categories found.</p>
      @else
        <div class="card shadow-sm">
          <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width:70px">Image</th>
                  <th>Name</th>
                  <th>Slug</th>
                  <th>Parent</th>
                  <th>Listing Fee</th>
                  <th class="text-end" style="width:220px">Actions</th>
                </tr>
              </thead>
              <tbody>
                {{-- PARENTS --}}
                @foreach($parents as $parent)
                  @include('categories._row', ['cat'=>$parent, 'isChild'=>false])

                  {{-- CHILDREN --}}
                  @foreach($parent->children as $child)
                    @include('categories._row', ['cat'=>$child, 'isChild'=>true])
                  @endforeach
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
