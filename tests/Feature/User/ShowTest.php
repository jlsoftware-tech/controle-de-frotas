<?php

test('exibir os dados de um usuário específico', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $otherUser = createUser();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/users/'.$otherUser->id);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            'id',
            'name',
            'email',
            'profile' => ['id', 'name'],
            'secretariat' => ['id', 'name', 'acronym'],
            'created_at',
            'updated_at',
            'deleted_at',
        ],
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => '',
        'data' => [
            'id' => $otherUser->id,
            'email' => $otherUser->email,
        ],
    ]);
});

test('retorna 404 ao exibir usuário inexistente', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/users/99999');

    $response->assertStatus(404);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [],
    ]);

    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não permite exibir usuário sem autenticação', function () {
    $user = createUser();

    $response = $this->getJson('/api/v1/users/'.$user->id);

    $response->assertStatus(401);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});
