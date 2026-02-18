@extends('theme.'.theme().'.layouts.app')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    <h2 class="text-2xl font-semibold mb-4">Add Service</h2>
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
    <form action="{{ route('seller.services.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
            <div class="border-b border-slate-200 px-4 py-3">Listing details</div>
            <div class="p-4">
                <div class="mb-3">
                    <label for="name" class="form-label">Service Name <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="name" name="name"  placeholder="Service name" value="{{ old('name') }}">
                    
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="phone" name="phone" required placeholder="+254 xxx xxx xxx" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="price" name="price" required placeholder="Price" value="{{ old('price') }}">
                </div>
                <div class="mb-3">
                    <label for="price_type" class="form-label">Price Type <span class="text-rose-600">Required</span></label>
                    <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="price_type" name="price_type" required>
                        <option value="">Select price type</option>
                        <option value="fixed" {{ old('price_type') == 'fixed' ? 'selected' : '' }}>Fixed Price</option>
                        <option value="hourly" {{ old('price_type') == 'hourly' ? 'selected' : '' }}>Hourly Rate</option>
                        <option value="negotiable" {{ old('price_type') == 'negotiable' ? 'selected' : '' }}>Negotiable</option>
                        <option value="per_item" {{ old('price_type') == 'per_item' ? 'selected' : '' }}>Per Item</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Service Provider Email Address <span class="text-rose-600">Required</span></label>
                    <input type="email" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="email" name="email" required placeholder="Service Provider Email Address" value="{{ old('email') }}">
                </div>
                <div class="mb-3">
                    <label for="tags" class="form-label">Tags <span class="text-slate-500">Optional</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="tags" name="tags" placeholder="e.g. Cleaning, Medical, Dog-Walking" value="{{ old('tags') }}">
                    <span class="form-text text-slate-500 text-xs">Add up to 13 tags to help people find your service.</span>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category <span class="text-rose-600">Required</span></label>
                    <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="category_id" name="category_id" required>
                        <option value="">--choose--</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                        <label for="origin_id" class="form-label">Country <span class="text-rose-600">Required</span></label>
                        <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="origin_id" name="origin_id" required>
                        <option value="">--choose--</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Service Description <span class="text-rose-600">Required</span></label>
                    <textarea class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                    <span class="form-text text-slate-500 text-xs">Make sure the service description provides a detailed explanation of your service so that it is easy to understand and find your service. It is recommended not to enter info on mobile numbers, e-mails, etc. into the service description to protect your personal data.</span>
                </div>
                
                
                
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
            <div class="border-b border-slate-200 px-4 py-3">Location & Availability</div>
            <div class="p-4">
                <div class="mb-3">
                    <label for="service_area" class="form-label">Service Area <span class="text-rose-600">Required</span></label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="location" name="location" required placeholder="e.g., Nairobi, Remote, Global" value="{{ old('location') }}">
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1" {{ old('is_remote') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_remote">
                            This service can be provided remotely
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Available Days <span class="text-rose-600">Required</span></label>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
                        @php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $oldDays = old('available_days', []);
                        @endphp
                        @foreach($days as $day)
                            <div class="w-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="day_{{ strtolower($day) }}" 
                                           name="available_days[]" 
                                           value="{{ $day }}"
                                           {{ in_array($day, $oldDays) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                        {{ $day }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="col-span-6">
                        <div class="mb-3">
                            <label for="available_time_from" class="form-label">Available Hours - Start <span class="text-rose-600">Required</span></label>
                            <input type="time" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="available_time_from" name="available_time_from" required value="{{ old('available_time_from') }}">
                        </div>
                    </div>
                    <div class="col-span-6">
                        <div class="mb-3">
                            <label for="available_time_to" class="form-label">Available Hours - End <span class="text-rose-600">Required</span></label>
                            <input type="time" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="available_time_to" name="available_time_to" required value="{{ old('available_time_to') }}">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="service_duration" class="form-label">Service Duration <span class="text-rose-600">Required</span></label>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                        <div class="col-span-6">
                            <input type="number" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="duration_value" name="duration_value" required placeholder="Duration" value="{{ old('duration_value') }}">
                        </div>
                        <div class="col-span-6">
                            <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" id="duration_unit" name="duration_unit" required>
                                <option value="">Select Unit</option>
                                <option value="minutes" {{ old('duration_unit') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                <option value="hours" {{ old('duration_unit') == 'hours' ? 'selected' : '' }}>Hours</option>
                                <option value="days" {{ old('duration_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                <option value="weeks" {{ old('duration_unit') == 'weeks' ? 'selected' : '' }}>Weeks</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
            <div class="border-b border-slate-200 px-4 py-3">Photos</div>
            <div class="p-4">
                <p class="text-slate-500">Avoid offering services that are violating Intellectual Property Rights, so that your services are not blacklisted.</p>
                <div class="mb-3">
                    <label for="photos" class="form-label">Service Photos <span class="text-rose-600">Required</span></label>
                    <input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" type="file" id="photos" name="photos[]" multiple required>
                </div>
            </div>
        </div>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">Save</button>
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





