@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
        <h2 class="text-3xl font-bold text-gray-900">Your Products</h2>
        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 w-full sm:w-auto">
            <form action="{{ route('products.index') }}" method="GET" class="flex w-full sm:w-auto">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search products..."
                    class="flex-grow border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-r-lg transition"
                >
                    Search
                </button>
            </form>
            <a
                href="{{ route('products.create') }}"
                class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition"
            >
                <!-- You can replace this with a Heroicon if available -->
                Add New Product
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Products Table Card -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($product->media->first())
                            <img
                                src="{{ asset('storage/' . $product->media->first()->url) }}"
                                alt="{{ $product->name }}"
                                class="w-12 h-12 object-cover rounded-md"
                            />
                        @else
                            <div class="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center">
                                <span class="text-gray-400">—</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-medium">{{ $product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-800">KES {{ number_format($product->price, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-800">{{ $product->stock }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $statusClasses = [
                                'active' => 'bg-green-100 text-green-800',
                                'draft'  => 'bg-gray-100 text-gray-800',
                                'pending'=> 'bg-yellow-100 text-yellow-800',
                                'archived' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$product->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-900 transition">Edit</a>
                        <form
                            action="{{ route('products.destroy', $product) }}"
                            method="POST"
                            class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this product?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 transition">Delete</button>
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

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
