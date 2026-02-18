{{-- =======================================================================
|  DELETE ACCOUNT SECTION â€“ Bootstrap 5
|  Replaces Tailwind section with a card + Bootstrap modal
============================================================================== --}}

{{-- Card with Delete button --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-danger mb-5">
  <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
    <h5 class="mb-0 text-rose-600">Delete Account</h5>
  </div>
  <div class="p-4 sm:p-5">
    <p class="text-slate-500 mb-4">
      Once your account is deleted, all of its resources and data will be permanently removed.
      Before deleting, please download any information you wish to keep.
    </p>
    <button type="button"
            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-rose-600 text-white hover:bg-rose-500"
            data-bs-toggle="modal"
            data-bs-target="#confirmUserDeletionModal">
      <i class="fas fa-trash-alt mr-1"></i>
      Delete Account
    </button>
  </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal" id="confirmUserDeletionModal" tabindex="-1"
     aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">

      {{-- Modal header --}}
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 bg-danger text-white">
        <h5 class="text-base font-semibold text-slate-900" id="confirmUserDeletionLabel">
          Confirm Account Deletion
        </h5>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 text-white hover:bg-white/20 hover:text-white"
                data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Modal form --}}
      <form method="POST" action="{{ route('profile.destroy') }}">
        @csrf
        @method('DELETE')

        <div class="px-4 py-4">
          <p class="mb-3 text-slate-500">
            Are you sure you want to delete your account? This action <strong>cannot</strong>
            be undone. Please enter your password to confirm.
          </p>

          <div class="mb-3">
            <label for="delete_password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
            <input type="password"
                   id="delete_password"
                   name="password"
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   required>
            @error('password')
              <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Modal footer --}}
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button"
                  class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500"
                  data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-rose-600 text-white hover:bg-rose-500">
            Delete Account
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

