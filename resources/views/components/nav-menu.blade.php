{{-- resources/views/components/nav-menu.blade.php --}}
@props([
  'active' => false,
  'href'   => '#',
])

@php
  $baseClasses = 'flex items-center w-full px-4 py-2 rounded-md transition-colors focus:outline-none';
  $activeClasses = $active
    ? 'bg-gray-700 text-white'
    : 'text-gray-300 hover:bg-gray-700 hover:text-white';
@endphp

<a
  href="{{ $href }}"
  {{ $attributes->merge(['class' => "$baseClasses $activeClasses"] ) }}
>
  @isset($icon)
    <span class="flex-shrink-0">
      {{ $icon }}
    </span>
  @endisset
  <span class="ml-3 flex-1 text-sm font-medium">
    {{ $slot }}
  </span>
</a>
