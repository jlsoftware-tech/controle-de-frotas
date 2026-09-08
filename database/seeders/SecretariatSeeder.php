<?php

namespace Database\Seeders;

use App\Models\Secretariat;
use Illuminate\Database\Seeder;

class SecretariatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $secretariats = [
            ['name' => 'Secretaria de Administração',   'acronym' => 'SEMAD'],
            ['name' => 'Secretaria de Educação',        'acronym' => 'SME'],
            ['name' => 'Secretaria de Fazenda',         'acronym' => 'SEFAZ'],
            ['name' => 'Secretaria de Planejamento',    'acronym' => 'SEPLAN'],
            ['name' => 'Secretaria de Saúde',           'acronym' => 'SMS'],
        ];

        foreach ($secretariats as $secretariat) {
            Secretariat::firstOrCreate($secretariat);
        }
    }
}
