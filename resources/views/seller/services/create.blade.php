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
                    <label for="price_type" class="form-label">Price Type <span class="text-danger">Required</span></label>
                    <select class="form-select" id="price_type" name="price_type" required>
                        <option value="">Select price type</option>
                        <option value="fixed" {{ old('price_type') == 'fixed' ? 'selected' : '' }}>Fixed Price</option>
                        <option value="hourly" {{ old('price_type') == 'hourly' ? 'selected' : '' }}>Hourly Rate</option>
                        <option value="negotiable" {{ old('price_type') == 'negotiable' ? 'selected' : '' }}>Negotiable</option>
                        <option value="per_item" {{ old('price_type') == 'per_item' ? 'selected' : '' }}>Per Item</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Service Provider Email Address <span class="text-danger">Required</span></label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Service Provider Email Address" value="{{ old('email') }}">
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
                <div class="mb-3">
                    <label for="description" class="form-label">Service Description <span class="text-danger">Required</span></label>
                    <textarea class="form-control" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                    <small class="form-text text-muted">Make sure the service description provides a detailed explanation of your service so that it is easy to understand and find your service. It is recommended not to enter info on mobile numbers, e-mails, etc. into the service description to protect your personal data.</small>
                </div>
                
                
                
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Location & Availability</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="service_area" class="form-label">Service Area <span class="text-danger">Required</span></label>
                    <input type="text" class="form-control" id="location" name="location" required placeholder="e.g., Nairobi, Remote, Global" value="{{ old('location') }}">
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1" {{ old('is_remote') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_remote">
                            This service can be provided remotely
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Available Days <span class="text-danger">Required</span></label>
                    <div class="row g-3">
                        @php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $oldDays = old('available_days', []);
                        @endphp
                        @foreach($days as $day)
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="day_{{ strtolower($day) }}" 
                                           name="available_days[]" 
                                           value="{{ $day }}"
                                           {{ in_array($day, $oldDays) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                        {{ $day }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="available_time_from" class="form-label">Available Hours - Start <span class="text-danger">Required</span></label>
                            <input type="time" class="form-control" id="available_time_from" name="available_time_from" required value="{{ old('available_time_from') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="available_time_to" class="form-label">Available Hours - End <span class="text-danger">Required</span></label>
                            <input type="time" class="form-control" id="available_time_to" name="available_time_to" required value="{{ old('available_time_to') }}">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="service_duration" class="form-label">Service Duration <span class="text-danger">Required</span></label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" class="form-control" id="duration_value" name="duration_value" required placeholder="Duration" value="{{ old('duration_value') }}">
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" id="duration_unit" name="duration_unit" required>
                                <option value="">Select Unit</option>
                                <option value="minutes" {{ old('duration_unit') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                <option value="hours" {{ old('duration_unit') == 'hours' ? 'selected' : '' }}>Hours</option>
                                <option value="days" {{ old('duration_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                <option value="weeks" {{ old('duration_unit') == 'weeks' ? 'selected' : '' }}>Weeks</option>
                            </select>
                        </div>
                    </div>
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

@push('scripts')
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
(function(){
  function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  onReady(function(){
    const el = document.getElementById('description');
    if(!el) return;
    const start = function(){
      try{ const i=tinymce.get('description'); if(i) i.remove(); }catch(_){}
      tinymce.init({
        selector:'#description',
        height:400,
        menubar:true,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount quickbars emoticons autoresize',
        toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media | code',
        branding:false,
        browser_spellcheck:true,
        gecko_spellcheck:true,
        elementpath:false,
        base_url: '{{ asset('assets/js/tinymce') }}',
        setup(editor){ editor.on('change', () => editor.save()); }
      });
    };
    if(window.tinymce){ start(); }
    else {
      const s=document.createElement('script');
      s.src='https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
      s.referrerPolicy='origin';
      s.onload=start;
      s.onerror=function(){ console.warn('TinyMCE CDN failed to load'); };
      document.head.appendChild(s);
    }
  });
})();
</script>
@endpush
