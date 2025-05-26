{{-- resources/views/shops/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl py-8">
    <h2 class="text-3xl font-bold mb-6 text-center">Edit Your Shop</h2>

    {{-- Flash Success --}}
    @if(session('success'))
      <div class="mb-6 p-4 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
      </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="mb-6 p-4 bg-red-100 text-red-800 rounded">
        <ul class="list-disc pl-5 space-y-1 mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form 
      action="{{ route('shops.update', $shop) }}" 
      method="POST" 
      enctype="multipart/form-data"
      x-data="{ 
        name: '{{ old('name', $shop->name) }}', 
        slug: '{{ old('slug', $shop->slug) }}' 
      }"
      @input.debounce.300ms="
        slug = name.toLowerCase()
                   .trim()
                   .replace(/[^a-z0-9]+/g,'-')
                   .replace(/(^-|-$)/g,'');
      "
      class="space-y-8"
    >
      @csrf
      @method('PATCH')

      {{-- 1) Shop Preferences --}}
      <section class="bg-white shadow rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">1. Shop Preferences</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label for="language" class="block font-medium mb-1">
              Language <span class="text-red-500">*</span>
            </label>
            <select name="language" id="language" required
                    class="w-full border-gray-300 rounded px-3 py-2">
              <option value="" disabled>Select language</option>
              <option value="English" {{ old('language', $shop->language)=='English' ? 'selected':'' }}>
                English
              </option>
              <option value="Spanish" {{ old('language', $shop->language)=='Spanish' ? 'selected':'' }}>
                Spanish
              </option>
              <option value="French" {{ old('language', $shop->language)=='French' ? 'selected':'' }}>
                French
              </option>
            </select>
          </div>
          <div>
            <label for="country" class="block font-medium mb-1">
              Country <span class="text-red-500">*</span>
            </label>
            <select name="country" id="country" required
                    class="w-full border-gray-300 rounded px-3 py-2">
              <option value="" disabled>Select country</option>
              <option value="United States" {{ old('country', $shop->country)=='United States' ? 'selected':'' }}>
                United States
              </option>
              <option value="Canada" {{ old('country', $shop->country)=='Canada' ? 'selected':'' }}>
                Canada
              </option>
              <option value="United Kingdom" {{ old('country', $shop->country)=='United Kingdom' ? 'selected':'' }}>
                United Kingdom
              </option>
            </select>
          </div>
          <div>
            <label for="currency" class="block font-medium mb-1">
              Currency <span class="text-red-500">*</span>
            </label>
            <select name="currency" id="currency" required
                    class="w-full border-gray-300 rounded px-3 py-2">
              <option value="" disabled>Select currency</option>
              <option value="USD" {{ old('currency', $shop->currency)=='USD' ? 'selected':'' }}>
                USD
              </option>
              <option value="CAD" {{ old('currency', $shop->currency)=='CAD' ? 'selected':'' }}>
                CAD
              </option>
              <option value="GBP" {{ old('currency', $shop->currency)=='GBP' ? 'selected':'' }}>
                GBP
              </option>
            </select>
          </div>
        </div>
      </section>

      {{-- 2) Name & Slug --}}
      <section class="bg-white shadow rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">2. Name Your Shop</h3>
        <div class="mb-4">
          <label for="name" class="block font-medium mb-1">
            Shop Name <span class="text-red-500">*</span>
          </label>
          <input 
            id="name" name="name" type="text"
            x-model="name"
            required
            class="w-full border-gray-300 rounded px-3 py-2"
          >
        </div>
        <div>
          <label for="slug" class="block font-medium mb-1">
            Slug (URL Identifier)
          </label>
          <div class="flex items-center space-x-2">
            <span class="text-gray-500">{{ url('shop') }}/</span>
            <input 
              id="slug" name="slug" type="text"
              x-model="slug"
              required
              class="flex-1 bg-gray-100 border-gray-300 rounded px-3 py-2"
            >
          </div>
          <p class="text-sm text-gray-500 mt-1">
            You may customize, but it must be unique.
          </p>
        </div>
      </section>

      {{-- 3) How You’ll Get Paid --}}
      <section class="bg-white shadow rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">3. How You’ll Get Paid</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="bank_account" class="block font-medium mb-1">
              Bank Account # <span class="text-red-500">*</span>
            </label>
            <input 
              id="bank_account" name="bank_account" type="text"
              value="{{ old('bank_account', $shop->bank_account) }}"
              required
              class="w-full border-gray-300 rounded px-3 py-2"
            >
          </div>
          <div>
            <label for="routing_number" class="block font-medium mb-1">
              Routing # <span class="text-red-500">*</span>
            </label>
            <input 
              id="routing_number" name="routing_number" type="text"
              value="{{ old('routing_number', $shop->routing_number) }}"
              required
              class="w-full border-gray-300 rounded px-3 py-2"
            >
          </div>
          
        </div>
      </section>

      

      {{-- 4) Share Your Billing Info --}}
      <section class="bg-white shadow rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">4. Share Your Billing Info</h3>
        <div class="mb-4">
          <label for="address" class="block font-medium mb-1">
            Street Address <span class="text-red-500">*</span>
          </label>
          <input 
            id="address" name="address" type="text"
            value="{{ old('address', $shop->address) }}"
            required
            class="w-full border-gray-300 rounded px-3 py-2"
          >
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="city" class="block font-medium mb-1">
              City <span class="text-red-500">*</span>
            </label>
            <input 
              id="city" name="city" type="text"
              value="{{ old('city', $shop->city) }}"
              required
              class="w-full border-gray-300 rounded px-3 py-2"
            >
          </div>
          <div>
            <label for="postal" class="block font-medium mb-1">
              Postal Code <span class="text-red-500">*</span>
            </label>
            <input 
              id="postal" name="postal" type="text"
              value="{{ old('postal', $shop->postal) }}"
              required
              class="w-full border-gray-300 rounded px-3 py-2"
            >
          </div>
        </div>
      </section>

      {{-- 5) Your Shop Security --}}
      <section class="bg-white shadow rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">5. Your Shop Security</h3>
        <div class="mb-4">
          <label for="password" class="block font-medium mb-1">
            Confirm Your Password <span class="text-red-500">*</span>
          </label>
          <input 
            id="password" name="password" type="password"
            required
            class="w-full border-gray-300 rounded px-3 py-2"
            placeholder="Enter your account password"
          >
        </div>
        <input type="hidden" name="enable_2fa" value="0">
        <div class="flex items-center">
          <input 
            id="enable_2fa" name="enable_2fa" type="checkbox" value="1"
            {{ old('enable_2fa', $shop->enable_2fa) ? 'checked' : '' }}
            class="h-4 w-4 text-green-600"
          >
          <label for="enable_2fa" class="ml-2 text-sm text-gray-700">
            Enable two-factor authentication
          </label>
        </div>

        <div class="mt-4">
          <label for="logo" class="block font-medium mb-1">Logo (optional)</label>
          <input 
            id="logo"
            type="file" 
            name="logo" 
            accept="image/*"
            class="w-full"
          >
          @if($shop->logo_url)
            <p class="mt-2 text-sm text-gray-500">
              Current logo:
              <img src="{{ $shop->logo_url }}" alt="logo" class="inline-block w-10 h-10 ml-2 rounded-full">
            </p>
          @endif
        </div>
      </section>

      {{-- Submit --}}
      <div class="text-right">
        <button 
          type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded"
        >
          Save Changes
        </button>
      </div>
    </form>
</div>
@endsection
