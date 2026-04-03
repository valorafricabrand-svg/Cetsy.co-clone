@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
    <h2 class="text-center font-bold mb-5">Edit Your Shop</h2>

    {{-- Flash Success --}}
    @if(session('success'))
      <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">
        {{ session('success') }}
      </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form 
      action="{{ route('seller.shops.update', $shop) }}" 
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
    >
      @csrf
      @method('PATCH')

      {{-- 1) Shop Preferences --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">1. Shop Preferences</div>
        <div class="p-4 sm:p-5 grid grid-cols-12 gap-4 gap-3">
          <div class="col-span-12 md:col-span-4">
            <label for="language" class="mb-1 block text-sm font-medium text-slate-700">Language <span class="text-rose-600">*</span></label>
            <select name="language" id="language" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
              <option value="" disabled selected>Select language</option>
              <option value="English" {{ old('language', $shop->language)=='English'?'selected':'' }}>English</option>
              <option value="Swahili" {{ old('language', $shop->language)=='Swahili'?'selected':'' }}>Swahili</option>
            </select>
            <div class="mt-1 text-xs text-rose-600">Please select a language.</div>
          </div>
          <div class="col-span-12 md:col-span-4">
            <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Country <span class="text-rose-600">*</span></label>
            <select name="country" id="country" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
              <option value="" disabled selected>Select country</option>
              @foreach($countries as $country)
                <option value="{{ $country->id }}" {{ old('country', $shop->country)==$country->id?'selected':'' }}>{{ $country->name }}</option>
              @endforeach
            </select>
            <div class="mt-1 text-xs text-rose-600">Please select a country.</div>
          </div>
          <div class="col-span-12 md:col-span-4">
            <label for="currency" class="mb-1 block text-sm font-medium text-slate-700">
              Currency <span class="text-rose-600">*</span>
            </label>
            <select
              id="currency"
              name="currency"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('currency') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
              required
            >
              <option value="" disabled {{ old('currency', $shop->currency) ? '' : 'selected' }}>
                Select a currency
              </option>
              @foreach(currencies() as $code => $name)
                <option
                  value="{{ $code }}"
                  {{ old('currency', $shop->currency) === $code ? 'selected' : '' }}
                >
                  ({{ $code }})
                </option>
              @endforeach
            </select>
            @error('currency')
              <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
            @else
              <div class="mt-1 text-xs text-rose-600">Please select a currency.</div>
            @enderror
          </div>

        </div>
      </div>

      {{-- 2) Name & Slug --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">2. Name Your Shop</div>
        <div class="p-4 sm:p-5">
          <div class="mb-3">
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Shop Name <span class="text-rose-600">*</span></label>
            <input type="text" id="name" name="name" x-model="name" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
          </div>
          <div>
            <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">Slug (URL Identifier)</label>
            <div class="flex w-full items-stretch">
              <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">{{ url('shop') }}/</span>
              <input type="text" id="slug" name="slug" x-model="slug" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
            <div class="mt-1 text-xs text-slate-500">You may customize, but it must be unique.</div>
          </div>
        </div>
      </div>

      {{-- 3) Shop Description --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">3. Describe Your Shop</div>
        <div class="p-4 sm:p-5">
          <div class="col-span-12">
          <label for="bio" class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Description</label>
            <textarea id="bio" name="bio" rows="6"
                      class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('bio') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">{{ old('bio',$shop->bio) }}</textarea>
            @error('bio')<div class="text-rose-600 mt-1">{{ $message }}</div>@enderror
            <div class="mt-1 text-xs text-slate-500">Tell customers about your shop and what makes it special.</div>
          </div>
        </div>
      </div>

      {{-- 3b) Shop Announcement --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">3b. Shop Announcement</div>
        <div class="p-4 sm:p-5">
          <div class="mb-3">
            <label for="announcement" class="mb-1 block text-sm font-medium text-slate-700">Shop Announcement</label>
            <textarea id="announcement" name="announcement" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="2">{{ old('announcement', $shop->announcement) }}</textarea>
            <div class="mt-1 text-xs text-slate-500">This announcement will appear at the top of your shop page.</div>
          </div>
        </div>
      </div>

      {{-- 3c) Shop Policies --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">3c. Shop Policies</div>
        <div class="p-4 sm:p-5">
          <div class="mb-3">
            <label for="policies" class="mb-1 block text-sm font-medium text-slate-700">Shop Policies</label>
            <textarea id="policies" name="policies" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3">{{ old('policies', $shop->policies) }}</textarea>
            <div class="mt-1 text-xs text-slate-500">Describe your shop's return, shipping, and other important policies.</div>
          </div>
        </div>
      </div>

      {{-- 4) Billing Info --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">4. Share Your Billing Info</div>
        <div class="p-4 sm:p-5">
          <div class="mb-3">
            <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Street Address <span class="text-rose-600">*</span></label>
            <input type="text" id="address" name="address" value="{{ old('address', $shop->address) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
          </div>
          <div class="grid grid-cols-12 gap-4 gap-3">
            <div class="col-span-12 md:col-span-6">
              <label for="city" class="mb-1 block text-sm font-medium text-slate-700">City <span class="text-rose-600">*</span></label>
              <input type="text" id="city" name="city" value="{{ old('city', $shop->city) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
            <div class="col-span-12 md:col-span-6">
              <label for="postal" class="mb-1 block text-sm font-medium text-slate-700">Postal Code <span class="text-rose-600">*</span></label>
              <input type="text" id="postal" name="postal" value="{{ old('postal', $shop->postal) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
          </div>
        </div>
      </div>

      {{-- 5) Security --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-4 py-3 font-semibold">5. Your Shop Security</div>
        <div class="p-4 sm:p-5">
          <div class="mb-3">
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Confirm Your Password <span class="text-rose-600">*</span></label>
            <div class="flex w-full items-stretch">
              <input type="password" id="password" name="password" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required placeholder="Enter your account password">
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" type="button" id="togglePassword">
                <i class="fas fa-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>
          <input type="hidden" name="enable_2fa" value="0">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="enable_2fa" name="enable_2fa" {{ old('enable_2fa', $shop->enable_2fa) ? 'checked' : '' }}>
            <label class="form-check-label" for="enable_2fa">Enable two-factor authentication</label>
          </div>

          <div class="mt-4">
            <label for="logo" class="mb-1 block text-sm font-medium text-slate-700">Logo <span class="text-slate-500">(optional)</span></label>
            <input class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('logo') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" type="file" id="logo" name="logo" accept="image/*">
            @error('logo')
              <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
            @else
              <div class="mt-1 text-xs text-slate-500">Upload your shop logo now or leave it blank and add it later. Recommended size: 200x200 pixels.</div>
            @enderror
            @if($shop->logo)
              <div class="mt-2">
                <img src="{{ asset('storage/' . $shop->logo) }}" alt="logo" class="rounded-full" width="50" height="50">
              </div>
            @endif
          </div>

          <div class="mt-4">
            <label for="featured_image" class="mb-1 block text-sm font-medium text-slate-700">Featured Image (optional)</label>
            <input class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" type="file" id="featured_image" name="featured_image" accept="image/*">
            <div class="mt-1 text-xs text-slate-500">This image will be displayed prominently on your shop page. Recommended size: 1200x400 pixels.</div>
            @if($shop->featured_image)
              <div class="mt-2">
                <img src="{{ asset('storage/' . $shop->featured_image) }}" alt="featured image" class="img-fluid rounded" style="max-width: 300px; max-height: 150px; object-fit: cover;">
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <div class="text-right">
        <a href="{{ route('seller.shops.show', $shop) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500 px-4">Cancel</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-4">Save Changes</button>
      </div>
    </form>
</div>

<!-- Ensure jQuery is loaded before Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('#currency').select2({
    placeholder: 'Select a currency',
    width: '100%',
    minimumResultsForSearch: 0 // Always show search box
  });
  $('#country').select2({
    placeholder: 'Select country',
    width: '100%',
    minimumResultsForSearch: 0 // Always show search box
  });
});
</script>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
  // TinyMCE with CDN fallback and DOM-ready init
  (function(){
    function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
    onReady(function(){
      const selector = '#bio,#announcement,#policies';
      const start = function(){
        try {
          ['bio','announcement','policies'].forEach(function(id){ const inst = tinymce.get(id); if (inst) inst.remove(); });
        } catch(_) {}
        tinymce.init({
          selector: selector,
          plugins: 'image link media code fullscreen',
          toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | image link media | code fullscreen',
          menubar: false,
          height: 300,
          branding: false,
        elementpath: false,
        base_url: '{{ asset('assets/js/tinymce') }}'
        });
      };
      if (window.tinymce) { start(); }
      else {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
        s.referrerPolicy = 'origin';
        s.onload = start;
        s.onerror = function(){ console.warn('TinyMCE CDN failed to load'); };
        document.head.appendChild(s);
      }
    });
  })();

  // Password visibility toggle
  document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.classList.remove('fa-eye');
      eyeIcon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      eyeIcon.classList.remove('fa-eye-slash');
      eyeIcon.classList.add('fa-eye');
    }
  });

  // Toggle sections
  document.getElementById('type').addEventListener('change', function(){
    const show = { digital: ['digitalFileSection'], physical: ['stockSection','shippingProfilesSection'] };
    ['stockSection','digitalFileSection','shippingProfilesSection'].forEach(id=>{
      document.getElementById(id).style.display = show[this.value]?.includes(id) ? 'block' : 'none';
    });
  });
  window.addEventListener('DOMContentLoaded', ()=> {
    document.getElementById('type').dispatchEvent(new Event('change'));
  });

  // Auto-default when checkbox toggled
  document.addEventListener('change', e=>{
    if(!e.target.matches('input[name="shipping_profiles[]"]')) return;
    const id = e.target.value;
    if(e.target.checked){
      document.getElementById('default_'+id).checked = true;
    } else if(document.getElementById('default_'+id).checked){
      const next = document.querySelector('input[name="shipping_profiles[]"]:checked');
      if(next) document.getElementById('default_'+next.value).checked = true;
    }
  });

  // Alpine.js: Image Upload + Sortable
  function imageUploadSortable(){
    return {
      previews: [], idCounter:0, sortable:null,
      handleFiles(files){ Array.from(files).forEach(f=> this.previewFile(f)); },
      previewFile(file){
        let reader=new FileReader();
        reader.onload=e=>{
          this.previews.push({id:this.idCounter++,url:e.target.result,fileObject:file});
          this.$nextTick(()=> this.initSortable());
        };
        reader.readAsDataURL(file);
      },
      removeFile(i){ this.previews.splice(i,1); },
      handleDrop(e){ if(e.dataTransfer.files.length) this.handleFiles(e.dataTransfer.files); },
      initSortable(){
        if(this.sortable) this.sortable.destroy();
        this.sortable = Sortable.create(document.getElementById('previewList'), {
          animation:150,
          onEnd:evt=>{
            let m=this.previews.splice(evt.oldIndex,1)[0];
            this.previews.splice(evt.newIndex,0,m);
          }
        });
      }
    }
  }
</script>

{{-- Hide TinyMCE code tag bar with CSS fallback --}}
<style>
.tox-statusbar__path {
  display: none !important;
}
</style>

@endsection

