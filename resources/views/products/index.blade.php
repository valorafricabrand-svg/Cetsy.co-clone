@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Your Products</h2>
        <a href="{{ route('products.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add New Product</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('products.index') }}" method="GET" class="mb-4">
        <div class="flex">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="border rounded-l px-3 py-2 w-full">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-r">Search</button>
        </div>
    </form>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($products as $product)
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    @if($product->media->first())
                      <img src="{{ asset('storage/' . $product->media->first()->url) }}" class="w-12 h-12 object-cover rounded">
                    @else
                      <span class="text-gray-400">–</span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ $product->category->name ?? '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($product->price,2) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ $product->stock }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{
                      $product->status=='active' ? 'bg-green-100 text-green-800' :
                      ($product->status=='draft' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800')
                    }}">
                      {{ ucfirst($product->status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="px-6 py-4 text-center text-gray-500">No products found.</td>
                </tr>
              @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection
