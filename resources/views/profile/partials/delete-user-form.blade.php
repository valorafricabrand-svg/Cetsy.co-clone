{{-- =======================================================================
|  DELETE ACCOUNT SECTION – Bootstrap 5
|  Replaces Tailwind section with a card + Bootstrap modal
============================================================================== --}}

{{-- Card with Delete button --}}
<div class="card border-danger mb-5">
  <div class="card-header bg-white border-0">
    <h5 class="mb-0 text-danger">Delete Account</h5>
  </div>
  <div class="card-body">
    <p class="text-muted mb-4">
      Once your account is deleted, all of its resources and data will be permanently removed.
      Before deleting, please download any information you wish to keep.
    </p>
    <button type="button"
            class="btn btn-danger"
            data-bs-toggle="modal"
            data-bs-target="#confirmUserDeletionModal">
      <i class="fas fa-trash-alt me-1"></i>
      Delete Account
    </button>
  </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1"
     aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      {{-- Modal header --}}
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmUserDeletionLabel">
          Confirm Account Deletion
        </h5>
        <button type="button" class="btn-close btn-close-white"
                data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Modal form --}}
      <form method="POST" action="{{ route('profile.destroy') }}">
        @csrf
        @method('DELETE')

        <div class="modal-body">
          <p class="mb-3 text-muted">
            Are you sure you want to delete your account? This action <strong>cannot</strong>
            be undone. Please enter your password to confirm.
          </p>

          <div class="mb-3">
            <label for="delete_password" class="form-label">Password</label>
            <input type="password"
                   id="delete_password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Modal footer --}}
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            Delete Account
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
