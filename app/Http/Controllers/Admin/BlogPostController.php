<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');

        $posts = BlogPost::query()
            ->with(['category', 'author'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statuses = BlogPost::statuses();

        return view('admin.blog.posts.index', compact('posts', 'status', 'statuses', 'search'));
    }

    public function create()
    {
        $post = new BlogPost([
            'status' => BlogPost::STATUS_DRAFT,
        ]);
        $categories = BlogCategory::orderBy('name')->get();
        $statuses = BlogPost::statuses();

        return view('admin.blog.posts.create', compact('post', 'categories', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog/posts', 'public');
        } else {
            unset($data['featured_image']);
        }

        $data['user_id'] = Auth::id();
        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['title']);
        $data = $this->preparePublicationData($data);

        BlogPost::create($data);

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function show(BlogPost $blogPost)
    {
        $blogPost->load(['category', 'author']);
        return view('admin.blog.posts.show', compact('blogPost'));
    }

    public function edit(BlogPost $blogPost)
    {
        $categories = BlogCategory::orderBy('name')->get();
        $statuses = BlogPost::statuses();

        return view('admin.blog.posts.edit', [
            'post' => $blogPost,
            'categories' => $categories,
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $data = $this->validatePost($request, $blogPost->id);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog/posts', 'public');

            $existingImage = $blogPost->featured_image;
            if ($existingImage && !Str::startsWith($existingImage, ['http://', 'https://', '//'])) {
                $storedPath = ltrim($existingImage, '/');
                if (Str::startsWith($storedPath, 'storage/')) {
                    $storedPath = substr($storedPath, strlen('storage/'));
                }
                Storage::disk('public')->delete($storedPath);
            }
        } else {
            unset($data['featured_image']);
        }

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['title'], $blogPost->id);
        $data = $this->preparePublicationData($data, $blogPost);

        $blogPost->update($data);

        return redirect()->route('admin.blog-posts.edit', $blogPost)
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Blog post moved to trash.');
    }

    private function validatePost(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(BlogPost::statuses())],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function preparePublicationData(array $data, ?BlogPost $existing = null): array
    {
        $meta = array_filter([
            'title' => Arr::get($data, 'meta_title'),
            'description' => Arr::get($data, 'meta_description'),
            'keywords' => Arr::get($data, 'meta_keywords'),
        ], fn ($value) => filled($value));

        $data['meta'] = empty($meta) ? null : $meta;
        unset($data['meta_title'], $data['meta_description'], $data['meta_keywords']);

        $status = $data['status'];
        $publishedAtInput = $data['published_at'] ?? null;
        $publishedAt = filled($publishedAtInput) ? Carbon::parse($publishedAtInput) : null;

        if ($status === BlogPost::STATUS_SCHEDULED) {
            if (!$publishedAt) {
                throw ValidationException::withMessages([
                    'published_at' => 'Scheduled posts require a future publish date.',
                ]);
            }
            if ($publishedAt->lessThanOrEqualTo(now())) {
                throw ValidationException::withMessages([
                    'published_at' => 'Scheduled publish date must be in the future.',
                ]);
            }
        }

        if ($status === BlogPost::STATUS_PUBLISHED) {
            if ($publishedAt && $publishedAt->greaterThan(now())) {
                $publishedAt = now();
            }

            $data['published_at'] = $publishedAt ?? ($existing?->published_at ?? now());
        } elseif ($status === BlogPost::STATUS_SCHEDULED) {
            $data['published_at'] = $publishedAt;
        } else {
            $data['published_at'] = null;
        }

        $data['blog_category_id'] = filled($data['blog_category_id'] ?? null)
            ? (int) $data['blog_category_id']
            : null;

        if (array_key_exists('featured_image', $data)) {
            $data['featured_image'] = filled($data['featured_image'])
                ? trim($data['featured_image'])
                : null;
        }

        return $data;
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = Str::random(8);
        }

        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = BlogPost::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
