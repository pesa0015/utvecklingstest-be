<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Checkin;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CheckinTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test check in to work
     */
    public function test_list_checkins_returns_collection(): void
    {
        $user = User::factory()->create();

        $checkin = Checkin::factory()->create();

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $response = $this->get('/api/', [], ['Authorization' => 'Bearer ' . $token]);

        $this->assertDatabaseCount('checkins', 1);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) =>
            $json->where('0.uuid', $checkin->uuid)
                ->where('0.in', $checkin->in)
                ->where('0.out', $checkin->out)
            );
    }

    /**
     * Test check in without gps to work
     */
    public function test_check_in_without_gps_returns_ok(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseEmpty('checkins');

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $response = $this->get('/api/checkin?gps=false', [], ['Authorization' => 'Bearer ' . $token]);
        
        $this->assertDatabaseHas('checkins', [
            'in' => now(),
            'user_id' => $user->id,
        ]);

        $this->assertNotNull(Checkin::where('user_id', $user->id)->whereNull('latitude')->get());

        $response->assertStatus(200);
    }

    /**
     * Test check in with gps to work
     */
    public function test_check_in_with_gps_returns_ok(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseEmpty('checkins');

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $response = $this->get('/api/checkin?gps=true', [], ['Authorization' => 'Bearer ' . $token]);
        
        $this->assertDatabaseHas('checkins', [
            'in' => now(),
            'user_id' => $user->id
        ]);

        $this->assertNotNull(Checkin::where('user_id', $user->id)->whereNotNull('latitude')->get());

        $response->assertStatus(200);
    }

    /**
     * Test check out from work
     */
    public function test_check_out_returns_ok(): void
    {
        $user = User::factory()->create();

        $checkin = Checkin::factory()->create();

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'out' => null
        ]);

        $response = $this->get('/api/checkout', [], ['Authorization' => 'Bearer ' . $token]);

        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'out' => now()
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test update previous checkins
     */
    public function test_update_previous_checkins_returns_ok(): void
    {
        $user = User::factory()->create();

        $checkin = Checkin::factory()->create();

        $token = auth()->attempt(['pnr' => $user->pnr, 'password' => 'password']);

        // $checkinTime = $user->checkins()->latest()->first()->in;

        $data = [
            'checkin' => Carbon::parse($checkin->in)->addMinutes(5)->format('Y-m-d H:i:s')
        ];

        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'in' => Carbon::parse($checkin->in)->format('Y-m-d H:i:s')
        ]);

        // Update checkin time
        $response = $this->put('/api/checkin/' . $checkin->uuid, $data, ['Authorization' => 'Bearer ' . $token]);

        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'in' => Carbon::parse($data['checkin'])->format('Y-m-d H:i:s')
        ]);

        $response->assertStatus(200);

        $checkin->update(['out' => Carbon::parse($checkin->in)->addHours(8)]);

        $data = [
            'checkout' => Carbon::parse($checkin->fresh()->out)->addMinutes(5)->format('Y-m-d H:i:s')
        ];

        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'out' => Carbon::parse($checkin->out)->format('Y-m-d H:i:s')
        ]);

        // Update checkout time
        $response = $this->put('/api/checkin/' . $checkin->uuid, $data, ['Authorization' => 'Bearer ' . $token]);

        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'out' => Carbon::parse($data['checkout'])->format('Y-m-d H:i:s')
        ]);

        $response->assertStatus(200);
    }
}
