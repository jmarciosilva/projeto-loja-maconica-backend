<?php

namespace Database\Seeders;

use App\Modules\Administration\Models\Lodge;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Services\LodgeProvisioningService;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(LodgeProvisioningService $provisioning): void
    {
        $lodge = Lodge::query()->firstOrCreate(
            ['slug' => 'loja-piloto'],
            [
                'name' => 'Loja Piloto',
                'registration_number' => '001',
                'email' => 'contato@loja-piloto.test',
                'potencia' => 'GOB',
                'rito' => 'REAA',
                'type' => 'Loja Simbólica',
                'address_zip_code' => '01001000',
                'address_street' => 'Praça da Sé',
                'address_number' => '100',
                'address_neighborhood' => 'Sé',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'status' => 'active',
            ],
        );

        $roles = $provisioning->provisionDefaultRoles($lodge);

        $admin = User::query()->firstOrCreate([
            'email' => 'admin@loja-piloto.test',
        ], [
            'lodge_id' => $lodge->id,
            'name' => 'Administrador Geral',
            'nickname' => 'Administrador',
            'cim' => '00000001',
            'cpf' => '00000000000',
            'degree' => 'Mestre',
            'whatsapp' => '11999999999',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $admin->roles()->syncWithoutDetaching([$roles['admin']->id]);
    }
}
