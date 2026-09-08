<?php

test('listar usuários autenticado', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

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

    $response->assertStatus(500);
});
