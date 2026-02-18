{{-- resources/views/theme/{{ theme() }}/partials/_tab_faq.blade.php --}}
<div class="listing-tab-pane hidden" id="faq-pane" role="tabpanel">
  <div class="space-y-3">
    @forelse($faqs as $i => $faq)
      <details class="group rounded-2xl border border-slate-200 bg-white p-4" {{ $i ? '' : 'open' }}>
        <summary class="cursor-pointer list-none pr-6 text-sm font-semibold text-slate-900">
          {{ $faq->question }}
        </summary>
        <div class="mt-2 text-sm text-slate-600">
          {{ $faq->answer }}
        </div>
      </details>
    @empty
      <p class="text-sm text-slate-500">Seller hasn't added any FAQs yet.</p>
    @endforelse
  </div>
</div>
