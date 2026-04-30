<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_update_keeps_existing_slug_and_redirects_to_current_edit_route(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_ADMIN,
        ]);

        $category = Category::create([
            'name' => "Women's Coats, Jackets & Vests",
            'slug' => 'womens-coats-jackets-vests',
            'listing_type' => 'products',
            'description' => 'Outerwear.',
            'listing_fee' => 0.25,
            'listing_frequency' => 4,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.categories.edit', $category))
            ->put(route('admin.categories.update', $category), [
                'name' => "Women's Coats, Jackets & Vests",
                'slug' => 'women-s-coats-jackets-vests',
                'parent_id' => null,
                'listing_type' => 'products',
                'description' => '<p>Warm coats, jackets, and vests.</p>',
                'listing_fee' => '0.25',
                'listing_frequency' => '4',
            ]);

        $category->refresh();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.categories.edit', $category));

        $this->assertSame('womens-coats-jackets-vests', $category->slug);
        $this->assertSame('Warm coats, jackets, and vests.', $category->description);

        $this->actingAs($admin)
            ->get(route('admin.categories.edit', $category))
            ->assertOk();

        $this->get(route('category.show', ['slug' => 'womens-coats-jackets-vests']))
            ->assertOk();
    }

    public function test_legacy_category_slug_urls_resolve_when_a_previous_update_changed_apostrophe_slugs(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_ADMIN,
        ]);

        Category::create([
            'name' => "Women's Coats, Jackets & Vests",
            'slug' => 'women-s-coats-jackets-vests',
            'listing_type' => 'products',
            'description' => 'Outerwear.',
            'listing_fee' => 0.25,
            'listing_frequency' => 4,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.edit', ['category' => 'womens-coats-jackets-vests']))
            ->assertOk();

        $this->get(route('category.show', ['slug' => 'womens-coats-jackets-vests']))
            ->assertOk();
    }
}
