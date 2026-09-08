<?php

namespace Tests\Feature;

use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_an_authenticated_user_can_view_the_dashboard(): void
    {
        $response = $this->actingAs(User::factory()->make())->get('/dashboard');

        $response->assertOk()->assertSee('Dashboard Overview');
    }
}
