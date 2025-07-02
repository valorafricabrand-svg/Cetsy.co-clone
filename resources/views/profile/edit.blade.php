{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="container-xxl">

    {{-- ================== GRID ================== --}}
    <div class="row g-4">

      {{-- 1️⃣  Update profile information --}}
      <div class="col-12">
        <div class="card shadow-sm border-light-subtle rounded-3">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-semibold">Update&nbsp;Profile&nbsp;Information</h5>
          </div>
          <div class="card-body">
            @include('profile.partials.update-profile-information-form')
          </div>
        </div>
      </div>

      {{-- 2️⃣  Change password --}}
      <div class="col-12">
        <div class="card shadow-sm border-light-subtle rounded-3">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-semibold">Change&nbsp;Password</h5>
          </div>
          <div class="card-body">
            @include('profile.partials.update-password-form')
          </div>
        </div>
      </div>

      {{-- 3️⃣  Delete account --}}
      <div class="col-12">
        <div class="card shadow-sm border-danger-subtle border-2 rounded-3">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0 text-danger fw-semibold">Delete&nbsp;Account</h5>
          </div>
          <div class="card-body">
            @include('profile.partials.delete-user-form')
          </div>
        </div>
      </div>

    </div> {{-- /row --}}
  </div>   {{-- /container --}}
</div>
@endsection
