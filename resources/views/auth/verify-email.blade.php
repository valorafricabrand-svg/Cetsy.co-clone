@extends('theme.'.theme().'.layouts.app')

@section('title', 'Verify Email Address')

@php
  $user = auth()->user();
  $fromAddress = config('mail.from.address');
  if (!$fromAddress) {
    $host = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();
    $fromAddress = $host ? 'support@'.$host : config('app.name').' support';
  }
@endphp

@section('main')
<section class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-10 md:py-14">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="mx-auto w-full max-w-3xl rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm md:p-6">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
          <i class="fa-solid fa-envelope-circle-check text-lg" aria-hidden="true"></i>
        </div>

        <h1 class="mt-4 text-center text-3xl font-extrabold tracking-tight text-slate-900">Almost there</h1>
        <p class="mt-2 text-center text-sm text-slate-500">Confirm your email to activate your account.</p>

        <p class="mt-6 text-sm leading-6 text-slate-700">
          Thanks for signing up{{ $user?->name ? ', '.$user->name : '' }}. We sent a verification link to
          <span class="font-semibold text-slate-900">{{ $user->email ?? __('your email address') }}</span>.
          Click that link to continue. If you did not receive it, request another email below.
        </p>

        @if (session('status') === 'verification-link-sent')
          <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            A fresh verification link is on its way to your inbox.
          </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-5">
          @csrf
          <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500">
            <i class="fa-solid fa-paper-plane mr-2" aria-hidden="true"></i>
            Resend verification email
          </button>
        </form>

        <p class="mt-4 text-center text-xs text-slate-500">
          Check your spam folder or add <span class="font-semibold text-slate-700">{{ $fromAddress }}</span> to your contacts.
        </p>

        <div class="mt-6 border-t border-slate-200 pt-4 text-center">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center text-sm font-semibold text-rose-600 hover:text-rose-500">
              <i class="fa-solid fa-arrow-right-from-bracket mr-1" aria-hidden="true"></i>
              Log out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
