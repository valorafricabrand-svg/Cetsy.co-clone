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
<section class="py-5 verify-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden verify-card">
                    <div class="card-header text-center bg-success bg-gradient py-4">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                        </div>
                        <h2 class="h4 fw-bold text-white mb-0">Almost there!</h2>
                        <p class="text-white-50 small mb-0">Confirm your email to access your account.</p>
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        <p class="text-muted mb-4">
                            Thanks for signing up{{ $user?->name ? ', '.$user->name : '' }}!
                            We just sent a verification link to
                            <span class="fw-semibold text-dark">{{ $user->email ?? __('your email address') }}</span>.
                            Click the link in that email to activate your account. Didn't get it?
                            You can request another message below.
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>A fresh verification link is on its way to your inbox.</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('verification.send') }}" class="d-grid gap-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i class="fa-solid fa-paper-plane me-2"></i>Resend verification email
                            </button>
                        </form>

                        <div class="text-center text-muted small mt-4">
                            Check your spam folder or add <strong>{{ $fromAddress }}</strong> to your contacts.
                        </div>

                        <hr class="my-4">

                        <form method="POST" action="{{ route('logout') }}" class="text-center">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger text-decoration-none fw-semibold">
                                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Log out
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .verify-hero {
        min-height: calc(100vh - 140px);
        background: linear-gradient(135deg, #067e46 0%, #044d2c 100%);
        display: flex;
        align-items: center;
    }
    .verify-card .card-header {
        position: relative;
    }
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .verify-card .btn-link {
        font-size: 0.95rem;
    }
    @media (max-width: 767.98px) {
        .verify-hero {
            min-height: auto;
        }
    }
</style>
@endpush
