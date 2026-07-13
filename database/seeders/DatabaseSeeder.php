<?php

namespace Database\Seeders;

use App\Modules\Administration\Models\Lodge;
use App\Modules\Administration\Models\Permission;
use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $lodge = Lodge::query()->firstOrCreate(
            ['slug' => 'loja-piloto'],
            [
                'name' => 'Loja Piloto',
                'registration_number' => '001',
                'email' => 'contato@loja-piloto.test',
                'status' => 'active',
            ],
        );

        $permissions = collect([
            ['name' => 'Acessar dashboard', 'slug' => 'dashboard.view'],
            ['name' => 'Gerenciar usuários', 'slug' => 'users.manage'],
            ['name' => 'Gerenciar permissões', 'slug' => 'permissions.manage'],
            ['name' => 'Gerenciar configurações da Loja', 'slug' => 'settings.manage'],
        ])->map(fn (array $permission) => Permission::query()->firstOrCreate(
            ['slug' => $permission['slug']],
            $permission,
        ));

        $adminRole = Role::query()->firstOrCreate(
            ['lodge_id' => $lodge->id, 'slug' => 'administrador-geral'],
            [
                'name' => 'Administrador Geral',
                'description' => 'Perfil inicial com acesso administrativo à Loja piloto.',
                'is_system' => true,
            ],
        );

        $adminRole->permissions()->sync($permissions->pluck('id'));

        $admin = User::query()->firstOrCreate([
            'email' => 'admin@loja-piloto.test',
        ], [
            'lodge_id' => $lodge->id,
            'name' => 'Administrador Geral',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
