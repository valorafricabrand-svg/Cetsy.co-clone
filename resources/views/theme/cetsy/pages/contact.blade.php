@extends('theme.'.theme().'.layouts.app')

@section('title', 'Contact Us')
@section('meta_description', 'Contact Cetsy support for marketplace help, order questions, seller support, policy questions, and general inquiries.')
@section('canonical_url', route('contact'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
<div class="relative overflow-x-clip pb-10">
  <div class="pointer-events-none absolute -right-20 -top-16 h-72 w-72 rounded-full bg-emerald-200/35 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-16 top-[24rem] h-64 w-64 rounded-full bg-cyan-200/30 blur-3xl"></div>

  <section class="relative bg-gradient-to-b from-emerald-900 to-emerald-700 py-12 text-white">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8">
      <h1 class="text-4xl font-extrabold leading-tight md:text-5xl">Contact Us</h1>
      <p class="mt-3 max-w-3xl text-sm text-emerald-50/95 md:text-base">
        We are here to help with orders, payments, refunds, disputes, and account issues.
      </p>
    </div>
  </section>

  <section class="bg-slate-50 py-6">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8">
      @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {{ session('success') }}
        </div>
      @endif

      @if(session('danger'))
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
          {{ session('danger') }}
        </div>
      @endif

      @if($errors->any())
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
          <ul class="list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Send Us a Message</h2>

        <form method="POST" action="{{ route('contact.submit') }}" novalidate class="mt-4 space-y-4">
          @csrf

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label for="contact_name" class="mb-1 block text-sm font-medium text-slate-700">Full Name</label>
              <input
                id="contact_name"
                type="text"
                name="name"
                maxlength="120"
                required
                value="{{ old('name', auth()->check() ? auth()->user()->name : '') }}"
                class="w-full rounded-xl border px-3 py-2 text-sm text-slate-700 focus:outline-none {{ $errors->has('name') ? 'border-rose-400 ring-1 ring-rose-200' : 'border-slate-300 focus:border-emerald-500' }}"
              >
              @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="contact_email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
              <input
                id="contact_email"
                type="email"
                name="email"
                maxlength="255"
                required
                value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                class="w-full rounded-xl border px-3 py-2 text-sm text-slate-700 focus:outline-none {{ $errors->has('email') ? 'border-rose-400 ring-1 ring-rose-200' : 'border-slate-300 focus:border-emerald-500' }}"
              >
              @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label for="contact_subject" class="mb-1 block text-sm font-medium text-slate-700">Subject</label>
              <input
                id="contact_subject"
                type="text"
                name="subject"
                maxlength="160"
                required
                value="{{ old('subject') }}"
                class="w-full rounded-xl border px-3 py-2 text-sm text-slate-700 focus:outline-none {{ $errors->has('subject') ? 'border-rose-400 ring-1 ring-rose-200' : 'border-slate-300 focus:border-emerald-500' }}"
              >
              @error('subject')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="contact_order_number" class="mb-1 block text-sm font-medium text-slate-700">Order Number (Optional)</label>
              <input
                id="contact_order_number"
                type="text"
                name="order_number"
                maxlength="120"
                value="{{ old('order_number') }}"
                class="w-full rounded-xl border px-3 py-2 text-sm text-slate-700 focus:outline-none {{ $errors->has('order_number') ? 'border-rose-400 ring-1 ring-rose-200' : 'border-slate-300 focus:border-emerald-500' }}"
              >
              @error('order_number')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="hidden" aria-hidden="true">
            <label for="contact_website" class="mb-1 block text-sm font-medium text-slate-700">Website</label>
            <input
              id="contact_website"
              type="text"
              name="website"
              tabindex="-1"
              autocomplete="off"
              value=""
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700"
            >
          </div>

          <div>
            <label for="contact_message" class="mb-1 block text-sm font-medium text-slate-700">Message</label>
            <textarea
              id="contact_message"
              name="message"
              rows="6"
              maxlength="5000"
              required
              class="w-full rounded-xl border px-3 py-2 text-sm text-slate-700 focus:outline-none {{ $errors->has('message') ? 'border-rose-400 ring-1 ring-rose-200' : 'border-slate-300 focus:border-emerald-500' }}"
            >{{ old('message') }}</textarea>
            @error('message')
              <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-slate-500 sm:text-sm">Please do not include card numbers or passwords.</p>
            <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
              Send Message
            </button>
          </div>
        </form>
      </div>

      <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Order Help</h2>
        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
          <li>For delivery questions, contact the seller first (each seller ships their own items).</li>
          <li>If you cannot resolve an issue with a seller, open a dispute from your order page and we will assist.</li>
        </ul>
      </div>
    </div>
  </section>
</div>
@endsection
