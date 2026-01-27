<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_login(): void
    {
        $user = User::factory()->create();

        $data = [
            'pnr' => $user->pnr,
            'password' => 'password'
        ];

        $response = $this->post('/api/login', $data);

        $response->assertStatus(200);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $response = $this->get('/api/logout', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }
}
