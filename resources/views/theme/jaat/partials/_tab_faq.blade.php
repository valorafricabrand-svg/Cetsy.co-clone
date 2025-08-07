{{-- resources/views/theme/{{ theme() }}/partials/_tab_faq.blade.php --}}
<div class="tab-pane fade" id="faq-pane" role="tabpanel">
  <div class="accordion" id="faqAccordion">
    @forelse($faqs as $i => $faq)
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading{{ $i }}">
          <button class="accordion-button {{ $i?'collapsed':'' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $i }}">
            {{ $faq->question }}
          </button>
        </h2>
        <div id="faqCollapse{{ $i }}" class="accordion-collapse collapse {{ $i?'':'show' }}" data-bs-parent="#faqAccordion">
          <div class="accordion-body small">{{ $faq->answer }}</div>
        </div>
      </div>
    @empty
      <p class="text-muted small mb-0">Seller hasn’t added any FAQs yet.</p>
    @endforelse
  </div>
</div>