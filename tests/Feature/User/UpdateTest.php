<?php

test('atualiza usuário com dados válidos', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $username = fake()->name;
    $email = fake()->email;

    $payload = [
        'name' => $username,
        'email' => $email,
        'password' => 'senhaforte',
        'password_confirmation' => 'senhaforte',
    ];

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson('/api/v1/users/' . $user->id, $payload);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Dados atualizados com sucesso.',
        'data' => [
            'name' => $username,
            'email' => $email,
        ],
    ]);
});

test('não atualiza usuário inexistente', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->putJson('/api/v1/users/99999', [
            'name' => fake()->name,
        ]);

    $response->assertStatus(404);

    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não atualiza usuário sem autenticação', function () {
    $user = createUser();

    $response = $this->putJson('/api/v1/users/' . $user->id, [
        'name' => fake()->name,
    ]);

    $response->assertStatus(500);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});

test('não atualiza usuário com email em formato inválido', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->putJson('/api/v1/users/' . $user->id, [
            'email' => 'email-invalido',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('email', 'data');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [ 'email' ],
    ]);
});

test('não atualiza usuário com email já usado por outro usuário', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);

    createUser(['email' => 'ocupado@example.com']);

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->putJson('/api/v1/users/' . $user->id, [
            'email' => 'ocupado@example.com',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('email', 'data');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});

test('permite atualizar usuário mantendo o próprio email', function () {
    $user = createUser(['email' => 'mesmo@example.com']);
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->putJson('/api/v1/users/' . $user->id, [
            'name' => fake()->name,
            'email' => 'mesmo@example.com',
        ]);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            'id',
            'name',
            'email',
        ],
    ]);
});
