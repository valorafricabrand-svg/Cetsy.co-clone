@extends('layouts.app')

@section('title', 'Activity #'.$activity->id)
@php
  $props = (array) (is_array($activity->properties) ? $activity->properties : (json_decode(json_encode($activity->properties), true) ?: []));
  $normalizeUrl = static function ($path) {
      $path = (string) $path;
      if ($path === '') {
          return null;
      }
      if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) {
          return $path;
      }
      return \Illuminate\Support\Facades\Storage::url(ltrim($path, '/'));
  };
  $isVideo = static function ($path) {
      $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
      return in_array($ext, ['mp4', 'mov', 'avi', 'wmv', 'webm'], true);
  };
  $mediaItems = [];
  if (!empty($props['files']) && is_array($props['files'])) {
      foreach ($props['files'] as $value) {
          $value = (string) $value;
          if ($value === '') {
              continue;
          }
          $url = $normalizeUrl($value);
          if ($url) {
              $mediaItems[] = ['path' => $value, 'url' => $url];
          }
      }
  }
  $mediaPaths = array_column($mediaItems, 'path');
  $singleFile = null;
  if (!empty($props['file'])) {
      $candidate = (string) $props['file'];
      if ($candidate !== '' && !in_array($candidate, $mediaPaths, true)) {
          $singleUrl = $normalizeUrl($candidate);
          if ($singleUrl) {
              $singleFile = ['path' => $candidate, 'url' => $singleUrl];
          }
      }
  }
  $featuredChange = data_get($props, 'changes.featured_image');
  $featuredChange = is_array($featuredChange) ? $featuredChange : [];
  $featuredFromUrl = isset($featuredChange['from']) ? $normalizeUrl($featuredChange['from']) : null;
  $featuredToUrl = isset($featuredChange['to']) ? $normalizeUrl($featuredChange['to']) : null;
  $hasFeaturedPreview = (bool) $featuredFromUrl || (bool) $featuredToUrl;
  $__hasDetails = false;
@endphp

@section('content')
<div class="content">
  <div class="row g-3">
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h5 fw-semibold mb-0">Product Activity #{{ $activity->id }}</h2>
        <a class="btn btn-outline-secondary" href="{{ route('admin.product-activities.index') }}">Back</a>
      </div>

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="small text-muted">When</div>
              <div>{{ $activity->created_at->toDayDateTimeString() }}</div>
            </div>
            <div class="col-md-6">
              <div class="small text-muted">User</div>
              <div>{{ optional($activity->user)->name ?? ('User #'.$activity->user_id) }}</div>
            </div>
            <div class="col-md-6">
              <div class="small text-muted">Product</div>
              <div>
                @if($product)
                  <a href="{{ route('admin.products.show', $product->id) }}">#{{ $product->id }} - {{ $product->name }}</a>
                @else
                  #{{ $activity->related_id }} <small class="text-muted">{{ $activity->related_type }}</small>
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <div class="small text-muted">Section</div>
              <div>{{ data_get($activity->properties, 'section', '-') }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-semibold">Details</div>
        <div class="card-body">

          <div class="row g-3 mb-2">
            @if(isset($props['action']))
              <div class="col-md-3">
                <div class="small text-muted">Action</div>
                <div class="fw-semibold text-capitalize">{{ $props['action'] }}</div>
              </div>
            @endif
            @if(isset($props['profile_name']))
              <div class="col-md-3">
                <div class="small text-muted">Profile</div>
                <div class="fw-semibold">{{ $props['profile_name'] }}</div>
              </div>
            @endif
            @if(isset($props['set_default']))
              <div class="col-md-3">
                <div class="small text-muted">Set Default</div>
                <div class="fw-semibold">{{ $props['set_default'] ? 'Yes' : 'No' }}</div>
              </div>
            @endif
            @if(isset($props['quality']))
              <div class="col-md-3">
                <div class="small text-muted">Quality</div>
                <div class="fw-semibold">{{ $props['quality'] }}</div>
              </div>
            @endif
          </div>

          @if(!empty($mediaItems))
            @php($__hasDetails = true)
            <h6 class="fw-semibold mt-2">Files</h6>
            <div class="d-flex flex-wrap gap-3 mb-3">
              @foreach($mediaItems as $item)
                <div class="d-flex align-items-center">
                  @if($isVideo($item['path']))
                    <video src="{{ $item['url'] }}" style="width: 120px; height: 80px; object-fit: cover;" controls muted></video>
                  @else
                    <a href="{{ $item['url'] }}" target="_blank" class="text-decoration-none">
                      <img src="{{ $item['url'] }}" alt="Media" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                    </a>
                  @endif
                </div>
              @endforeach
            </div>
          @endif

          @if($singleFile)
            @php($__hasDetails = true)
            <h6 class="fw-semibold mt-2">File</h6>
            <div class="d-flex flex-wrap gap-3 mb-3">
              <div class="d-flex align-items-center">
                @if($isVideo($singleFile['path']))
                  <video src="{{ $singleFile['url'] }}" style="width: 120px; height: 80px; object-fit: cover;" controls muted></video>
                @else
                  <a href="{{ $singleFile['url'] }}" target="_blank" class="text-decoration-none">
                    <img src="{{ $singleFile['url'] }}" alt="Media" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                  </a>
                @endif
              </div>
            </div>
          @endif

          @if(!empty($props['changes']) && is_array($props['changes']))
            @php($__hasDetails = true)
            <h6 class="fw-semibold mt-2">Field Changes</h6>
            <div class="table-responsive mb-3">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 220px;">Field</th>
                    <th>From</th>
                    <th>To</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($props['changes'] as $field => $chg)
                    @php($fromValue = $chg['from'] ?? '')
                    @php($toValue = $chg['to'] ?? '')
                    <tr>
                      <td class="text-nowrap"><code>{{ $field }}</code></td>
                      <td>{{ is_array($fromValue) ? json_encode($fromValue) : (string) $fromValue }}</td>
                      <td>{{ is_array($toValue) ? json_encode($toValue) : (string) $toValue }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          @if(!empty($props['counters']) && is_array($props['counters']))
            @php($__hasDetails = true)
            <h6 class="fw-semibold mt-2">Counters</h6>
            <div class="table-responsive mb-3">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 220px;">Metric</th>
                    <th>From</th>
                    <th>To</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($props['counters'] as $metric => $counts)
                    <tr>
                      <td class="text-nowrap"><code>{{ $metric }}</code></td>
                      <td>{{ $counts['from'] ?? '' }}</td>
                      <td>{{ $counts['to'] ?? '' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          @if($hasFeaturedPreview)
            @php($__hasDetails = true)
            <h6 class="fw-semibold mt-2">Primary Image</h6>
            <div class="d-flex flex-wrap gap-4 mb-3">
              @if($featuredFromUrl)
                <div>
                  <div class="small text-muted mb-1">From</div>
                  <a href="{{ $featuredFromUrl }}" target="_blank" class="text-decoration-none">
                    <img src="{{ $featuredFromUrl }}" alt="From" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                  </a>
                </div>
              @endif
              @if($featuredToUrl)
                <div>
                  <div class="small text-muted mb-1">To</div>
                  <a href="{{ $featuredToUrl }}" target="_blank" class="text-decoration-none">
                    <img src="{{ $featuredToUrl }}" alt="To" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                  </a>
                </div>
              @endif
            </div>
          @endif

          @if(! $__hasDetails)
            <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
