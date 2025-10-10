@extends('layouts.app')

@section('title', 'Create Blog Post')

@section('content')
<div class="content">
  <div class="container-xxl">
    <div class="mb-4">
        <h1 class="h3 mb-1">Create Blog Post</h1>
        <p class="text-muted mb-0">Share updates, announcements, and educational content with your community.</p>
    </div>

    <form method="POST" action="{{ route('admin.blog-posts.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.blog.posts._form', ['submitLabel' => 'Create Post'])
    </form>
  </div>
</div>
@endsection



@push('scripts')
    <script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
    <script>
    (function(){
      function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
      onReady(function(){
        const start = function(){
          try { const inst = tinymce.get('description'); if (inst) inst.remove(); } catch(_) {}
          tinymce.init({
      selector: '#description',
      height: 400,
      min_height: 400,
      menubar: true,
      plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount',
        'formatpainter',
        'lineheight',
        'textcolor'
      ],
       toolbar: [
         'undo redo | fontselect fontsizeselect |',
         'bold italic underline strikethrough forecolor backcolor |',
         'alignleft aligncenter alignright alignjustify |',
         'bullist numlist outdent indent | removeformat | link image media | code'
       ].join(' '),
      font_formats: [
        'Arial=arial,helvetica,sans-serif;',
        'Courier New=courier new,courier,monospace;',
        'Georgia=georgia,palatino,serif;',
        'Tahoma=tahoma,arial,helvetica,sans-serif;',
        'Times New Roman=times new roman,times,serif;',
        'Verdana=verdana,geneva,sans-serif'
      ].join(' '),
      fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
       browser_contextmenu: true,
       contextmenu: 'link image inserttable | cell row column',
       branding: false,
       content_style: 'body { min-height:400px !important; }',
       base_url: '{{ asset('assets/js/tinymce') }}',
       setup(editor) {
         editor.on('change', () => editor.save());
       }
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
    </script>
@endpush
