<?php

use App\Models\Permission;
use App\Models\Profile;

function authenticateUserWithViewPermission()
{
    $viewProfilesPermission = Permission::create(['name' => 'view', 'module' => 'profiles']);

    $profile = Profile::create(['name' => 'admin', 'description' => 'admin']);
    $profile->permissions()->attach($viewProfilesPermission);

    $user = createUser(['profile_id' => $profile->id]);
    $token = authenticateUser($user);

    return $token;
}

test('listar permissões estando autenticado e autorizado', function () {
    $token = authenticateUserWithViewPermission();

    Permission::create(['name' => 'create', 'module' => 'users']);
    Permission::create(['name' => 'delete', 'module' => 'users']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/permissions');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            'items' => [
                '*' => ['id', 'name', 'module', 'created_at', 'updated_at'],
            ],
            'pagination' => ['numPerPage', 'currPage', 'totalEntries', 'totalPages'],
        ],
    ]);
});

test('filtra permissões por busca', function () {
    $token = authenticateUserWithViewPermission();

    Permission::create(['name' => 'create', 'module' => 'users']);
    Permission::create(['name' => 'delete', 'module' => 'users']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/permissions?search=create');

    $response->assertStatus(200);
    $response->assertJsonPath('data.pagination.totalEntries', 1);
});

test('não permite listar permissões sem a permissão necessária', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/permissions');

    $response->assertStatus(403);
});

test('não permite listar permissões sem autenticação', function () {
    $response = $this->getJson('/api/v1/permissions');

    $response->assertStatus(401);
});
