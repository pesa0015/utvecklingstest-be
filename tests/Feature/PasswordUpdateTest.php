<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $data = [
            'current_password' => 'password',
            'password' => 'newpassword'
        ];

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $response = $this->put('/api/password', $data, ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('newpassword', $user->refresh()->password));
    }

    public function test_cannot_update_password_with_wrong_credentials(): void
    {
        $user = User::factory()->create();

        $data = [
            'current_password' => 'test',
            'password' => 'newpassword'
        ];

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $response = $this->put('/api/password', $data, ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);

        $this->assertFalse(Hash::check('newpassword', $user->refresh()->password));
    }
}
