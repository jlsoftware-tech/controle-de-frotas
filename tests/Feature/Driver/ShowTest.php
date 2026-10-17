<?php

use App\Models\Driver;
use Symfony\Component\HttpFoundation\Response;

test('exibir os dados de um motorista específico', function () {
    $user = getUserWithPermission(createUser(), 'view', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'data' => [
            'id', 'cpf', 'status', 'name', 'email', 'cnh', 'cnh_category', 'cnh_expiration_date', 'phone_number',
            'profile' => ['id', 'name'],
            'secretariat' => ['id', 'name', 'acronym'],
            'created_at', 'updated_at', 'deleted_at'
        ]
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Sucesso.',
        'data' => [
            'id' => $driver->id,
            'name' => $driver->user->name,
            'email' => $driver->user->email,
            'cpf' => $driver->cpf,
            'cnh' => $driver->cnh,
        ],
    ]);
});

test('retorna 404 ao exibir motorista inexistente', function () {
    $user = getUserWithPermission(createUser(), 'view', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/drivers/99999');

    $response->assertStatus(Response::HTTP_NOT_FOUND);

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
    $driver = createValidDriver();

    $response = $this->getJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);
});

test('não permite exibir motorista sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});
