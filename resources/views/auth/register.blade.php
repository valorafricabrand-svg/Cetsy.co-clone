@extends('theme.layouts.main')

@section('main')
<div class="container py-16">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 col-sm-10">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Create your {{ config('app.name') }} account</h2>

      <form method="POST" action="{{ route('register') }}" class="space-y-6 bg-white p-4 shadow rounded-lg">
        @csrf

        <!-- Account Type -->
        <div>
          <label class="form-label">Account Type</label>
          <div class="d-flex gap-4">
            <div class="form-check">
              <input type="radio" name="role" value="buyer" checked
                     class="form-check-input @error('role') is-invalid @enderror" id="role-buyer">
              <label class="form-check-label" for="role-buyer">Buyer</label>
            </div>
            <div class="form-check">
              <input type="radio" name="role" value="seller"
                     class="form-check-input @error('role') is-invalid @enderror" id="role-seller">
              <label class="form-check-label" for="role-seller">Seller</label>
            </div>
          </div>
          @error('role')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Name -->
        <div>
          <label for="name" class="form-label">Name</label>
          <input id="name" name="name" type="text" required autofocus
                 value="{{ old('name') }}"
                 class="form-control @error('name') is-invalid @enderror">
          @error('name')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Email Address -->
        <div>
          <label for="email" class="form-label">Email</label>
          <input id="email" name="email" type="email" required
                 value="{{ old('email') }}"
                 class="form-control @error('email') is-invalid @enderror">
          @error('email')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>
        <div>
          <label for="phone" class="form-label">Phone</label>
          <input id="phone" name="phone" type="text" required
                 value="{{ old('phone') }}"
                 class="form-control @error('phone') is-invalid @enderror">
        </div>
        <div>
          <label for="country_id" class="form-label">Country</label>
          <select name="country_id" id="country_id" class="form-control @error('country_id') is-invalid @enderror">
            <option value="">Select Country</option>
            @foreach($countries as $country)
              <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
          </select>
          @error('country_id')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="form-label">Password</label>
          <input id="password" name="password" type="password" required
                 autocomplete="new-password"
                 class="form-control @error('password') is-invalid @enderror">
          @error('password')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" required
                 autocomplete="new-password"
                 class="form-control @error('password_confirmation') is-invalid @enderror">
          @error('password_confirmation')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <div class="d-flex justify-content-between">
          <a href="{{ route('login') }}" class="text-sm text-success">
            Already have an account?
          </a>
          <button type="submit" class="btn btn-success">
            Register
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
