<?php

use App\Models\Permission;
use App\Models\Profile;

function authenticateUserWithViewPermissionForShow()
{
    $viewProfilesPermission = Permission::create(['name' => 'view', 'module' => 'profiles']);

    $profile = Profile::create(['name' => 'admin', 'description' => 'admin']);
    $profile->permissions()->attach($viewProfilesPermission);

    $user = createUser(['profile_id' => $profile->id]);
    $token = authenticateUser($user);

    return [$token, $viewProfilesPermission];
}

test('exibir os dados de uma permissão específica estando autenticado e autorizado', function () {
    [$token, $permission] = authenticateUserWithViewPermissionForShow();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/permissions/'.$permission->id);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'data' => ['id', 'name', 'module', 'created_at', 'updated_at'],
    ]);
    $response->assertJsonPath('data.id', $permission->id);
});

test('não permite exibir permissão sem a permissão necessária', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $permission = Permission::create(['name' => 'create', 'module' => 'users']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/permissions/'.$permission->id);

    $response->assertStatus(403);
});

test('não permite exibir permissão sem autenticação', function () {
    $permission = Permission::create(['name' => 'create', 'module' => 'users']);

    $response = $this->getJson('/api/v1/permissions/'.$permission->id);

    $response->assertStatus(401);
});
