<?php

test('atualiza os dados do usuário autenticado com sucesso', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $novoNome = fake()->name;
    $novoEmail = fake()->unique()->email;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'name' => $novoNome,
            'email' => $novoEmail,
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Dados atualizados com sucesso',
        'data' => [
            'name' => $novoNome,
            'email' => $novoEmail,
        ],
    ]);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => ['id', 'name', 'email'],
    ]);

    $response->assertJsonMissingPath('data.password');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $novoNome,
        'email' => $novoEmail,
    ]);
});

test('atualiza apenas o campo enviado (name), mantendo o restante', function () {
    $user = createUser(['name' => 'Nome Original']);
    $token = authenticateUser($user);

    $novoNome = fake()->name;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'name' => $novoNome,
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'data' => [
            'name' => $novoNome,
            'email' => $user->email,
        ],
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $novoNome,
        'email' => $user->email,
    ]);
});

test('não atualiza dados sem autenticação', function () {
    $response = $this->putJson('/api/v1/users/update', [
        'name' => fake()->name,
    ]);

    $response->assertStatus(401);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);

    $response->assertJson([
        'success' => false,
        'status_code' => 401,
        'data' => null,
    ]);
});

test('retorna erro quando nenhum dado é enviado', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', []);

    $response->assertStatus(400);

    $response->assertJson([
        'success' => false,
        'status_code' => 400,
        'message' => 'Nenhum dado foi enviado',
        'data' => null,
    ]);
});

test('não atualiza com email em formato inválido', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'email' => 'email-invalido',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('email', 'data');
});

test('não atualiza com email já usado por outro usuário', function () {
    $user = createUser();
    $token = authenticateUser($user);

    createUser(['email' => 'ocupado@example.com']);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'email' => 'ocupado@example.com',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('email', 'data');
});

test('permite manter o próprio email ao atualizar', function () {
    $user = createUser(['email' => 'mesmo@example.com']);
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'name' => fake()->name,
            'email' => 'mesmo@example.com',
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'data' => [
            'email' => 'mesmo@example.com',
        ],
    ]);
});

test('não atualiza dados de outro usuário, apenas do usuário autenticado', function () {
    $userLogado = createUser(['name' => 'Usuário Logado']);
    $outroUsuario = createUser(['name' => 'Outro Usuário']);
    $token = authenticateUser($userLogado);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'name' => 'Nome Alterado',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $userLogado->id,
        'name' => 'Nome Alterado',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $outroUsuario->id,
        'name' => 'Outro Usuário',
    ]);
});

test('não aceita o campo name maior que 255 caracteres', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/update', [
            'name' => str_repeat('a', 256),
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('name', 'data');
});
