@php
  $indent = $isChild ? '&nbsp;&nbsp;↳&nbsp;' : '';
@endphp
<tr>
  {{-- Image --}}
  <td class="text-center">
    @if($cat->image)
      <img src="{{ asset('storage/'.$cat->image) }}"
           class="rounded-circle"
           style="width:40px;height:40px;object-fit:cover;">
    @else
      <span class="text-muted">—</span>
    @endif
  </td>

  {{-- Name / Slug / Parent / Fee --}}
  <td>{!! $indent !!}{{ $cat->name }}</td>
  <td>{{ $cat->slug }}</td>
  <td>{{ $cat->parent?->name ?? '—' }}</td>
  <td>{{ get_currency() }} {{ number_format($cat->listing_fee,2) }}</td>

  {{-- Actions --}}
  <td class="text-end">
    <a href="{{ route('admin.categories.show', $cat) }}"
       class="btn btn-sm btn-info text-white me-1">View</a>

    <a href="{{ route('admin.categories.edit', $cat) }}"
       class="btn btn-sm btn-warning me-1">Edit</a>

    <form action="{{ route('admin.categories.destroy', $cat) }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Delete this category?');">
      @csrf @method('DELETE')
      <button class="btn btn-sm btn-danger">Delete</button>
    </form>
  </td>
</tr>
