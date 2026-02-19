@extends('theme.'.theme().'.layouts.app')

@section('title', 'Users')

@section('main')
@php
  $showBuyerSidebar = auth()->check() && auth()->user()->isBuyer();
  $showSellerSidebar = auth()->check() && auth()->user()->isSeller();
  $hasSidebar = $showBuyerSidebar || $showSellerSidebar;
@endphp

<div class="py-8">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
      @if($hasSidebar)
        <div class="col-span-12 lg:col-span-3">
          @if($showBuyerSidebar)
            @include('buyer.partials.sidebar')
          @elseif($showSellerSidebar)
            @include('seller.partials.sidebar')
          @endif
        </div>
      @endif

      <div class="{{ $hasSidebar ? 'col-span-12 lg:col-span-9' : 'col-span-12' }} space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
          <h1 class="text-2xl font-semibold text-slate-900">Users</h1>
          <p class="mt-1 text-slate-500">Browse users associated with orders.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-3">
            <h3 class="text-lg font-semibold text-slate-900">Users List</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50 text-slate-700">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">ID</th>
                  <th class="px-4 py-3 text-left font-semibold">Name</th>
                  <th class="px-4 py-3 text-left font-semibold">Created</th>
                  <th class="px-4 py-3 text-left font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                @forelse($users as $order)
                  <tr>
                    <td class="px-4 py-3">{{ $order->id }}</td>
                    <td class="px-4 py-3">{{ $order->name }}</td>
                    <td class="px-4 py-3">{{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}</td>
                    <td class="px-4 py-3">
                      <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-500">View</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">No users found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="border-t border-slate-200 px-4 py-3">
            {{ $users->links('pagination::tailwind') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
