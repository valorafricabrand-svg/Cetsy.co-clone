@extends('layouts.app')

@section('title', 'KYC Management')

@section('content')
<div class="content">
  <div class="card shadow-sm">
    <div class="card-body">

      {{-- Header --}}
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h2 class="h5 mb-0">KYC Verifications</h2>

        {{-- Filters --}}
        <form method="get" class="d-flex flex-wrap gap-2">
          <input type="text" name="q" value="{{ $search ?? request('q') }}" placeholder="Search name/email/ID…" class="form-control form-control-sm" style="width:210px">
          <select name="status" class="form-select form-select-sm" style="width:170px" onchange="this.form.submit()">
            <option value="">All ({{ isset($counts) ? array_sum($counts->toArray()) : ($total ?? '') }})</option>
            @foreach(['pending','approved','rejected','needs_correction'] as $s)
              <option value="{{ $s }}" @selected(($status ?? request('status')) === $s)>
                {{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})
              </option>
            @endforeach
          </select>
          <select name="per_page" class="form-select form-select-sm" style="width:100px" onchange="this.form.submit()">
            @foreach([10,25,50,100] as $n)
              <option value="{{ $n }}" @selected(($perPage ?? request('per_page',10)) == $n)>{{ $n }}</option>
            @endforeach
          </select>
          <button class="btn btn-sm btn-primary">Filter</button>
        </form>
      </div>

      {{-- Status Pills --}}
      <ul class="nav nav-pills mb-3 gap-2 flex-wrap">
        @php $current = $status ?? request('status'); @endphp
        <li class="nav-item">
          <a class="nav-link {{ $current===''||$current===null ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status'=>null,'page'=>1]) }}">
            All <span class="badge bg-secondary">{{ isset($counts) ? array_sum($counts->toArray()) : ($total ?? '') }}</span>
          </a>
        </li>
        @foreach(['pending','approved','rejected','needs_correction'] as $s)
          <li class="nav-item">
            <a class="nav-link {{ $current===$s ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status'=>$s,'page'=>1]) }}">
              {{ ucfirst($s) }} <span class="badge bg-secondary">{{ $counts[$s] ?? 0 }}</span>
            </a>
          </li>
        @endforeach
      </ul>

      {{-- Flash --}}
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      {{-- Table & Bulk Form --}}
      <form id="bulkForm" method="POST" action="{{ route('admin.kyc.bulk') }}">
        @csrf
        @method('PATCH')

        <div class="table-responsive mb-3">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:32px">
                  <input type="checkbox" id="checkAll">
                </th>
                @php
                  $dirToggler = fn($field) => request('dir','desc')==='asc' ? 'desc' : 'asc';
                @endphp
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'first_name','dir'=>$dirToggler('first_name')]) }}" class="text-decoration-none">Seller</a>
                </th>
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'id_type','dir'=>$dirToggler('id_type')]) }}" class="text-decoration-none">ID Type</a>
                </th>
                <th>
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'id_number','dir'=>$dirToggler('id_number')]) }}" class="text-decoration-none">ID Number</a>
                </th>
                <th class="d-none d-md-table-cell">
                  <a href="{{ request()->fullUrlWithQuery(['sort'=>'created_at','dir'=>$dirToggler('created_at')]) }}" class="text-decoration-none">Submitted</a>
                </th>
                <th class="d-none d-md-table-cell">Documents</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse(($kycs ?? $pendingKycs) as $kyc)
                <tr>
                  <td>
                    <input type="checkbox" name="ids[]" value="{{ $kyc->id }}" class="row-check">
                  </td>
                  <td>
                    <a href="{{ route('admin.kyc.showDetails', $kyc) }}" class="text-decoration-none">
                      <strong>{{ $kyc->first_name }} {{ $kyc->last_name }}</strong><br>
                      <small class="text-muted">{{ $kyc->email }} &bull; {{ $kyc->phone }}</small>
                    </a>
                  </td>
                  <td>{{ ucfirst($kyc->id_type) }}</td>
                  <td>{{ $kyc->id_number }}</td>
                  <td class="d-none d-md-table-cell">{{ $kyc->created_at->format('M d, Y H:i') }}</td>
                  <td class="d-none d-md-table-cell">
                    <a href="{{ Storage::url($kyc->id_front) }}" target="_blank" class="me-2">Front</a>
                    <a href="{{ Storage::url($kyc->id_back) }}" target="_blank" class="me-2">Back</a>
                    <a href="{{ Storage::url($kyc->selfie) }}" target="_blank">Selfie</a>
                  </td>
                  <td class="text-end">
                    @can('update', $kyc)
                      <button
                        type="button"
                        class="btn btn-sm btn-success me-1 single-action"
                        data-bs-toggle="modal"
                        data-bs-target="#kycModal"
                        data-kyc-id="{{ $kyc->id }}"
                        data-action="approved"
                      >
                        <i class="fas fa-check me-1"></i> Approve
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-danger single-action"
                        data-bs-toggle="modal"
                        data-bs-target="#kycModal"
                        data-kyc-id="{{ $kyc->id }}"
                        data-action="rejected"
                      >
                        <i class="fas fa-times me-1"></i> Reject
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-warning text-dark single-action"
                        data-bs-toggle="modal"
                        data-bs-target="#kycModal"
                        data-kyc-id="{{ $kyc->id }}"
                        data-action="needs_correction"
                      >
                        <i class="fas fa-edit me-1"></i> Request Correction
                      </button>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-3">
                    No KYC records found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination & Bulk Buttons --}}
        <div class="d-flex justify-content-between align-items-center">
          <div>
            {{-- SHOW BULK BUTTONS TO ADMINS --}}
            @if(auth()->check() && auth()->user()->isAdmin())
              <div class="btn-group">
                <button type="button" class="btn btn-outline-success btn-sm bulk-btn" data-action="approved" data-bs-toggle="modal" data-bs-target="#kycModal">
                  Bulk Approve
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm bulk-btn" data-action="rejected" data-bs-toggle="modal" data-bs-target="#kycModal">
                  Bulk Reject
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm bulk-btn" data-action="needs_correction" data-bs-toggle="modal" data-bs-target="#kycModal">
                  Bulk Request Correction
                </button>
              </div>
            @endif
          </div>

          <div>
            {{ ($kycs ?? $pendingKycs)->links() }}
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="kycModalForm" method="POST" class="modal-content">
      @csrf
      @method('PATCH')

      <div class="modal-header">
        <h5 class="modal-title" id="kycModalLabel">KYC Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="status" id="kycStatus" value="approved">
        <input type="hidden" name="ids_json" id="ids_json" value="">
        <div class="mb-3 d-none" id="kycNotesGroup">
          <label for="admin_notes" class="form-label">Notes</label>
          <div class="d-flex gap-2 mb-2">
            <select id="notes_template" class="form-select form-select-sm" style="max-width:260px">
              <option value="">Select template...</option>
              <option>Document images are blurry. Please re-upload clearer scans.</option>
              <option>Name on ID does not match your account. Please update details or provide matching ID.</option>
              <option>ID appears expired. Please submit a valid, unexpired ID.</option>
              <option>Missing required document pages. Please include both front and back.</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" type="button" id="apply_template">Apply</button>
          </div>
          <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3" placeholder="Enter details for rejection/corrections..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="kycSubmitBtn" class="btn btn-success">Approve</button>
      </div>
    </form>
  </div>
