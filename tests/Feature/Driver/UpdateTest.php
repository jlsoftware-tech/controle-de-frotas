<?php

use App\Models\Driver;
use Symfony\Component\HttpFoundation\Response;

test('edita motorista com dados válidos estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $newData = Driver::factory()->raw();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver->id, $newData);

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
        'status_code' => Response::HTTP_OK,
        'message' => 'Dados atualizados com sucesso',
        'data' => [
            'name' => $newData['name'],
            'email' => $newData['email'],
            'cpf' => $newData['cpf'],
            'cnh' => $newData['cnh'],
            'cnh_category' => $newData['cnh_category'],
            'cnh_expiration_date' => $newData['cnh_expiration_date'],
            'phone_number' => $newData['phone_number']
        ],
    ]);
});


test('não edita motorista com cpf inválido', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver->id, ['cpf' => '123a']);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não edita motorista com cpf repetido', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver1 = createValidDriver();
    $driver2 = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver2->id, ['cpf' => $driver1->cpf]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não edita motorista com cnh inválida', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver->id, ['cnh' => '123a']);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com cnh repetida', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver1 = createValidDriver();
    $driver2 = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver2->id, ['cnh' => $driver1->cnh]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não edita motorista com cnh vencida', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver->id, ['cnh_expiration_date' => now()->subDay()]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não edita motorista com telefone inválido', function () {
    $user = getUserWithPermission(createUser(), 'update', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver->id, ['phone_number' => '1234']);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não edita motorista sem autenticação', function () {
    $driver = createValidDriver();

    $response = $this->putJson('/api/v1/drivers/'.$driver->id, ['nome' => 'joaozinho']);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('não edita motorista sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/drivers/'.$driver->id, ['nome' => 'joaozinho']);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
})->skip();
