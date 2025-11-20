@csrf

<div class="row mb-3">
  <div class="col-md-8">
    <label class="form-label">Title</label>
    <input type="text" name="title" value="{{ old('title', $slide->title) }}" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Tag (small label)</label>
    <input type="text" name="tag" value="{{ old('tag', $slide->tag) }}" class="form-control" placeholder="e.g., Save, New, Hot">
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Link to Deal (optional)</label>
    <select name="deal_id" class="form-select">
      <option value="">-- None --</option>
      @foreach($deals as $deal)
        <option value="{{ $deal->id }}" @selected((int) old('deal_id', $slide->deal_id) === $deal->id)>{{ $deal->name }}</option>
      @endforeach
    </select>
    <div class="form-text">If set, the primary button links to listings filtered to this deal.</div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Link to Category (optional)</label>
    <select name="category_id" class="form-select">
      <option value="">-- None --</option>
      @foreach($categories as $cat)
        <option value="{{ $cat->id }}" @selected((int) old('category_id', $slide->category_id) === $cat->id)>{{ $cat->name }}</option>
      @endforeach
    </select>
    <div class="form-text">Used when no deal is chosen - button goes to this category page.</div>
  </div>
</div>

<div class="mb-3">
  <label class="form-label">Subtitle</label>
  <textarea name="subtitle" rows="3" class="form-control" placeholder="Short supporting copy">{{ old('subtitle', $slide->subtitle) }}</textarea>
</div>

<div class="row mb-3">
  <div class="col-md-4">
    <label class="form-label">Button label</label>
    <input type="text" name="button_label" value="{{ old('button_label', $slide->button_label) }}" class="form-control" placeholder="Shop deals">
  </div>
  <div class="col-md-8">
    <label class="form-label">Button URL</label>
    <input type="text" name="button_url" value="{{ old('button_url', $slide->button_url) }}" class="form-control" placeholder="{{ route('listings') }}">
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-4">
    <label class="form-label">Sort order</label>
    <input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order ?? 0) }}" class="form-control" min="0" step="1">
    <div class="form-text">Lower numbers appear first.</div>
  </div>
  <div class="col-md-4 d-flex align-items-center">
    <div class="form-check mt-4">
      <input type="hidden" name="is_active" value="0">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $slide->is_active) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
  </div>
</div>

<div class="mb-3">
  <label class="form-label">Hero image</label>
  <input type="file" name="image" class="form-control">
  <div class="form-text">Recommended: wide image (e.g. 1600x600), max 4MB.</div>
  @if($slide->image_path)
    <div class="mt-2">
      <img src="{{ asset('storage/'.$slide->image_path) }}" alt="Current hero image" class="img-fluid rounded" style="max-height:160px;object-fit:cover;">
    </div>
  @endif
</div>

<div class="mt-3">
  <button type="submit" class="btn btn-primary">Save</button>
  <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
