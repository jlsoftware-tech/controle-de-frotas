<?php

use App\Models\Profile;
use App\Models\Secretariat;

test('cria usuário com dados válidos', function () {
    $user = getUserWithPermission(createUser(), 'create', 'users');
    $token = authenticateUser($user);

    $username = fake()->name;
    $email = fake()->email;
    $profile = Profile::create([
        'name' => 'test',
        'description' => 'test',
    ]);
    $secretariat = Secretariat::create([
        'name' => 'test',
        'acronym' => 'test',
    ]);

    $payload = [
        'name' => $username,
        'email' => $email,
        'profile_id' => $profile->id,
        'secretariat_id' => $secretariat->id,
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ];

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/users', $payload);

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

    $this->assertDatabaseHas('users', [
        'name' => $username,
        'email' => $email,
    ]);
});

test('não cria usuário sem campos obrigatórios', function () {
    $user = getUserWithPermission(createUser(), 'create', 'users');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/users', []);

    $response->assertStatus(422);

    $response->assertJsonStructure(structure: [
        'success',
        'status_code',
        'message',
        'data' => [],
    ]);

    $response->assertJsonValidationErrorFor('name', 'data');
    $response->assertJsonValidationErrorFor('email', 'data');
    $response->assertJsonValidationErrorFor('password', 'data');
    $response->assertJsonValidationErrorFor('profile_id', 'data');
    $response->assertJsonValidationErrorFor('secretariat_id', 'data');
});

test('não cria usuário com email já existente', function () {
    $user = getUserWithPermission(createUser(), 'create', 'users');
    $token = authenticateUser($user);

    $existente = createUser(['email' => 'duplicado@example.com']);
    $payload = [
        'name' => fake()->name,
        'email' => $existente->email,
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ];

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/users', $payload);

    $response->assertStatus(422);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [],
    ]);

    $response->assertJsonValidationErrorFor('email', 'data');
});

test('não cria usuário com email em formato inválido', function () {
    $user = getUserWithPermission(createUser(), 'create', 'users');
    $token = authenticateUser($user);

    $payload = [
        'name' => fake()->name,
        'email' => 'email-invalido',
        'password' => 'senha123',
        'password_confirmation' => 'senha123',
    ];

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/users', $payload);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('email', 'data');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});

test('não cria usuário com senha menor que o mínimo permitido', function () {
    $user = getUserWithPermission(createUser(), 'create', 'users');
    $token = authenticateUser($user);

    $payload = [
        'name' => fake()->name,
        'email' => 'novo@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ];

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/users', $payload);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('password', 'data');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});

test('não cria usuário quando confirmação de senha não confere', function () {
    $user = getUserWithPermission(createUser(), 'create', 'users');
    $token = authenticateUser($user);

    $payload = [
        'name' => fake()->name,
        'email' => 'novo@example.com',
        'password' => 'senha123',
        'password_confirmation' => 'senha_diferente',
    ];

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/users', $payload);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('password', 'data');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});

test('não permite criar usuários sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $username = fake()->name;
    $email = fake()->email;
    $profile = Profile::create([
        'name' => 'test',
        'description' => 'test',
    ]);
    $secretariat = Secretariat::create([
        'name' => 'test',
        'acronym' => 'test',
    ]);

    $payload = [
        'name' => $username,
        'email' => $email,
        'profile' => $profile->id,
        'secretariat_id' => $secretariat->id,
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ];

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/'.$user->id, $payload);

    $response->assertStatus(403);
});
