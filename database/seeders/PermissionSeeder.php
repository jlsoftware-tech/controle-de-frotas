<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = ['users', 'profiles', 'secretariats', 'permissions', 'fleets'];
        $actions = ['create', 'view', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'module' => $module,
                    'name' => $action,
                ]);
            }
        }

        // atribui todas as permissões ao admin
        $admin = Profile::where('name', 'Administrador')->first();
        $admin->permissions()->sync(Permission::pluck('id'));

        // gestor tem acesso total a frotas e secretarias, leitura em usuários
        $gestor = Profile::where('name', 'Gestor')->first();
        $gestor->permissions()->sync(
            Permission::whereIn('module', ['fleets', 'secretarias'])
                ->orWhere(
                    fn ($q) => $q->where('module', 'users')
                        ->where('name', 'view')
                )->pluck('id')
        );

        // fiscal tem apenas leitura em frotas e secretarias
        $fiscal = Profile::where('name', 'Fiscal')->first();
        $fiscal->permissions()->sync(
            Permission::whereIn('module', ['fleets', 'secretarias'])
                ->where('name', 'view')
                ->pluck('id')
        );

        // motorista apenas tem leitura de frotas
        $driver = Profile::where('name', 'Motorista')->first();
        $driver->permissions()->sync(
            Permission::where('module', 'fleets')
                ->where('name', 'view')
                ->pluck('id')
        );
    }
}
