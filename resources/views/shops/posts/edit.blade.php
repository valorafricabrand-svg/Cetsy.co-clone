{{-- resources/views/shops/posts/edit.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm mb-4">
    <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="mb-0 font-bold">Edit Shop Post</h2>
      <a href="{{ route('seller.shop-posts.index') }}" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto">
        <i class="fas fa-arrow-left mr-1"></i> Back to Posts
      </a>
    </div>
    <div class="p-4 sm:p-5">
      <form action="{{ route('seller.shop-posts.update', $shopPost) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-12 md:col-span-7">
            <div class="mb-3">
              <label for="title" class="mb-1 block text-sm font-medium text-slate-700">Title <span class="text-rose-600">*</span></label>
              <input type="text" name="title" id="title" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="{{ old('title', $shopPost->title) }}" required>
            </div>
            <div class="mb-3">
              <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description <span class="text-rose-600">*</span></label>
              <textarea name="description" id="description" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="6" required>{{ old('description', $shopPost->description) }}</textarea>
            </div>
          </div>
          <div class="col-span-12 md:col-span-5">
            <div class="mb-3">
              <label for="image" class="mb-1 block text-sm font-medium text-slate-700">Image</label>
              <input type="file" name="image" id="image" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" accept="image/*">
              @if($shopPost->image)
                <div class="mt-2">
                  <img src="{{ asset('storage/' . $shopPost->image) }}" alt="Current Image" style="width: 100px; height: 70px; object-fit: cover;" class="rounded shadow-sm border">
                </div>
              @endif
            </div>
            <div class="mb-3">
              <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status <span class="text-rose-600">*</span></label>
              <select name="status" id="status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required>
                <option value="draft" {{ old('status', $shopPost->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $shopPost->status) == 'published' ? 'selected' : '' }}>Published</option>
              </select>
            </div>
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 md:col-span-6 mb-3">
                <label for="published_at" class="mb-1 block text-sm font-medium text-slate-700">Published At</label>
                <input type="date" name="published_at" id="published_at" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="{{ old('published_at', optional($shopPost->published_at)->format('Y-m-d')) }}">
              </div>
              <div class="col-span-12 md:col-span-6 mb-3">
                <label for="expired_at" class="mb-1 block text-sm font-medium text-slate-700">Expired At</label>
                <input type="date" name="expired_at" id="expired_at" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="{{ old('expired_at', optional($shopPost->expired_at)->format('Y-m-d')) }}">
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
          <a href="{{ route('seller.shop-posts.index') }}" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 sm:w-auto">
            <i class="fas fa-times mr-1"></i> Cancel
          </a>
          <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-4 sm:w-auto">
            <i class="fas fa-save mr-1"></i> Update Post
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
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

