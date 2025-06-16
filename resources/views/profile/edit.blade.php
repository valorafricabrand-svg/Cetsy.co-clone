@extends('layouts.app')

@section('content')
<div class="content">
  <div class="row gy-4">

    <!-- Update Profile Information -->
    <div class="col-12">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Update Profile Information</h5>
        </div>
        <div class="card-body">
          @include('profile.partials.update-profile-information-form')
        </div>
      </div>
    </div>

    <!-- Update Password -->
    <div class="col-12">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Change Password</h5>
        </div>
        <div class="card-body">
          @include('profile.partials.update-password-form')
        </div>
      </div>
    </div>

    <!-- Delete Account -->
    <div class="col-12">
      <div class="card shadow-sm mb-4 border border-2 border-danger">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0 text-danger">Delete Account</h5>
        </div>
        <div class="card-body">
          @include('profile.partials.delete-user-form')
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
