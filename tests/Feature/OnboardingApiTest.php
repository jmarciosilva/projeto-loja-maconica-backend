<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\Lodge;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Services\LodgeProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OnboardingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_register_a_new_lodge_and_is_signed_in_immediately(): void
    {
        $response = $this->postJson('/api/v1/auth/register/lodge', [
            'lodge_name' => 'Loja Nova Aurora',
            'registration_number' => '123',
            'lodge_email' => 'contato@nova-aurora.test',
            'potencia' => 'GOB',
            'rito' => 'REAA',
            'tipo' => 'Loja Simbólica',
            'address_zip_code' => '01001-000',
            'address_street' => 'Praça da Sé',
            'address_number' => '100',
            'address_neighborhood' => 'Sé',
            'address_city' => 'São Paulo',
            'address_state' => 'sp',
            'admin_name' => 'Fulano de Tal',
            'admin_cim' => '1234',
            'admin_cpf' => '123.456.789-01',
            'admin_degree' => 'Mestre',
            'admin_email' => 'fulano@example.test',
            'admin_whatsapp' => '11988887777',
            'admin_password' => 'super-secret',
            'admin_password_confirmation' => 'super-secret',
        ]);

        $response->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'fulano@example.test')
            ->assertJsonPath('user.status', 'active')
            ->assertJsonPath('user.lodge.name', 'Loja Nova Aurora')
            ->assertJsonPath('user.roles.0.slug', 'administrador-geral');

        $lodge = Lodge::query()->where('slug', 'loja-nova-aurora')->firstOrFail();
        $this->assertSame('SP', $lodge->address_state);
        $this->assertSame('01001000', $lodge->address_zip_code);

        $admin = User::query()->where('email', 'fulano@example.test')->firstOrFail();
        $this->assertSame('12345678901', $admin->cpf);

        $token = $response->json('access_token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'fulano@example.test');
    }

    public function test_lodge_registration_options_are_public(): void
    {
        $this->getJson('/api/v1/public/lodge-registration-options')
            ->assertOk()
            ->assertJsonPath('potencias', ['GOSP', 'GOB', 'GLESP', 'GOP'])
            ->assertJsonPath('ritos', ['REAA', 'Rito de York', 'Rito Adonhiramita', 'Rito Brasileiro'])
            ->assertJsonPath('tipos', ['Loja Simbólica'])
            ->assertJsonPath('graus', ['Aprendiz', 'Companheiro', 'Mestre', 'Venerável']);
    }

    public function test_public_lodge_listing_only_exposes_active_lodges_and_minimal_fields(): void
    {
        $active = Lodge::factory()->create(['name' => 'Loja Ativa', 'status' => 'active']);
        Lodge::factory()->create(['name' => 'Loja Inativa', 'status' => 'inactive']);

        $response = $this->getJson('/api/v1/public/lodges');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $active->uuid)
            ->assertJsonMissingPath('data.0.email');
    }

    public function test_visitor_can_register_as_pending_member_and_cannot_login_until_approved(): void
    {
        $lodge = Lodge::factory()->create(['status' => 'active']);

        $register = $this->postJson('/api/v1/auth/register/member', [
            'lodge_uuid' => $lodge->uuid,
            'name' => 'Novo Obreiro',
            'email' => 'obreiro@example.test',
            'password' => 'super-secret',
            'password_confirmation' => 'super-secret',
        ]);

        $register->assertCreated()
            ->assertJsonPath('user.status', 'pending')
            ->assertJsonMissingPath('access_token');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'obreiro@example.test',
            'password' => 'super-secret',
        ]);

        $login->assertForbidden()
            ->assertJsonPath('message', 'Seu cadastro está aguardando aprovação da Loja.');
    }

    public function test_lodge_admin_can_list_and_approve_pending_members(): void
    {
        $lodge = Lodge::factory()->create(['status' => 'active']);
        $roles = app(LodgeProvisioningService::class)->provisionDefaultRoles($lodge);

        $admin = User::factory()->create(['lodge_id' => $lodge->id, 'status' => 'active']);
        $admin->roles()->attach($roles['admin']->id);

        $pending = User::factory()->create(['lodge_id' => $lodge->id, 'status' => 'pending']);
        $pending->roles()->attach($roles['member']->id);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/members/pending')
            ->assertOk()
            ->assertJsonPath('data.0.uuid', $pending->uuid);

        $this->postJson("/api/v1/members/{$pending->uuid}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertSame('active', $pending->fresh()->status);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $pending->email,
            'password' => 'password',
        ]);

        $login->assertOk();
    }

    public function test_admin_cannot_approve_member_from_another_lodge(): void
    {
        $lodgeA = Lodge::factory()->create(['status' => 'active']);
        $lodgeB = Lodge::factory()->create(['status' => 'active']);
        $rolesA = app(LodgeProvisioningService::class)->provisionDefaultRoles($lodgeA);
        $rolesB = app(LodgeProvisioningService::class)->provisionDefaultRoles($lodgeB);

        $adminA = User::factory()->create(['lodge_id' => $lodgeA->id, 'status' => 'active']);
        $adminA->roles()->attach($rolesA['admin']->id);

        $pendingB = User::factory()->create(['lodge_id' => $lodgeB->id, 'status' => 'pending']);
        $pendingB->roles()->attach($rolesB['member']->id);

        Sanctum::actingAs($adminA);

        $this->postJson("/api/v1/members/{$pendingB->uuid}/approve")
            ->assertForbidden();

        $this->assertSame('pending', $pendingB->fresh()->status);
    }
}
