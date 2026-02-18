@extends('theme.'.theme().'.layouts.app')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    <h2 class="text-2xl font-semibold mb-4">Edit Service</h2>
    @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))   
        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any()) 
        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>   
        </div>
    @endif
    <form action="{{ route('seller.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
            <div class="border-b border-slate-200 px-4 py-3">Photos</div>
            <div class="p-4">
                <p class="text-slate-500">Avoid offering services that are violating Intellectual Property Rights, so that your services are not blacklisted.</p>
                <div class="mb-3">
                    <label for="photos" class="form-label">Service Photos <span class="text-rose-600">Required</span></label>
                    <input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" type="file" id="photos" name="photos[]" multiple>
                    <div class="mt-2">
                        @foreach($service->media as $media)
                            <img src="{{ asset('storage/'.$media->url) }}" alt="Service Image" style="height: 60px; margin-right: 8px;">
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
            <div class="border-b border-slate-200 px-4 py-3">Listing details</div>
            <div class="p-4">
                <div class="mb-3">
                    <label for="name" class="form-label">Service Name <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="name" name="name" placeholder="Service name" value="{{ old('name', $service->name) }}">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="phone" name="phone" placeholder="+254 xxx xxx xxx" value="{{ old('phone', $service->phone) }}">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Service Provider Email Address <span class="text-rose-600">Required</span></label>
                    <input type="email" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="email" name="email" placeholder="Service Provider Email Address" value="{{ old('email', $service->email) }}">
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Service Provider Geographical Location <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="location" name="location" placeholder="Service Provider Geographical Location" value="{{ old('location', $service->location) }}">
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price <span class="text-rose-600">Required</span></label>
                    <input type="number" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="price" name="price" min="0" step="0.01" value="{{ old('price', $service->price) }}">
                </div>
                <div class="mb-3">
                    <label for="origin_id" class="form-label">Country of Origin <span class="text-rose-600">Required</span></label>
                    <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="origin_id" name="origin_id" required>
                        <option value="">--choose--</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" {{ old('origin_id', $service->origin_id) == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Service Description <span class="text-rose-600">Required</span></label>
                    <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="description" name="description" rows="5" required>{{ old('description', $service->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="tags" class="form-label">Tags <span class="text-slate-500">Optional</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="tags" name="tags" placeholder="e.g. Cleaning, Medical, Dog-Walking" value="{{ old('tags', $service->tags) }}">
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category <span class="text-rose-600">Required</span></label>
                    <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="category_id" name="category_id" required>
                        <option value="">--choose--</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Renewal Options <span class="text-rose-600">Required</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="renewal_option" id="renewal_auto" value="0" required {{ old('renewal_option', $service->renewal_option) == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="renewal_auto">Automatic (recommended)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="renewal_option" id="renewal_manual" value="1" required {{ old('renewal_option', $service->renewal_option) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="renewal_manual">Manual</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="listTypeFee_id" class="form-label">Listing Fee Renewal <span class="text-rose-600">Required</span></label>
                    <select name="listTypeFee_id" id="listTypeFee_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
                   
                        @foreach ($category_listFee_types as $listType)
                            <option value="{{ $listType->id }}" {{ old('listTypeFee_id', $service->listTypeFee_id) == $listType->id ? 'selected' : '' }}>{{ $listType->name }}</option>
                        @endforeach
                    </select>
                    @error('listTypeFee_id')
                        <span class="text-rose-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">Update</button>
    </form>
</div>
      </div>
    </div>
  </div>
</section>
@endsection 

@push('scripts')
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
(function(){
  function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  onReady(function(){
    const el = document.getElementById('description');
    if(!el) return;
    const start = function(){
      try{ const i=tinymce.get('description'); if(i) i.remove(); }catch(_){}
      tinymce.init({
        selector:'#description',
        height:400,
        menubar:true,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount quickbars emoticons autoresize',
        toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media | code',
        branding:false,
        browser_spellcheck:true,
        gecko_spellcheck:true,
        elementpath:false,
        base_url: '{{ asset('assets/js/tinymce') }}',
        setup(editor){ editor.on('change', () => editor.save()); }
      });
    };
    if(window.tinymce){ start(); }
    else {
      const s=document.createElement('script');
      s.src='https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
      s.referrerPolicy='origin';
      s.onload=start;
      s.onerror=function(){ console.warn('TinyMCE CDN failed to load'); };
      document.head.appendChild(s);
    }
  });
})();
</script>
@endpush



