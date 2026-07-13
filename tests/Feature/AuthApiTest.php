<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\Lodge;
use App\Modules\Administration\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_read_current_profile(): void
    {
        $lodge = Lodge::factory()->create();
        $user = User::factory()->create([
            'lodge_id' => $lodge->id,
            'email' => 'admin@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
            'device_name' => 'Feature Test',
        ]);

        $login->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.lodge.uuid', $lodge->uuid);

        $token = $login->json('access_token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.lodge.uuid', $lodge->uuid);
    }

    public function test_user_cannot_access_another_lodge_context(): void
    {
        $userLodge = Lodge::factory()->create();
        $otherLodge = Lodge::factory()->create();
        $user = User::factory()->create(['lodge_id' => $userLodge->id]);

        Sanctum::actingAs($user);

        $this->withHeader('X-Lodge-Uuid', $otherLodge->uuid)
            ->getJson('/api/v1/auth/me')
            ->assertForbidden();
    }
}
