<?php

use Symfony\Component\HttpFoundation\Response;

test('excluir motorista estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'drivers');
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(Response::HTTP_OK);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Motorista removido com sucesso',
        'data' => null,
    ]);

    $this->assertSoftDeleted('drivers', ['id' => $driver->id]);
});

test('retorna 404 ao excluir motorista inexistente', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/secretariats/99999');

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('não permite excluir motorista sem autenticação', function () {
    $driver = createValidDriver();

    $response = $this->deleteJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $this->assertDatabaseHas('drivers', [
        'id' => $driver->id,
        'deleted_at' => null,
    ]);
});

test('não permite excluir motorista sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $driver = createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});
