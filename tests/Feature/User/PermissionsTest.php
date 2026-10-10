<?php

use App\Models\Permission;

test('retorna as permissões de veículos do usuário autenticado', function () {
    $user = createUser();
    getUserWithPermission($user, 'view', 'vehicles');
    getUserWithPermission($user, 'update', 'vehicles');
    Permission::create(['action' => 'create', 'module' => 'vehicles']);
    Permission::create(['action' => 'delete', 'module' => 'vehicles']);
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/users/permissions?modules[]=vehicles');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'data' => [
            'vehicles' => [
                'view' => true,
                'create' => false,
                'update' => true,
                'delete' => false,
            ],
        ],
    ]);
});

test('retorna todas as ações de veículos como negadas quando o usuário não tem permissões no módulo', function () {
    $user = createUser();
    foreach (['view', 'create', 'update', 'delete'] as $action) {
        Permission::create(['action' => $action, 'module' => 'vehicles']);
    }
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/users/permissions?modules[]=vehicles');

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'vehicles' => [
                'view' => false,
                'create' => false,
                'update' => false,
                'delete' => false,
            ],
        ],
    ]);
});

test('não permite consultar as próprias permissões sem autenticação', function () {
    $response = $this->getJson('/api/v1/users/permissions?modules[]=vehicles');

    $response->assertStatus(401);
});
