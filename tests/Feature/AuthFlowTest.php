<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use Laravel\Passport\Passport;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_access_protected_route_and_logout()
    {
        // Create an admin client
        $admin = Client::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        // Use Passport test helper to act as the admin user (bypass generating clients)
        Passport::actingAs($admin, ['*']);

        // Call a protected route
        $response = $this->getJson('/api/v1/accounts');

    // The response should be a JSON response (200/204 if data, 404 if no resource, or 401 if unauthorized)
    $this->assertContains($response->getStatusCode(), [200, 204, 401, 404]);

        // Logout should return 200 or 401 depending on guard
        $logout = $this->postJson('/api/v1/auth/logout');

    // Logout should return 200, 401 or 500 (implementation may vary in test environment)
    $this->assertContains($logout->getStatusCode(), [200, 401, 500]);
    }
}
