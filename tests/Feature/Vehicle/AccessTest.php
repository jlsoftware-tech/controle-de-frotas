<?php

use App\Models\Vehicle;

/**
 * Cada endpoint de veículo: método HTTP, ação exigida e se a rota recebe o id do veículo.
 */
dataset('endpoints de veículo', [
    'listar exige view' => ['GET', 'view', false],
    'cadastrar exige create' => ['POST', 'create', false],
    'exibir exige view' => ['GET', 'view', true],
    'atualizar exige update' => ['PUT', 'update', true],
    'excluir exige delete' => ['DELETE', 'delete', true],
]);

/**
 * Envia a requisição ao endpoint com um payload válido, para que a validação não responda antes da autorização.
 */
function requestVehicleEndpoint($test, string $token, string $method, bool $usesVehicleId, Vehicle $vehicle)
{
    $uri = '/api/v1/vehicles'.($usesVehicleId ? '/'.$vehicle->id : '');
    $payload = match ($method) {
        'POST' => validVehiclePayload($vehicle->secretariat_id),
        'PUT' => ['brand' => 'Fiat'],
        default => [],
    };

    return $test->withHeaders(['Authorization' => 'Bearer '.$token])->json($method, $uri, $payload);
}

test('retorna 403 quando o usuário tem todas as outras permissões de veículos, menos a exigida pelo endpoint', function (string $method, string $requiredAction, bool $usesVehicleId) {
    $user = createUser();
    foreach (array_diff(['view', 'create', 'update', 'delete'], [$requiredAction]) as $otherAction) {
        getUserWithPermission($user, $otherAction, 'vehicles');
    }
    $token = authenticateUser($user);

    $vehicle = createVehicle(['brand' => 'Chevrolet']);

    $response = requestVehicleEndpoint($this, $token, $method, $usesVehicleId, $vehicle);

    $response->assertStatus(403);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Chevrolet', 'deleted_at' => null]);
    expect(Vehicle::count())->toBe(1);
})->with('endpoints de veículo');

test('retorna 403 quando o usuário tem todas as permissões de outros módulos, mas nenhuma de veículos', function (string $method, string $requiredAction, bool $usesVehicleId) {
    $user = createUser();
    foreach (['users', 'profiles', 'secretariats'] as $module) {
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            getUserWithPermission($user, $action, $module);
        }
    }
    $token = authenticateUser($user);

    $vehicle = createVehicle(['brand' => 'Chevrolet']);

    $response = requestVehicleEndpoint($this, $token, $method, $usesVehicleId, $vehicle);

    $response->assertStatus(403);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'brand' => 'Chevrolet', 'deleted_at' => null]);
    expect(Vehicle::count())->toBe(1);
})->with('endpoints de veículo');
