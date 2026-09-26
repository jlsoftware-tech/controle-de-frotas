<?php

test('listar usuários autenticado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'users');
    $token = authenticateUser($user);

    createUser();
    createUser();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/users');

    $response->assertStatus(200);

    /*
    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            '*' => ['id', 'nome', 'email'],
        ],
    ]);
    */
});

test('não permite listar usuários sem autenticação', function () {
    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(401);
});

test('não permite listar usuários sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/users');

    $response->assertStatus(403);
});
