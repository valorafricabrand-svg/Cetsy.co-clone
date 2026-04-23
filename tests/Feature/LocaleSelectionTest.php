<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_locale(): void
    {
        $response = $this
            ->from('/')
            ->get(route('locale.set', ['locale' => 'sw']));

        $response
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'sw')
            ->assertCookie(config('locales.cookie', 'locale'), 'sw');
    }

    public function test_authenticated_locale_switch_updates_the_user_preference(): void
    {
        $user = User::factory()->create([
            'preferred_locale' => 'en',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->get(route('locale.set', ['locale' => 'sw']));

        $response
            ->assertRedirect('/profile')
            ->assertSessionHas('locale', 'sw')
            ->assertCookie(config('locales.cookie', 'locale'), 'sw');

        $this->assertSame('sw', $user->fresh()->preferred_locale);
    }

    public function test_profile_page_uses_the_authenticated_users_preferred_locale(): void
    {
        $user = User::factory()->create([
            'preferred_locale' => 'sw',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response
            ->assertOk()
            ->assertSee('Maelezo Mafupi');
    }
}
