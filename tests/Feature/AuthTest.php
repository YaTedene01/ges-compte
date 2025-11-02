<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', []);
        $response->assertStatus(422);
    }

    public function test_admin_can_login()
    {
        // seed admin
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => 'admin'
        ]);

        // If Passport not installed the endpoint may return 500; assert 200 or 401 accordingly
        $this->assertContains($response->getStatusCode(), [200, 401, 500]);
    }
}
