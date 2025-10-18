@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy User Agreement')

@section('main')
  <section class="py-5">
    <div class="container">
      <h1 class="fw-bold mb-3">Cetsy User Agreement</h1>
      <p class="text-muted">Effective: June 2025 (and as noted per section)</p>

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

          <div id="privacy" class="anchor">@include('theme.cetsy.pages.user-agreement.sections.1_privacy')</div>
          <div id="terms" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.2_terms')</div>
          <div id="seller-forum" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.3_seller_forum')</div>
          <div id="seller-tips" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.4_seller_tips')</div>
          <div id="buyer-tips" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.5_buyer_tips')</div>
          <div id="house-rules" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.6_house_rules')</div>
          <div id="about-cetsy" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.7_about')</div>
          <div id="prohibited" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.8_prohibited_items')</div>
          <div id="behavioural" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.9_behavioural')</div>
          <div id="fees" class="mt-4 anchor">@include('theme.cetsy.pages.user-agreement.sections.10_fees')</div>
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

