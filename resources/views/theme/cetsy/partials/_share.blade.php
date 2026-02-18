{{-- resources/views/theme/{{ theme() }}/partials/_share.blade.php --}}
<div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
  <span class="font-semibold text-slate-700">Share:</span>
  <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-500">
    <i class="fa-brands fa-facebook fa-lg"></i>
  </a>
  <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-slate-500">
    <i class="fa-brands fa-x-twitter fa-lg"></i>
  </a>
  <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&media={{ asset('storage/'.$product->featured_image) }}&description={{ urlencode($product->name) }}" target="_blank" rel="noopener" class="text-rose-600 hover:text-rose-500">
    <i class="fa-brands fa-pinterest fa-lg"></i>
  </a>
  <button class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-rose-300 hover:text-rose-600" data-bs-toggle="modal" data-bs-target="#reportModal" title="Report this listing">
    <i class="fa-solid fa-flag mr-1"></i>Report
  </button>
</div>
