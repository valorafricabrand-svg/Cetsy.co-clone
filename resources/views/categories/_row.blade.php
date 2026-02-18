@php
  /** @var \App\Models\Category $cat */
  $depth = $depth ?? 0;
  $isTop = $depth === 0;

  // Visual indent for nested levels
  $indentHtml = $depth > 0 ? str_repeat('&nbsp;', $depth * 3) . 'â†³&nbsp;' : '';
@endphp

<tr data-id="{{ $cat->id }}" data-parent-id="{{ $cat->parent_id ?? '' }}">
  {{-- Select --}}
  <td class="text-center">
    <input type="checkbox" class="category-checkbox" name="ids[]" value="{{ $cat->id }}">
  </td>
  {{-- Image --}}
  <td class="text-center">
    @if($cat->image)
      <img src="{{ asset('storage/' . ltrim($cat->image, '/')) }}"
           class="rounded-full"
           style="width:40px;height:40px;object-fit:cover;">
    @else
      <span class="text-slate-500">â€”</span>
    @endif
  </td>

  {{-- Name (bold if top-level) --}}
  <td>
    {!! $indentHtml !!}
    @if($isTop)
      <strong>{{ $cat->name }}</strong>
    @else
      {{ $cat->name }}
    @endif
    <div class="slug">/{{ $cat->slug }}</div>
    <button type="button"
            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50 ml-2 mt-1 select-subtree"
            data-id="{{ $cat->id }}">
      Select children
    </button>
  </td>

  {{-- Type --}}
  <td>
    @if($cat->listing_type)
      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium badge-soft">{{ ucfirst($cat->listing_type) }}</span>
    @else
      â€”
    @endif
  </td>

  {{-- Parent --}}
  <td>{{ $cat->parent?->name ?? 'â€”' }}</td>

  {{-- Listing Fee --}}
  <td>{{ get_currency() }} {{ number_format((float)($cat->listing_fee ?? 0), 2) }}</td>

  {{-- Cycle --}}
  <td>
    @php $f = (int) ($cat->listing_frequency ?? 4); @endphp
    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium badge-soft green">{{ $f }} mo{{ $f === 1 ? '' : 's' }}</span>
  </td>

  {{-- Actions --}}
  <td class="text-right">
    <a href="{{ route('admin.categories.show', $cat) }}"
       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-sky-500 text-white hover:bg-sky-400 text-white mr-1">
      View
    </a>
    <a href="{{ route('admin.categories.edit', $cat) }}"
       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400 mr-1">
      Edit
    </a>
    <form action="{{ route('admin.categories.destroy', $cat) }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Delete category â€œ{{ $cat->name }}â€?')">
      @csrf
      @method('DELETE')
      <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-rose-600 text-white hover:bg-rose-500">Delete</button>
    </form>
  </td>
</tr>

{{-- Render children (and grandchildren, etc.) recursively --}}
@if($cat->relationLoaded('children') && $cat->children->isNotEmpty())
  @foreach($cat->children->sortBy('name') as $child)
    @include('categories._row', ['cat' => $child, 'depth' => $depth + 1])
  @endforeach
@endif

