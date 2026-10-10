<?php

use App\Models\Secretariat;

test('atualiza veículo com dados válidos estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle(['name' => 'veiculo antigo', 'plate' => 'ABC1D23']);
    $newSecretariat = Secretariat::create(['name' => 'Outra Secretaria', 'acronym' => 'OS']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, [
            'name' => 'veiculo novo',
            'plate' => 'EFG4H56',
            'renavam' => '98765432100',
            'chassi' => '9BWZZZ377VT004251',
            'brand' => 'Fiat',
            'model' => 'Strada',
            'model_year' => 2023,
            'fuel_type' => 'ETHANOL',
            'tank_capacity' => 55,
            'status' => 'UNAVAILABLE',
            'secretariat_id' => $newSecretariat->id,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Dados atualizados com sucesso!',
        'data' => [
            'id' => $vehicle->id,
            'name' => 'veiculo novo',
            'plate' => 'EFG4H56',
            'brand' => 'Fiat',
            'model' => 'Strada',
            'model_year' => 2023,
            'fuel_type' => 'ETHANOL',
            'tank_capacity' => 55,
            'status' => 'UNAVAILABLE',
            'secretariat_id' => $newSecretariat->id,
        ],
    ]);

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'name' => 'veiculo novo',
        'plate' => 'EFG4H56',
        'renavam' => '98765432100',
        'chassi' => '9BWZZZ377VT004251',
        'brand' => 'Fiat',
        'model' => 'Strada',
        'model_year' => 2023,
        'fuel_type' => 'ETHANOL',
        'tank_capacity' => 55,
        'status' => 'UNAVAILABLE',
        'secretariat_id' => $newSecretariat->id,
    ]);
});

test('atualiza apenas o campo enviado, mantendo os demais inalterados', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle([
        'name' => 'veiculo antigo',
        'plate' => 'ABC1D23',
        'brand' => 'Chevrolet',
        'model' => 'Onix',
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->patchJson('/api/v1/vehicles/'.$vehicle->id, [
            'model' => 'Prisma',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'name' => 'veiculo antigo',
            'plate' => 'ABC1D23',
            'brand' => 'Chevrolet',
            'model' => 'Prisma',
        ],
    ]);

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'name' => 'veiculo antigo',
        'plate' => 'ABC1D23',
        'brand' => 'Chevrolet',
        'model' => 'Prisma',
    ]);
});

test('normaliza nome, placa, chassi, combustível e status ao atualizar', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, [
            'name' => '  Veiculo   NOVO ',
            'plate' => 'efg4h56',
            'chassi' => '9bwzzz377vt004251',
            'fuel_type' => 'diesel',
            'status' => 'unavailable',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'name' => 'veiculo novo',
        'plate' => 'EFG4H56',
        'chassi' => '9BWZZZ377VT004251',
        'fuel_type' => 'DIESEL',
        'status' => 'UNAVAILABLE',
    ]);
});

test('permite atualizar veículo com o mesmo nome de outro veículo', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'veiculo a']);
    $vehicle = createVehicle(['name' => 'veiculo b']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, ['name' => 'veiculo a']);

    $response->assertStatus(200);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'name' => 'veiculo a']);
});

test('retorna 404 ao atualizar veículo excluído', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle(['brand' => 'Chevrolet']);
    $vehicle->delete();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, ['brand' => 'Fiat']);

    $response->assertStatus(404);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Chevrolet']);
});

test('permite manter a própria placa, renavam, chassi e nome ao atualizar', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle([
        'name' => 'veiculo a',
        'plate' => 'ABC1D23',
        'renavam' => '01234567890',
        'chassi' => '9BWZZZ377VT004251',
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, [
            'name' => 'veiculo a',
            'plate' => 'ABC1D23',
            'renavam' => '01234567890',
            'chassi' => '9BWZZZ377VT004251',
            'brand' => 'Fiat',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Fiat']);
});

test('retorna mensagem e não altera o veículo quando nenhum dado é enviado', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle(['name' => 'veiculo a', 'brand' => 'Chevrolet']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, []);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Nenhum dado foi enviado.',
        'data' => null,
    ]);

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'name' => 'veiculo a',
        'brand' => 'Chevrolet',
    ]);
});

test('não atualiza veículo com valores inválidos', function (string $field, mixed $value) {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle(['name' => 'veiculo a']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, [$field => $value]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor($field, 'data');

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'name' => 'veiculo a']);
})->with([
    'nome com menos de 3 caracteres' => ['name', 'ab'],
    'modelo com menos de 3 caracteres' => ['model', 'ab'],
    'placa com formato inválido' => ['plate', 'ABC12'],
    'renavam com menos de 11 dígitos' => ['renavam', '123456789'],
    'renavam não numérico' => ['renavam', 'abcdefghijk'],
    'chassi com 16 caracteres' => ['chassi', '9BWZZZ377VT00425'],
    'ano do modelo anterior a 1950' => ['model_year', 1949],
    'ano do modelo no futuro' => ['model_year', 9999],
    'combustível inexistente' => ['fuel_type', 'FLEX'],
    'capacidade do tanque zerada' => ['tank_capacity', 0],
    'capacidade do tanque acima de 200' => ['tank_capacity', 201],
    'status inexistente' => ['status', 'BROKEN'],
    'secretaria inexistente' => ['secretariat_id', 99999],
]);

test('não atualiza veículo com placa, renavam ou chassi de outro veículo', function (string $field, string $value) {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    createVehicle([
        'name' => 'veiculo a',
        'plate' => 'ABC1D23',
        'renavam' => '01234567890',
        'chassi' => '9BWZZZ377VT004251',
    ]);
    $vehicle = createVehicle(['name' => 'veiculo b']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, [$field => $value]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor($field, 'data');

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'name' => 'veiculo b']);
})->with([
    'placa de outro veículo' => ['plate', 'ABC1D23'],
    'renavam de outro veículo' => ['renavam', '01234567890'],
    'chassi de outro veículo' => ['chassi', '9BWZZZ377VT004251'],
]);

test('retorna a mensagem de placa já utilizada ao atualizar com a placa de outro veículo', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['plate' => 'ABC1D23']);
    $vehicle = createVehicle();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, ['plate' => 'ABC1D23']);

    $response->assertStatus(422);
    $response->assertJsonPath('data.plate.0', 'A placa já está sendo utilizada.');
});

test('retorna 404 ao atualizar veículo inexistente', function () {
    $user = getUserWithPermission(createUser(), 'update', 'vehicles');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/99999', ['brand' => 'Fiat']);

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não permite atualizar veículo sem autenticação', function () {
    $vehicle = createVehicle(['brand' => 'Chevrolet']);

    $response = $this->putJson('/api/v1/vehicles/'.$vehicle->id, ['brand' => 'Fiat']);

    $response->assertStatus(401);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Chevrolet']);
});

test('não permite atualizar veículo sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $vehicle = createVehicle(['brand' => 'Chevrolet']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/vehicles/'.$vehicle->id, ['brand' => 'Fiat']);

    $response->assertStatus(403);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Chevrolet']);
});
