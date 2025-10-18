@extends('layouts.frontapp')

@section('title', 'Cetsy User Agreement')

@section('main')
  <section class="py-5">
    <div class="container">
      <div class="mx-auto" style="max-width: 900px;">
        <h1 class="fw-bold mb-3">Cetsy User Agreement</h1>
        <p class="text-muted">Effective: June 2025 (and as noted per section)</p>

        <div class="alert alert-info mb-4">
          This page contains the full text of Cetsy’s User Agreement and related policies as provided. Use the quick links below to jump to a section.
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
          <a class="btn btn-sm btn-outline-primary" href="#privacy">Privacy Policy</a>
          <a class="btn btn-sm btn-outline-primary" href="#terms">Terms & Conditions</a>
          <a class="btn btn-sm btn-outline-primary" href="#seller-forum">Seller Forum Guidelines</a>
          <a class="btn btn-sm btn-outline-primary" href="#seller-tips">Seller Tips</a>
          <a class="btn btn-sm btn-outline-primary" href="#buyer-tips">Buyer Tips</a>
          <a class="btn btn-sm btn-outline-primary" href="#house-rules">House Rules & Conditions</a>
          <a class="btn btn-sm btn-outline-primary" href="#about-cetsy">About Cetsy</a>
          <a class="btn btn-sm btn-outline-primary" href="#prohibited">Prohibited Items Policy</a>
          <a class="btn btn-sm btn-outline-primary" href="#behavioural">Behavioural Policy</a>
          <a class="btn btn-sm btn-outline-primary" href="#fees">Fees & Commissions</a>
        </div>

        <div id="privacy">@include('theme.cetsy.pages.user-agreement.sections.1_privacy')</div>
        <div id="terms" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.2_terms')</div>
        <div id="seller-forum" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.3_seller_forum')</div>
        <div id="seller-tips" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.4_seller_tips')</div>
        <div id="buyer-tips" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.5_buyer_tips')</div>
        <div id="house-rules" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.6_house_rules')</div>
        <div id="about-cetsy" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.7_about')</div>
        <div id="prohibited" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.8_prohibited_items')</div>
        <div id="behavioural" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.9_behavioural')</div>
        <div id="fees" class="mt-4">@include('theme.cetsy.pages.user-agreement.sections.10_fees')</div>
      </div>
    </div>
  </section>
@endsection

@push('styles')
<style>
  pre { background: #f8f9fa; font-size: 0.95rem; line-height: 1.45; }
</style>
@endpush

