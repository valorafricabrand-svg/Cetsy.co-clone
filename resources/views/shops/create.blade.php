@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-lg py-8">
    <h2 class="text-2xl font-bold mb-6">Create Your Shop</h2>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form 
        action="{{ route('shops.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
        x-data="{ name: @entangle('oldName') || '', slug: @entangle('oldSlug') || '' }"
        @input.debounce.500ms="
            slug = name.toLowerCase()
                       .replace(/[^a-z0-9]+/g,'-')
                       .replace(/(^-|-$)/g,'');
        "
    >
        @csrf

        {{-- Shop Name --}}
        <div class="mb-4">
            <label for="name" class="block font-medium mb-1">Shop Name</label>
            <input 
                id="name"
                type="text" 
                x-model="name" 
                name="name" 
                value="{{ old('name') }}"
                class="w-full border rounded px-3 py-2"
                placeholder="e.g. My Craft Shop"
                required
            >
        </div>

        {{-- Slug --}}
        <div class="mb-4">
            <label for="slug" class="block font-medium mb-1">Slug (URL Identifier)</label>
            <input 
                id="slug"
                type="text" 
                x-model="slug" 
                name="slug" 
                value="{{ old('slug') }}"
                class="w-full border rounded px-3 py-2 bg-gray-100"
                readonly
            >
            <small class="text-gray-500">
                Your shop URL will be: 
                <code>{{ url('shop') }}/<span x-text="slug"></span></code>
            </small>
        </div>

        {{-- Bio --}}
        <div class="mb-4">
            <label for="bio" class="block font-medium mb-1">Bio / Description</label>
            <textarea 
                id="bio"
                name="bio" 
                rows="4"
                class="w-full border rounded px-3 py-2"
                placeholder="Tell your buyers about your shop..."
            >{{ old('bio') }}</textarea>
        </div>

        {{-- Logo --}}
        <div class="mb-6">
            <label for="logo" class="block font-medium mb-1">Logo (optional)</label>
            <input 
                id="logo"
                type="file" 
                name="logo" 
                accept="image/*"
                class="w-full"
            >
        </div>

        {{-- Submit --}}
        <button 
            type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
        >
            Create Shop
        </button>
    </form>
</div>
@endsection
