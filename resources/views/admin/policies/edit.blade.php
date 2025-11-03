{{-- resources/views/admin/policies/edit.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="h4 mb-0">Edit: {{ $label }}</h2>
@endsection

@section('content')
<div class="content">
  <div class="mb-3">
    <a href="{{ route('admin.policies.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.policies.update', $slug) }}" method="POST">
    @csrf @method('PUT')
    <div class="card shadow-sm">
      <div class="card-body">
        <label class="form-label">Content</label>
        <textarea id="editor" name="content" rows="18" class="form-control">{!! old('content', $content) !!}</textarea>
        <div class="form-text">Leave blank to fall back to the default static content.</div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.policies.index') }}">Cancel</a>
        <button class="btn btn-primary">Save</button>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
  (function(){
    function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
    onReady(function(){
      const start = function(){ try{ const i=tinymce.get('editor'); if(i) i.remove(); }catch(_){}
        tinymce.init({
          selector:'#editor', height:500, menubar:true,
          plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount quickbars autoresize',
          toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code',
          branding:false, browser_spellcheck:true, gecko_spellcheck:true, elementpath:false,
          base_url: '{{ asset('assets/js/tinymce') }}',
          setup(ed){ ed.on('change', ()=> ed.save()); }
        });
      };
      if (window.tinymce) start();
      else {
        const s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js'; s.referrerPolicy='origin'; s.onload=start; document.head.appendChild(s);
      }
    });
  })();
</script>
@endpush

