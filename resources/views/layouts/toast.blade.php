{{-- ========= Toast Notifications (auto-fade after 4 s) ========= --}}
<style>
  #toast-container{
      position:fixed;top:1rem;right:1rem;z-index:1050;
      display:flex;flex-direction:column;gap:1rem
  }
  /* Entry animation */
  .toast{border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.1);
         overflow:hidden;animation:fadeInUp .5s ease-out}
  .toast-header{background:#f8f9fa;border-bottom:1px solid #e9ecef}
  .toast-body  {font-size:.9rem}
  .toast .badge{margin-right:.5rem;font-size:.75rem}
  @keyframes fadeInUp{from{transform:translateY(20px);opacity:0}
                      to{transform:translateY(0);opacity:1}}
</style>

<div id="toast-container">

  {{-- Success --}}
  @if(session('success'))
  <div class="toast fade" role="alert" aria-live="assertive" aria-atomic="true"
       data-bs-delay="4000" data-bs-autohide="true">
    <div class="toast-header">
      <span class="me-2 text-success">
        <i class="fa-solid fa-circle-check"></i>
      </span>
      <strong class="me-auto">System</strong>
      <small>Just now</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">
      <span class="badge bg-success">Success</span>
      {!! session('success') !!}
    </div>
  </div>
  @endif

  {{-- Warning --}}
  @if(session('warning'))
  <div class="toast fade" role="alert" aria-live="assertive" aria-atomic="true"
       data-bs-delay="4000" data-bs-autohide="true">
    <div class="toast-header">
      <span class="me-2 text-warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </span>
      <strong class="me-auto">System</strong>
      <small>Just now</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">
      <span class="badge bg-warning">Warning</span>
      {!! session('warning') !!}
    </div>
  </div>
  @endif

  {{-- Danger / Error --}}
  @if(session('danger'))
  <div class="toast fade" role="alert" aria-live="assertive" aria-atomic="true"
       data-bs-delay="4000" data-bs-autohide="true">
    <div class="toast-header">
      <span class="me-2 text-danger">
        <i class="fa-solid fa-circle-xmark"></i>
      </span>
      <strong class="me-auto">System</strong>
      <small>Just now</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">
      <span class="badge bg-danger">Error</span>
      {!! session('danger') !!}
    </div>
  </div>
  @endif
</div>

@push('scripts')
<script>
  /* Bootstrap 5: init every toast in the container */
  document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('#toast-container .toast').forEach(el=>{
      new bootstrap.Toast(el).show();          // delay & autohide taken from data attributes
    });
  });
</script>
@endpush
