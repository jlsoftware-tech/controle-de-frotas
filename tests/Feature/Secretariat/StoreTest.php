<?php

use App\Models\Secretariat;

test('cria secretaria com dados válidos estando autenticado', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $name = fake()->company;
    $acronym = fake()->lexify('???');

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/secretariats', [
            'name' => $name,
            'acronym' => $acronym,
        ]);

    $response->assertStatus(201);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => ['id', 'name', 'acronym', 'created_at', 'updated_at', 'deleted_at'],
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 201,
        'message' => 'Secretaria cadastrada com sucesso!',
        'data' => [
            'name' => $name,
            'acronym' => $acronym,
        ],
    ]);

    $this->assertDatabaseHas('secretariats', [
        'name' => $name,
        'acronym' => $acronym,
    ]);
});

test('não cria secretaria sem campos obrigatórios', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $countBefore = Secretariat::count();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/secretariats', []);

    $response->assertStatus(422);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [],
    ]);

    $response->assertJsonValidationErrorFor('name', 'data');
    $response->assertJsonValidationErrorFor('acronym', 'data');

    $this->assertDatabaseCount('secretariats', $countBefore);
});

test('não cria secretaria com nome maior que o permitido', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/secretariats', [
            'name' => str_repeat('a', 256),
            'acronym' => 'OK',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('name', 'data');
});

test('não cria secretaria com sigla maior que o permitido', function () {
    $user = createUser();
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/secretariats', [
            'name' => 'Secretaria de Teste',
            'acronym' => str_repeat('a', 17),
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('acronym', 'data');
});

test('não permite cadastrar secretaria sem autenticação', function () {
    $response = $this->postJson('/api/v1/secretariats', [
        'name' => 'Secretaria de Teste',
        'acronym' => 'ST',
    ]);

    $response->assertStatus(401);

    $this->assertDatabaseMissing('secretariats', ['name' => 'Secretaria de Teste']);
});
