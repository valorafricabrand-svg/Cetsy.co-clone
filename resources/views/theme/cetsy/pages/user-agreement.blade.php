@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy User Agreement')

@section('main')
  <section class="py-5">
    <div class="container">
      <h1 class="fw-bold mb-3">Cetsy User Agreement</h1>
      <p class="text-muted">Effective: {{ policy_effective_label() }} (and as noted per section)</p>

      <div class="row g-4">
        <!-- TOC Sidebar -->
        <aside class="col-12 col-lg-3">
          <nav class="toc-sticky card border-0 shadow-sm">
            <div class="card-header bg-light fw-semibold">Contents</div>
            <div class="list-group list-group-flush">
              <a class="list-group-item list-group-item-action" href="#privacy">Privacy Policy</a>
              <a class="list-group-item list-group-item-action" href="#terms">Terms &amp; Conditions</a>
              <a class="list-group-item list-group-item-action" href="#seller-forum">Seller Forum Guidelines</a>
              <a class="list-group-item list-group-item-action" href="#seller-tips">Seller Tips</a>
              <a class="list-group-item list-group-item-action" href="#buyer-tips">Buyer Tips</a>
              <a class="list-group-item list-group-item-action" href="#house-rules">House Rules &amp; Conditions</a>
              <a class="list-group-item list-group-item-action" href="#about-cetsy">About Cetsy</a>
              <a class="list-group-item list-group-item-action" href="#prohibited">Prohibited Items Policy</a>
              <a class="list-group-item list-group-item-action" href="#behavioural">Behavioural Policy</a>
              <a class="list-group-item list-group-item-action" href="#fees">Fees &amp; Commissions</a>
            </div>
          </nav>
        </aside>

        <!-- Main Content -->
        <div class="col-12 col-lg-9">
          <div class="alert alert-info mb-4">
            This page contains the full text of Cetsy’s User Agreement and related policies. Use the contents to jump to a section.
          </div>

          @php($__sec = App\Models\PolicySection::where('slug','privacy')->first())
          <div id="privacy" class="anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.1_privacy') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','terms')->first())
          <div id="terms" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.2_terms') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','seller-forum')->first())
          <div id="seller-forum" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.3_seller_forum') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','seller-tips')->first())
          <div id="seller-tips" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.4_seller_tips') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','buyer-tips')->first())
          <div id="buyer-tips" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.5_buyer_tips') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','house-rules')->first())
          <div id="house-rules" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.6_house_rules') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','about-cetsy')->first())
          <div id="about-cetsy" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.7_about') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','prohibited')->first())
          <div id="prohibited" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.8_prohibited_items') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','behavioural')->first())
          <div id="behavioural" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.9_behavioural') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','fees')->first())
          <div id="fees" class="mt-4 anchor">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.10_fees') !!}</div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('styles')
<style>
  pre { background: #f8f9fa; font-size: 0.95rem; line-height: 1.45; }
  .toc-sticky { position: sticky; top: 88px; }
  .anchor { scroll-margin-top: 96px; }
  .list-group-item-action { font-size: 0.95rem; }
</style>
@endpush
