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

      @includeWhen(session('success'), 'partials.alert-success', ['msg'=>session('success')])

      <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
          + New Category
        </a>
      </div>

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
                @include('categories._row', ['cat'=>$parent,'isChild'=>false])

                {{-- CHILDREN --}}
                @foreach($parent->children as $child)
                  @include('categories._row', ['cat'=>$child,'isChild'=>true])
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
