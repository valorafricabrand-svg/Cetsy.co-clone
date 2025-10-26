{{-- resources/views/shops/posts/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h2 class="mb-0 fw-bold">Edit Shop Post</h2>
      <a href="{{ route('seller.shop-posts.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Posts
      </a>
    </div>
    <div class="card-body">
      <form action="{{ route('seller.shop-posts.update', $shopPost) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="row">
          <div class="col-md-7">
            <div class="mb-3">
              <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $shopPost->title) }}" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea name="description" id="description" class="form-control" rows="6" required>{{ old('description', $shopPost->description) }}</textarea>
            </div>
          </div>
          <div class="col-md-5">
            <div class="mb-3">
              <label for="image" class="form-label">Image</label>
              <input type="file" name="image" id="image" class="form-control" accept="image/*">
              @if($shopPost->image)
                <div class="mt-2">
                  <img src="{{ asset('storage/' . $shopPost->image) }}" alt="Current Image" style="width: 100px; height: 70px; object-fit: cover;" class="rounded shadow-sm border">
                </div>
              @endif
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" id="status" class="form-select" required>
                <option value="draft" {{ old('status', $shopPost->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $shopPost->status) == 'published' ? 'selected' : '' }}>Published</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="published_at" class="form-label">Published At</label>
                <input type="date" name="published_at" id="published_at" class="form-control" value="{{ old('published_at', optional($shopPost->published_at)->format('Y-m-d')) }}">
              </div>
              <div class="col-md-6 mb-3">
                <label for="expired_at" class="form-label">Expired At</label>
                <input type="date" name="expired_at" id="expired_at" class="form-control" value="{{ old('expired_at', optional($shopPost->expired_at)->format('Y-m-d')) }}">
              </div>
            </div>
          </div>
        </div>
        <div class="text-end mt-4">
          <a href="{{ route('seller.shop-posts.index') }}" class="btn btn-outline-secondary px-4 me-2">
            <i class="fas fa-times me-1"></i> Cancel
          </a>
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-save me-1"></i> Update Post
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
