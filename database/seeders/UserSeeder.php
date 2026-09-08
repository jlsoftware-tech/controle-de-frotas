<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admPerfil = Profile::where('name', 'Administrador')->first();
        $gestorPerfil = Profile::where('name', 'Gestor')->first();
        $fiscalPerfil = Profile::where('name', 'Fiscal')->first();
        $motoristaPerfil = Profile::where('name', 'Motorista')->first();

        $secretariaAdm = Secretariat::where('name', 'Secretaria de Administração')->first();
        $secretariaSaude = Secretariat::where('name', 'Secretaria de Saúde')->first();

        $usuarios = [
            [
                'name' => 'Administrador do Sistema',
                'email' => 'admin@test.com',
                'password' => Hash::make('12345678'),
                'profile_id' => $admPerfil->id,
                'secretariat_id' => $secretariaAdm->id,
            ],
            [
                'name' => 'Gestor de Frotas',
                'email' => 'gestor@test.com',
                'password' => Hash::make('12345678'),
                'profile_id' => $gestorPerfil->id,
                'secretariat_id' => $secretariaAdm->id,
            ],
            [
                'name' => 'Fiscal de Saúde',
                'email' => 'fiscal@test.com',
                'password' => Hash::make('12345678'),
                'profile_id' => $fiscalPerfil->id,
                'secretariat_id' => $secretariaSaude->id,
            ],
            [
                'name' => 'Motorista Padrão',
                'email' => 'motorista@test.com',
                'password' => Hash::make('12345678'),
                'profile_id' => $motoristaPerfil->id,
                'secretariat_id' => $secretariaSaude->id,
            ],
        ];

        foreach ($usuarios as $usuario) {
            // cria um novo usuário se não existir ainda
            User::firstOrCreate(
                ['email' => $usuario['email']],
                $usuario
            );
        }
    }
}
