<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BlogVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_blog_named_routes_use_post_urls(): void
    {
        $this->assertSame('/post', parse_url(route('blog.index'), PHP_URL_PATH));
        $this->assertSame('/post/example-post', parse_url(route('blog.show', 'example-post'), PHP_URL_PATH));
    }

    public function test_laravel_does_not_claim_wordpress_blog_urls(): void
    {
        $this->get('/blog')->assertNotFound();
        $this->get('/blog/example-post')->assertNotFound();
    }

    public function test_old_cetsy_blog_urls_redirect_to_post_urls(): void
    {
        $this->get('/cetsy-blog?category=news')
            ->assertRedirect('/post?category=news')
            ->assertStatus(301);

        $this->get('/cetsy-blog/example-post')
            ->assertRedirect('/post/example-post')
            ->assertStatus(301);
    }

    public function test_published_posts_are_visible_even_if_published_at_is_in_the_future(): void
    {
        Carbon::setTestNow('2026-03-06 20:15:00');

        $category = BlogCategory::create([
            'name' => 'News',
            'slug' => 'news',
            'is_active' => true,
        ]);

        BlogPost::create([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'blog_category_id' => $category->id,
            'body' => 'Visible on the public blog.',
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->addHours(3),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Published Post');

        Carbon::setTestNow();
    }

    public function test_scheduled_posts_become_visible_after_their_publish_time(): void
    {
        Carbon::setTestNow('2026-03-06 20:15:00');

        $category = BlogCategory::create([
            'name' => 'Updates',
            'slug' => 'updates',
            'is_active' => true,
        ]);

        BlogPost::create([
            'title' => 'Scheduled Post',
            'slug' => 'scheduled-post',
            'blog_category_id' => $category->id,
            'body' => 'This should now be public.',
            'status' => BlogPost::STATUS_SCHEDULED,
            'published_at' => now()->subHour(),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Scheduled Post');

        Carbon::setTestNow();
    }

    public function test_future_scheduled_posts_stay_hidden(): void
    {
        Carbon::setTestNow('2026-03-06 20:15:00');

        $category = BlogCategory::create([
            'name' => 'Guides',
            'slug' => 'guides',
            'is_active' => true,
        ]);

        BlogPost::create([
            'title' => 'Future Scheduled Post',
            'slug' => 'future-scheduled-post',
            'blog_category_id' => $category->id,
            'body' => 'This should stay hidden for now.',
            'status' => BlogPost::STATUS_SCHEDULED,
            'published_at' => now()->addHour(),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertDontSee('Future Scheduled Post');

        Carbon::setTestNow();
    }
}
