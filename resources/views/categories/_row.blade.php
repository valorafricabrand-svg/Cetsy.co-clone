@php
  /** @var \App\Models\Category $cat */
  $depth = $depth ?? 0;
  $isTop = $depth === 0;

  // Visual indent for nested levels
  $indentHtml = $depth > 0 ? str_repeat('&nbsp;', $depth * 3) . '↳&nbsp;' : '';
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
           class="rounded-circle"
           style="width:40px;height:40px;object-fit:cover;">
    @else
      <span class="text-muted">—</span>
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
            class="btn btn-sm btn-outline-secondary ms-2 mt-1 select-subtree"
            data-id="{{ $cat->id }}">
      Select children
    </button>
  </td>

  {{-- Type --}}
  <td>
    @if($cat->listing_type)
      <span class="badge badge-soft">{{ ucfirst($cat->listing_type) }}</span>
    @else
      —
    @endif
  </td>

  {{-- Parent --}}
  <td>{{ $cat->parent?->name ?? '—' }}</td>

  {{-- Listing Fee --}}
  <td>{{ get_currency() }} {{ number_format((float)($cat->listing_fee ?? 0), 2) }}</td>

  {{-- Cycle --}}
  <td>
    @php $f = (int) ($cat->listing_frequency ?? 4); @endphp
    <span class="badge badge-soft green">{{ $f }} mo{{ $f === 1 ? '' : 's' }}</span>
  </td>

  {{-- Actions --}}
  <td class="text-end">
    <a href="{{ route('admin.categories.show', $cat) }}"
       class="btn btn-sm btn-info text-white me-1">
      View
    </a>
    <a href="{{ route('admin.categories.edit', $cat) }}"
       class="btn btn-sm btn-warning me-1">
      Edit
    </a>
    <form action="{{ route('admin.categories.destroy', $cat) }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Delete category “{{ $cat->name }}”?')">
      @csrf
      @method('DELETE')
      <button class="btn btn-sm btn-danger">Delete</button>
    </form>
  </td>
</tr>

{{-- Render children (and grandchildren, etc.) recursively --}}
@if($cat->relationLoaded('children') && $cat->children->isNotEmpty())
  @foreach($cat->children->sortBy('name') as $child)
    @include('categories._row', ['cat' => $child, 'depth' => $depth + 1])
  @endforeach
@endif
