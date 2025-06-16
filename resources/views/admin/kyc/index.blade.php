@extends('layouts.app')

@section('title', 'KYC Management')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Pending KYC Verifications</h2>
      </div>

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="table-responsive mb-3">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Seller</th>
              <th>ID Type</th>
              <th>ID Number</th>
              <th class="d-none d-md-table-cell">Submitted</th>
              <th class="d-none d-md-table-cell">Documents</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendingKycs as $kyc)
              <tr>
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
                  <button
                    class="btn btn-sm btn-success me-1"
                    data-bs-toggle="modal"
                    data-bs-target="#kycModal"
                    data-kyc-id="{{ $kyc->id }}"
                    data-action="approved"
                  >
                    <i class="fas fa-check me-1"></i> Approve
                  </button>
                  <button
                    class="btn btn-sm btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#kycModal"
                    data-kyc-id="{{ $kyc->id }}"
                    data-action="rejected"
                  >
                    <i class="fas fa-times me-1"></i> Reject
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-3">
                  No pending KYC verifications found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div>
        {{ $pendingKycs->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Modal -->
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

        <div class="mb-3 d-none" id="kycNotesGroup">
          <label for="admin_notes" class="form-label">Rejection Reason</label>
          <textarea
            name="admin_notes"
            id="admin_notes"
            class="form-control"
            rows="3"
            placeholder="Enter reason for rejection..."
          ></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="submit" id="kycSubmitBtn" class="btn btn-success">
          Approve
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  var kycModal = document.getElementById('kycModal');
  kycModal.addEventListener('show.bs.modal', function (event) {
    var button    = event.relatedTarget;
    var kycId     = button.getAttribute('data-kyc-id');
    var action    = button.getAttribute('data-action');
    var form      = document.getElementById('kycModalForm');
    var title     = document.getElementById('kycModalLabel');
    var statusIn  = document.getElementById('kycStatus');
    var notesGrp  = document.getElementById('kycNotesGroup');
    var notesIn   = document.getElementById('admin_notes');
    var submitBtn = document.getElementById('kycSubmitBtn');

    // set form action URL
    form.action = `/admin/kyc/${kycId}`;

    if (action === 'approved') {
      title.textContent = 'Approve KYC Verification';
      submitBtn.textContent = 'Approve';
      submitBtn.className  = 'btn btn-success';
      statusIn.value       = 'approved';
      notesGrp.classList.add('d-none');
      notesIn.required     = false;
    } else {
      title.textContent = 'Reject KYC Verification';
      submitBtn.textContent = 'Reject';
      submitBtn.className  = 'btn btn-danger';
      statusIn.value       = 'rejected';
      notesGrp.classList.remove('d-none');
      notesIn.required     = true;
    }
    // clear notes
    notesIn.value = '';
  });
</script>
@endsection
