@extends('layouts.app')

@section('content')
<div class="content">
    <h2 class="h3 mb-4">Add Service</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))   
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any()) 
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>   
        </div>
    @endif
    <form action="{{ route('seller.services.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        

        <div class="card mb-4">
            <div class="card-header">Listing details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Service Name <span class="text-danger">Required</span></label>
                    <input type="text" class="form-control" id="name" name="name"  placeholder="Service name" value="{{ old('name') }}">
                    
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">Required</span></label>
                    <input type="text" class="form-control" id="phone" name="phone" required placeholder="+254 xxx xxx xxx" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price <span class="text-danger">Required</span></label>
                    <input type="text" class="form-control" id="price" name="price" required placeholder="Price" value="{{ old('price') }}">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Service Provider Email Address <span class="text-danger">Required</span></label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Service Provider Email Address" value="{{ old('email') }}">
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Service Provider Geographical Location <span class="text-danger">Required</span></label>
                    <input type="text" class="form-control" id="location" name="location" required placeholder="Service Provider Geographical Location" value="{{ old('location') }}">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Service Description <span class="text-danger">Required</span></label>
                    <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                    <small class="form-text text-muted">Make sure the service description provides a detailed explanation of your service so that it is easy to understand and find your service. It is recommended not to enter info on mobile numbers, e-mails, etc. into the service description to protect your personal data.</small>
                </div>
                <div class="mb-3">
                    <label for="tags" class="form-label">Tags <span class="text-muted">Optional</span></label>
                    <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g. Cleaning, Medical, Dog-Walking" value="{{ old('tags') }}">
                    <small class="form-text text-muted">Add up to 13 tags to help people find your service.</small>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category <span class="text-danger">Required</span></label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">--choose--</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                        <label for="origin_id" class="form-label">Country <span class="text-danger">Required</span></label>
                        <select class="form-select" id="origin_id" name="origin_id" required>
                        <option value="">--choose--</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1 d-flex align-items-center gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="renewal_option" id="renewal_automatic" value="0" {{ old('renewal_option', '0') == '0' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="renewal_automatic">Automatic</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="renewal_option" id="renewal_manual" value="1" {{ old('renewal_option') == '1' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="renewal_manual">Manual</label>
                    </div>
                    @error('renewal_option')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="listing_fee_renewal" class="form-label">Listing Fee Renewal <span class="text-danger">Required</span></label>
                    <select name="listTypeFee_id" id="listTypeFee_id" class="form-select" required>
                        <option value="">--choose--</option>
                        @foreach ($category_listFee_types as $listType)
                            <option value="{{ $listType->id }}" {{ old('listTypeFee_id') == $listType->id ? 'selected' : '' }}>{{ $listType->name }}</option>
                        @endforeach
                    </select>
                    @error('listTypeFee_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Photos</div>
            <div class="card-body">
                <p class="text-muted">Avoid offering services that are violating Intellectual Property Rights, so that your services are not blacklisted.</p>
                <div class="mb-3">
                    <label for="photos" class="form-label">Service Photos <span class="text-danger">Required</span></label>
                    <input class="form-control" type="file" id="photos" name="photos[]" multiple required>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection 