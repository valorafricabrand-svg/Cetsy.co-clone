@php
    $meta = $post->meta ?? [];
@endphp

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="auto-generated if blank">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Excerpt</label>
                    <textarea name="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Short summary shown on listings">{{ old('excerpt', $post->excerpt) }}</textarea>
                    @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
                    <textarea name="body" rows="12" class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $post->body) }}</textarea>
                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">SEO Meta</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $meta['title'] ?? '') }}" class="form-control @error('meta_title') is-invalid @enderror" placeholder="Overrides default page title">
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" rows="3" class="form-control @error('meta_description') is-invalid @enderror" placeholder="Short description for search engines">{{ old('meta_description', $meta['description'] ?? '') }}</textarea>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-0">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $meta['keywords'] ?? '') }}" class="form-control @error('meta_keywords') is-invalid @enderror" placeholder="Comma separated keywords">
                    @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="blog_category_id" class="form-select @error('blog_category_id') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('blog_category_id', $post->blog_category_id) === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ old('status', $post->status ?? \App\Models\BlogPost::STATUS_DRAFT) === $statusOption ? 'selected' : '' }}>
                                {{ ucfirst($statusOption) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted d-block mt-1">Scheduled posts require a future publish date.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Publish At</label>
                    @php
                        $publishValue = old('published_at', optional($post->published_at)->format('Y-m-d\TH:i'));
                    @endphp
                    <input type="datetime-local" name="published_at" value="{{ $publishValue }}" class="form-control @error('published_at') is-invalid @enderror">
                    @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Featured Image URL</label>
                    <input type="text" name="featured_image" value="{{ old('featured_image', $post->featured_image) }}" class="form-control @error('featured_image') is-invalid @enderror" placeholder="https://example.com/image.jpg">
                    @error('featured_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save Post' }}</button>
                    <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
