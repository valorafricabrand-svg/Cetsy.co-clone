@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy User Agreement')
@section('meta_description', 'Read the Cetsy user agreement and related marketplace policies for buyers, sellers, privacy, fees, conduct, and prohibited items.')
@section('canonical_url', route('user-agreement'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
      <header class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Cetsy User Agreement</h1>
        <p class="mt-2 text-sm text-slate-500">Effective: {{ policy_effective_label() }} (and as noted per section)</p>
      </header>

      <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
        <aside>
          <nav class="toc-sticky rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" aria-label="User agreement contents">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.08em] text-slate-500">Contents</p>
            <div class="space-y-1">
              <a class="toc-link" href="#privacy">Privacy Policy</a>
              <a class="toc-link" href="#terms">Terms &amp; Conditions</a>
              <a class="toc-link" href="#seller-forum">Seller Forum Guidelines</a>
              <a class="toc-link" href="#seller-tips">Seller Tips</a>
              <a class="toc-link" href="#buyer-tips">Buyer Tips</a>
              <a class="toc-link" href="#house-rules">House Rules &amp; Conditions</a>
              <a class="toc-link" href="#about-cetsy">About Cetsy</a>
              <a class="toc-link" href="#prohibited">Prohibited Items Policy</a>
              <a class="toc-link" href="#behavioural">Behavioural Policy</a>
              <a class="toc-link" href="#fees">Fees &amp; Commissions</a>
            </div>
          </nav>
        </aside>

        <div class="space-y-4">
          <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            This page contains the full text of Cetsy's User Agreement and related policies. Use the contents to jump to a section.
          </div>

          @php($__sec = App\Models\PolicySection::where('slug','privacy')->first())
          <div id="privacy" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.1_privacy') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','terms')->first())
          <div id="terms" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.2_terms') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','seller-forum')->first())
          <div id="seller-forum" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.3_seller_forum') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','seller-tips')->first())
          <div id="seller-tips" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.4_seller_tips') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','buyer-tips')->first())
          <div id="buyer-tips" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.5_buyer_tips') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','house-rules')->first())
          <div id="house-rules" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.6_house_rules') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','about-cetsy')->first())
          <div id="about-cetsy" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.7_about') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','prohibited')->first())
          <div id="prohibited" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.8_prohibited_items') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','behavioural')->first())
          <div id="behavioural" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.9_behavioural') !!}</div>

          @php($__sec = App\Models\PolicySection::where('slug','fees')->first())
          <div id="fees" class="anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">{!! $__sec && trim((string)$__sec->content) !== '' ? $__sec->content : view('theme.cetsy.pages.user-agreement.sections.10_fees') !!}</div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('styles')
<style>
  .toc-sticky { position: sticky; top: 92px; }
  .anchor { scroll-margin-top: 108px; }
  .toc-link {
    display: block;
    border-radius: 0.75rem;
    padding: 0.55rem 0.7rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: rgb(51 65 85);
    transition: background-color .15s ease, color .15s ease;
  }
  .toc-link:hover {
    background: rgb(240 253 250);
    color: rgb(6 95 70);
  }
</style>
@endpush
