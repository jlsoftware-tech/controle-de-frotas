<?php

test('exibe veículo estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle([
        'name' => 'chevrolet onix',
        'plate' => 'ABC1D23',
        'renavam' => '01234567890',
        'chassi' => '9BWZZZ377VT004251',
        'brand' => 'Chevrolet',
        'model' => 'Onix',
        'model_year' => 2020,
        'fuel_type' => 'GASOLINE',
        'tank_capacity' => 44,
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            'id',
            'name',
            'plate',
            'renavam',
            'chassi',
            'brand',
            'model',
            'model_year',
            'fuel_type',
            'tank_capacity',
            'status',
            'secretariat_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
    ]);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'data' => [
            'id' => $vehicle->id,
            'name' => 'chevrolet onix',
            'plate' => 'ABC1D23',
            'renavam' => '01234567890',
            'chassi' => '9BWZZZ377VT004251',
            'brand' => 'Chevrolet',
            'model' => 'Onix',
            'model_year' => 2020,
            'fuel_type' => 'GASOLINE',
            'tank_capacity' => 44,
            'secretariat_id' => $vehicle->secretariat_id,
        ],
    ]);
});

test('formata as datas do veículo como dia/mês/ano hora:minuto:segundo', function () {
    $this->travelTo('2026-10-09 14:35:20');

    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(200);
    $response->assertJsonPath('data.created_at', '09/10/2026 14:35:20');
    $response->assertJsonPath('data.updated_at', '09/10/2026 14:35:20');
});

test('retorna 404 ao exibir veículo excluído', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle();
    $vehicle->delete();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(404);
});

test('retorna 404 ao exibir veículo inexistente', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles/99999');

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não permite exibir veículo sem autenticação', function () {
    $vehicle = createVehicle();

    $response = $this->getJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(401);
});

test('não permite exibir veículo sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $vehicle = createVehicle();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(403);
});
