<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    /**
     * Display a paginated list of live blog posts.
     */
    public function index(Request $request)
    {
        $categorySlug = $request->query('category');

        $categories = Cache::remember('blog_categories_active', now()->addMinutes(30), function () {
            return BlogCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });

        $postsQuery = BlogPost::query()
            ->with(['category'])
            ->live()
            ->latest('published_at')
            ->latest('created_at');

        if ($categorySlug) {
            $postsQuery->whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            });
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        return view('theme.cetsy.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $categorySlug,
        ]);
    }

    /**
     * Display a single blog post.
     */
    public function show(string $localeOrSlug, ?string $slug = null)
    {
        $slug = $slug ?? $localeOrSlug;

        $post = BlogPost::with(['category', 'author'])
            ->live()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = BlogPost::query()
            ->live()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, function ($query) use ($post) {
                $query->where('blog_category_id', $post->blog_category_id);
            })
            ->latest('published_at')
            ->latest('created_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'published_at', 'excerpt', 'featured_image']);

        return view('theme.cetsy.blog.show', [
            'post' => $post,
            'relatedPosts' => $related,
        ]);
    }
}
