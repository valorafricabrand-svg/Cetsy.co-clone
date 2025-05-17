@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Categories') }}
</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

    @if(session('success'))
      <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
      </div>
    @endif

    <div class="flex justify-end mb-4">
      <a href="{{ route('categories.create') }}"
         class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        + New Category
      </a>
    </div>

    @if($categories->isEmpty())
      <p class="text-gray-500">No categories found.</p>
    @else
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          @foreach($categories as $cat)
            <tr>
              <td class="px-6 py-4">
                @if($cat->image)
                  <img src="{{ asset('storage/'.$cat->image) }}"
                       class="h-8 w-8 object-cover rounded">
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </td>
              <td class="px-6 py-4">{{ $cat->name }}</td>
              <td class="px-6 py-4">{{ $cat->slug }}</td>
              <td class="px-6 py-4">{{ $cat->parent?->name ?? '—' }}</td>
              <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                <a href="{{ route('categories.edit', $cat) }}"
                   class="text-blue-600 hover:text-blue-900">Edit</a>
                <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif

  </div>
</div>
@endsection
