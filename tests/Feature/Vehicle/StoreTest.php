<?php

use App\Models\Secretariat;
use App\Models\Vehicle;

test('cria veículo com dados válidos estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id));

    $response->assertStatus(201);
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
        'status_code' => 201,
        'data' => [
            'name' => 'chevrolet onix 1.0',
            'plate' => 'ABC1D23',
            'renavam' => '01234567890',
            'chassi' => '9BWZZZ377VT004251',
            'brand' => 'Chevrolet',
            'model' => 'Onix',
            'model_year' => 2020,
            'fuel_type' => 'GASOLINE',
            'tank_capacity' => 44,
            'status' => 'AVAILABLE',
            'secretariat_id' => $secretariat->id,
        ],
    ]);

    $this->assertDatabaseHas('vehicles', [
        'name' => 'chevrolet onix 1.0',
        'plate' => 'ABC1D23',
        'renavam' => '01234567890',
        'chassi' => '9BWZZZ377VT004251',
        'brand' => 'Chevrolet',
        'model' => 'Onix',
        'model_year' => 2020,
        'fuel_type' => 'GASOLINE',
        'tank_capacity' => 44,
        'status' => 'AVAILABLE',
        'secretariat_id' => $secretariat->id,
    ]);
});

test('cria veículo apenas com os campos obrigatórios e status disponível por padrão', function () {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', [
            'name' => 'fiat uno',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'model_year' => 2015,
            'secretariat_id' => $secretariat->id,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('vehicles', [
        'name' => 'fiat uno',
        'plate' => null,
        'renavam' => null,
        'chassi' => null,
        'fuel_type' => null,
        'tank_capacity' => null,
        'status' => 'AVAILABLE',
        'secretariat_id' => $secretariat->id,
    ]);
});

test('normaliza nome, placa, chassi, combustível e status antes de salvar', function () {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id, [
            'name' => '  Chevrolet   ONIX  1.0 ',
            'plate' => 'abc1d23',
            'chassi' => '9bwzzz377vt004251',
            'fuel_type' => 'diesel',
            'status' => 'unavailable',
        ]));

    $response->assertStatus(201);

    $this->assertDatabaseHas('vehicles', [
        'name' => 'chevrolet onix 1.0',
        'plate' => 'ABC1D23',
        'chassi' => '9BWZZZ377VT004251',
        'fuel_type' => 'DIESEL',
        'status' => 'UNAVAILABLE',
    ]);
});

test('aceita placa no padrão antigo e no padrão Mercosul', function (string $plate) {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id, ['plate' => $plate]));

    $response->assertStatus(201);

    $this->assertDatabaseHas('vehicles', ['plate' => $plate]);
})->with([
    'padrão antigo ABC1234' => ['ABC1234'],
    'padrão Mercosul ABC1D23' => ['ABC1D23'],
]);

test('não cria veículo sem campos obrigatórios', function () {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', []);

    $response->assertStatus(422);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [],
    ]);
    $response->assertJsonValidationErrorFor('name', 'data');
    $response->assertJsonValidationErrorFor('brand', 'data');
    $response->assertJsonValidationErrorFor('model', 'data');
    $response->assertJsonValidationErrorFor('model_year', 'data');
    $response->assertJsonValidationErrorFor('secretariat_id', 'data');

    expect(Vehicle::count())->toBe(0);
});

test('não cria veículo com valores inválidos', function (string $field, mixed $value) {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id, [$field => $value]));

    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor($field, 'data');

    expect(Vehicle::count())->toBe(0);
})->with([
    'nome com menos de 3 caracteres' => ['name', 'ab'],
    'modelo com menos de 3 caracteres' => ['model', 'ab'],
    'placa com formato inválido' => ['plate', 'ABC12'],
    'placa com caracteres especiais' => ['plate', 'ABC-1234'],
    'renavam com menos de 11 dígitos' => ['renavam', '123456789'],
    'renavam não numérico' => ['renavam', 'abcdefghijk'],
    'chassi com 16 caracteres' => ['chassi', '9BWZZZ377VT00425'],
    'chassi com caractere acentuado' => ['chassi', '9BWZZZ377VT00425Á'],
    'ano do modelo anterior a 1950' => ['model_year', 1949],
    'ano do modelo no futuro' => ['model_year', 9999],
    'combustível inexistente' => ['fuel_type', 'FLEX'],
    'capacidade do tanque zerada' => ['tank_capacity', 0],
    'capacidade do tanque acima de 200' => ['tank_capacity', 201],
    'status inexistente' => ['status', 'BROKEN'],
    'secretaria inexistente' => ['secretariat_id', 99999],
]);

test('retorna a mensagem de placa inválida ao cadastrar veículo com placa fora do formato', function () {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id, ['plate' => 'ABC12']));

    $response->assertStatus(422);
    $response->assertJsonPath('data.plate.0', 'A placa deve estar no formato ABC1234 ou ABC1D23.');
});

test('não cria veículo com placa, renavam ou chassi já cadastrados', function (string $field, string $message) {
    $user = getUserWithPermission(createUser(), 'create', 'vehicles');
    $token = authenticateUser($user);

    $existing = createVehicle([
        'plate' => 'ABC1D23',
        'renavam' => '01234567890',
        'chassi' => '9BWZZZ377VT004251',
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($existing->secretariat_id));

    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor($field, 'data');
    if ($message !== '') {
        $response->assertJsonPath("data.{$field}.0", $message);
    }

    expect(Vehicle::count())->toBe(1);
})->with([
    'placa duplicada' => ['plate', 'A placa já está sendo utilizada.'],
    'renavam duplicado' => ['renavam', ''],
    'chassi duplicado' => ['chassi', ''],
]);

test('não permite cadastrar veículo sem autenticação', function () {
    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id));

    $response->assertStatus(401);

    expect(Vehicle::count())->toBe(0);
});

test('não permite cadastrar veículo sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/vehicles', validVehiclePayload($secretariat->id));

    $response->assertStatus(403);

    expect(Vehicle::count())->toBe(0);
});
