<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            ['name' => 'Administrador', 'description' => 'Pode gerenciar qualquer recurso e configuração do sistema.'],
            ['name' => 'Gestor',        'description' => ''],
            ['name' => 'Fiscal',        'description' => ''],
            ['name' => 'Motorista',     'description' => ''],
        ];

        foreach ($profiles as $profile) {
            Profile::firstOrCreate($profile);
        }
    }
}
