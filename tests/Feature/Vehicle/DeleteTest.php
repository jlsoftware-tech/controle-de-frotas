<?php

test('excluir veículo estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Veículo removido com sucesso!',
        'data' => null,
    ]);

    $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
});

test('retorna 404 ao excluir veículo já excluído', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'vehicles');
    $token = authenticateUser($user);

    $vehicle = createVehicle();
    $vehicle->delete();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(404);
});

test('retorna 404 ao excluir veículo inexistente', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'vehicles');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/vehicles/99999');

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não permite excluir veículo sem autenticação', function () {
    $vehicle = createVehicle();

    $response = $this->deleteJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(401);

    $this->assertNotSoftDeleted('vehicles', ['id' => $vehicle->id]);
});

test('não permite excluir veículo sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $vehicle = createVehicle();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/vehicles/'.$vehicle->id);

    $response->assertStatus(403);

    $this->assertNotSoftDeleted('vehicles', ['id' => $vehicle->id]);
});
