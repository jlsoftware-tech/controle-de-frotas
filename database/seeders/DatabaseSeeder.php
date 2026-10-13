<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Permission;
use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $modules = ['users', 'profiles', 'secretariats'];
        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::create([
                    'action' => $action,
                    'module' => $module,
                ])->save();
            }
        }

        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('senha123'),
            'profile_id' => Profile::create([
                'name' => 'Test Profile',
                'description' => 'Test Profile',
            ]),
            'secretariat_id' => Secretariat::create([
                'name' => 'Test Secretariat',
                'acronym' => 'Test Secretariat',
            ]),
        ]);

        $testUser->permissions()->attach(Permission::all());

        Driver::create([
            'cpf' => '00011122233',
            'cnh' => '00011122233',
            'cnh_category' => 'AB',
            'cnh_expiration_date' => now(),
            'phone_number' => '88988888888',
            'user_id' => $testUser->id
        ]);
    }
}
