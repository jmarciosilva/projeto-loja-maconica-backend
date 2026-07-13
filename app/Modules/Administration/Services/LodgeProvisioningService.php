<?php

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\Lodge;
use App\Modules\Administration\Models\Permission;
use App\Modules\Administration\Models\Role;
use Illuminate\Support\Collection;

class LodgeProvisioningService
{
    /**
     * @var list<array{name: string, slug: string}>
     */
    private const BASELINE_PERMISSIONS = [
        ['name' => 'Acessar dashboard', 'slug' => 'dashboard.view'],
        ['name' => 'Gerenciar usuários', 'slug' => 'users.manage'],
        ['name' => 'Gerenciar permissões', 'slug' => 'permissions.manage'],
        ['name' => 'Gerenciar configurações da Loja', 'slug' => 'settings.manage'],
    ];

    /**
     * @return Collection<int, Permission> keyed by slug
     */
    public function ensureBaselinePermissions(): Collection
    {
        return collect(self::BASELINE_PERMISSIONS)
            ->map(fn (array $permission) => Permission::query()->firstOrCreate(
                ['slug' => $permission['slug']],
                $permission,
            ))
            ->keyBy('slug');
    }

    /**
     * Creates (or reuses) the default "Administrador Geral" and "Obreiro"
     * roles for a Lodge, so self-service registration always has a role
     * to attach the admin and pending members to.
     *
     * @return array{admin: Role, member: Role}
     */
    public function provisionDefaultRoles(Lodge $lodge): array
    {
        $permissions = $this->ensureBaselinePermissions();

        $adminRole = Role::query()->firstOrCreate(
            ['lodge_id' => $lodge->id, 'slug' => 'administrador-geral'],
            [
                'name' => 'Administrador Geral',
                'description' => 'Perfil com acesso administrativo completo à Loja.',
                'is_system' => true,
            ],
        );
        $adminRole->permissions()->sync($permissions->pluck('id'));

        $memberRole = Role::query()->firstOrCreate(
            ['lodge_id' => $lodge->id, 'slug' => 'obreiro'],
            [
                'name' => 'Obreiro',
                'description' => 'Perfil padrão de obreiro vinculado à Loja.',
                'is_system' => true,
            ],
        );
        $memberRole->permissions()->sync($permissions->only(['dashboard.view'])->pluck('id'));

        return ['admin' => $adminRole, 'member' => $memberRole];
    }
}
