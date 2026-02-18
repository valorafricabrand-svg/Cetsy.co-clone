@extends('theme.'.theme().'.layouts.app')
@section('title', 'Create Offer')
@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 py-4">
    <h1 class="text-xl font-semibold mb-4">Create Offer</h1>
    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">This is the create offer page. Add your form here.</div>
</div>
      </div>
    </div>
  </div>
</section>
@endsection 


