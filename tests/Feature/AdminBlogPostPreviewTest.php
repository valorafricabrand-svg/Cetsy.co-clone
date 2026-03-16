<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBlogPostPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_blog_preview_renders_saved_html_body(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_ADMIN,
        ]);

        $post = BlogPost::create([
            'user_id' => $admin->id,
            'title' => 'Admin Preview Post',
            'slug' => 'admin-preview-post',
            'body' => '<p><strong>Preview HTML</strong> with <em>rich text</em>.</p>',
            'status' => BlogPost::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.blog-posts.show', $post));

        $response->assertOk();
        $response->assertSee('<strong>Preview HTML</strong>', false);
        $response->assertSee('<em>rich text</em>', false);
        $response->assertDontSee('&lt;strong&gt;Preview HTML&lt;/strong&gt;', false);
    }
}