</div>

{{-- Scripts --}}
@push('scripts')
<script>
(function () {
  const modalEl   = document.getElementById('kycModal');
  const checkAll  = document.getElementById('checkAll');
  const rowChecks = document.querySelectorAll('.row-check');

  // Select all
  if (checkAll) {
    checkAll.addEventListener('change', function () {
      rowChecks.forEach(cb => cb.checked = checkAll.checked);
    });
  }

  modalEl.addEventListener('show.bs.modal', function (event) {
    const button    = event.relatedTarget;
    const action    = button.getAttribute('data-action');
    const singleId  = button.getAttribute('data-kyc-id');

    const form      = document.getElementById('kycModalForm');
    const title     = document.getElementById('kycModalLabel');
    const statusIn  = document.getElementById('kycStatus');
    const notesGrp  = document.getElementById('kycNotesGroup');
    const notesIn   = document.getElementById('admin_notes');
    const submitBtn = document.getElementById('kycSubmitBtn');
    const idsJson   = document.getElementById('ids_json');

    let ids = [];
    if (button.classList.contains('single-action')) {
      form.action = `/admin/kyc/${singleId}`;
      ids = [singleId];
    } else {
      // Bulk
      form.action = "{{ route('admin.kyc.bulk') }}";
      document.querySelectorAll('.row-check:checked').forEach(cb => ids.push(cb.value));
      if (!ids.length) {
        event.preventDefault();
        return alert('Please select at least one record.');
      }
    }
    idsJson.value = JSON.stringify(ids);

    if (action === 'approved') {
      title.textContent     = 'Approve KYC Verification';
      submitBtn.textContent = 'Approve';
      submitBtn.className   = 'btn btn-success';
      statusIn.value        = 'approved';
      notesGrp.classList.add('d-none');
      notesIn.required      = false;
    } else if (action === 'rejected') {
      title.textContent     = 'Reject KYC Verification';
      submitBtn.textContent = 'Reject';
      submitBtn.className   = 'btn btn-danger';
      statusIn.value        = 'rejected';
      notesGrp.classList.remove('d-none');
      notesIn.required      = true;
    } else if (action === 'needs_correction') {
      title.textContent     = 'Request KYC Correction';
      submitBtn.textContent = 'Send Request';
      submitBtn.className   = 'btn btn-warning text-dark';
      statusIn.value        = 'needs_correction';
      notesGrp.classList.remove('d-none');
      notesIn.required      = true;
    }
    notesIn.value = '';
  });
})();
</script>
@endpush
@endsection
