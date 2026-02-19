@extends('theme.'.theme().'.layouts.app')
@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 lg:col-span-9">
                <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <h3 class="text-2xl font-semibold text-slate-900">Account Details</h3>
                    <p class="mt-1 text-slate-500">Update your profile information.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <form action="{{ route('account.updateDetails') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                            <input type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" id="name" name="name" value="{{ Auth::user()->name }}" placeholder="Enter your name">
                        </div>
                        <div>
                            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
                            <input type="email" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" id="email" name="email" value="{{ Auth::user()->email }}" placeholder="Enter your email">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Update Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
