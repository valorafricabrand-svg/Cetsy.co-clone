@extends('theme.'.theme().'.layouts.app')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
 <h1 class="mb-0">One‑Off Deals</h1>
 <a href="{{ route('seller.deals.create') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fas fa-plus mr-1"></i>New Deal
 </a>
 </div>

 @if(session('success'))
 <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">
 {{ session('success') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert">&times;</button>
 </div>
 @endif

 @if(session('error'))
 <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700">
 {{ session('error') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert">&times;</button>
 </div>
 @endif

 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
 <div class="p-4 p-0">
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm mb-0">
 <thead class="bg-slate-50 text-slate-600">
 <tr>
 <th>Deal Name</th>
 <th>Discount</th>
 <th>Applies To</th>
 <th>Status</th>
 <th>Period</th>
 <th>Actions</th>
 </tr>
 </thead>
 <tbody>
 @forelse($deals as $deal)
 <tr>
 <td>
 <strong>{{ $deal->name }}</strong>
 </td>
 <td>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200 text-base">{{ $deal->discount_percent }}% OFF</span>
 </td>
 <td>
 @if($deal->applies_to_all)
 <span class="text-slate-500">All Products</span>
 @else
 <span class="text-slate-500">{{ $deal->products->count() }} Selected</span>
 @endif
 </td>
 <td>
 @if($deal->isActive())
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">Active</span>
 @elseif($deal->starts_at->isFuture())
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 border-amber-200">Scheduled</span>
 @else
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border-slate-200">Expired</span>
 @endif
 </td>
 <td>
 <span class="text-slate-500 text-xs">
 {{ $deal->starts_at->format('M d, Y H:i') }}<br>
 → {{ $deal->ends_at->format('M d, Y H:i') }}
 </span>
 </td>
 <td>
 <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 p-1" role="group">
 <a href="{{ route('seller.deals.edit', $deal) }}" 
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg" 
 title="Edit Deal">
 <i class="fas fa-edit"></i>
 </a>
 
 @if($deal->isActive())
 <form action="{{ route('seller.deals.stop', $deal) }}" 
 method="POST" 
 class="inline"
 onsubmit="return confirm('Are you sure you want to stop this deal?')">
 @csrf
 <button type="submit" 
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50 px-2.5 py-1.5 text-xs rounded-lg" 
 title="Stop Deal">
 <i class="fas fa-stop"></i>
 </button>
 </form>
 @endif
 
 <form action="{{ route('seller.deals.destroy', $deal) }}" 
 method="POST" 
 class="inline"
 onsubmit="return confirm('Are you sure you want to delete this deal? This action cannot be undone.')">
 @csrf
 @method('DELETE')
 <button type="submit" 
 class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 text-xs rounded-lg" 
 title="Delete Deal">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="text-center py-4">
 <div class="text-slate-500">
 <i class="fas fa-percent fa-3x mb-3"></i>
 <p class="mb-0">No deals created yet</p>
 <span class="text-xs">Create your first deal to start offering discounts to customers</span>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 @if($deals->hasPages())
 <div class="mt-4">
 {{ $deals->links() }}
 </div>
 @endif
</div>
 </div>
 </div>
 </div>
</section>
@endsection





