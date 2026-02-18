@extends('theme.'.theme().'.layouts.app')
@section('main')
<div class="content-wrapper p-4">
    <h3 class="mb-4 text-slate-900">Account Details</h3>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
        <div class="p-4 sm:p-5">
            <form action="{{ route('account.updateDetails') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" id="name" name="name" value="{{ Auth::user()->name }}" placeholder="Enter your name">
                </div>
                <div class="mb-3">
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
                    <input type="email" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" id="email" name="email" value="{{ Auth::user()->email }}" placeholder="Enter your email">
                </div>
                <div class="d-grid">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Update Details</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



