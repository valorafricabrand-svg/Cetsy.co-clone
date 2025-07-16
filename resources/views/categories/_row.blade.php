@php
    // Add a small visual indent + arrow for child rows
    $indent = $isChild ? '&nbsp;&nbsp;↳&nbsp;' : '';
@endphp

<tr>
    {{-- Image --}}
    <td class="text-center">
        @if($cat->image)
            <img src="{{ asset('storage/' . $cat->image) }}"
                 class="rounded-circle"
                 style="width:40px;height:40px;object-fit:cover;">
        @else
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- Name (bold if parent) --}}
    <td>
        {!! $indent !!}
        @if(!$isChild)
            <strong>{{ $cat->name }}</strong>
        @else
            {{ $cat->name }}
        @endif
    </td>

    {{-- Slug / Parent / Fee --}}
 <td>{{ $parent->listing_type }}</td>
    <td>{{ $cat->parent?->name ?? '—' }}</td>
    <td>{{ get_currency() }} {{ number_format($cat->listing_fee, 2) }}</td>

    {{-- Actions --}}
    <td class="text-end">
        <a href="{{ route('admin.categories.show', $cat) }}"
           class="btn btn-sm btn-info text-white me-1">
            View
        </a>
    </td>
</tr>
