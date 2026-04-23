{{-- resources/views/theme/{{ theme() }}/partials/_share.blade.php --}}
@php
  $shareUrl = url()->current();
  $shareTitle = trim((string) ($product->localized_name ?? $product->name ?? 'Listing'));
  $shareImage = function_exists('product_thumb_url') ? product_thumb_url($product) : media_url($product->featured_image ?? null);
  $shareText = trim($shareTitle . ' - ' . $shareUrl);
  $emailSubject = rawurlencode('Check out this listing: ' . $shareTitle);
  $emailBody = rawurlencode("I thought you might like this listing:\n\n{$shareTitle}\n{$shareUrl}");
@endphp

<div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
  <span class="font-semibold text-slate-700">Share:</span>

  <a
    href="https://wa.me/?text={{ urlencode($shareText) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="inline-flex items-center rounded-full border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
    aria-label="Share this listing on WhatsApp"
    title="Share on WhatsApp"
  >
    <i class="fa-brands fa-whatsapp mr-1.5"></i>WhatsApp
  </a>

  <a
    href="mailto:?subject={{ $emailSubject }}&body={{ $emailBody }}"
    class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700"
    aria-label="Share this listing by email"
    title="Share by email"
  >
    <i class="fa-solid fa-envelope mr-1.5"></i>Email
  </a>

  <a
    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="text-blue-600 hover:text-blue-500"
    aria-label="Share this listing on Facebook"
    title="Share on Facebook"
  >
    <i class="fa-brands fa-facebook fa-lg"></i>
  </a>

  <a
    href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="text-slate-700 hover:text-slate-500"
    aria-label="Share this listing on X"
    title="Share on X"
  >
    <i class="fa-brands fa-x-twitter fa-lg"></i>
  </a>

  <a
    href="https://pinterest.com/pin/create/button/?url={{ urlencode($shareUrl) }}&media={{ urlencode($shareImage) }}&description={{ urlencode($shareTitle) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="text-rose-600 hover:text-rose-500"
    aria-label="Share this listing on Pinterest"
    title="Share on Pinterest"
  >
    <i class="fa-brands fa-pinterest fa-lg"></i>
  </a>

  <button
    type="button"
    class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-rose-300 hover:text-rose-600"
    data-tw-modal-open="reportModal"
    title="Report this listing"
  >
    <i class="fa-solid fa-flag mr-1"></i>Report
  </button>
</div>
