<?php

use App\Models\Driver;

test('excluir motorista estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'drivers');
    $token = authenticateUser($user);

    $user = createUser(['name' => 'motorista teste']);
    $driver = Driver::create(['cpf' => '00011122233', 'cnh' => '11111111111', 'cnh_category' => 'AB', 'cnh_expiration_date' => now(), 'phone_number' => '88988888888', 'user_id' => $user->id]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(200);

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

    $response->assertStatus(404);
});

test('não permite excluir motorista sem autenticação', function () {
    $user = createUser(['name' => 'motorista teste']);
    $driver = Driver::create(['cpf' => '00011122233', 'cnh' => '11111111111', 'cnh_category' => 'AB', 'cnh_expiration_date' => now(), 'phone_number' => '88988888888', 'user_id' => $user->id]);

    $response = $this->deleteJson('/api/v1/drivers/'.$driver->id);

    $response->assertStatus(401);

    $this->assertDatabaseHas('drivers', [
        'id' => $driver->id,
        'deleted_at' => null,
    ]);
});

test('não permite excluir motorista sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $user = createUser(['name' => 'motorista teste']);
    $driver = Driver::create(['cpf' => '00011122233', 'cnh' => '11111111111', 'cnh_category' => 'AB', 'cnh_expiration_date' => now(), 'phone_number' => '88988888888', 'user_id' => $user->id]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/secretariats/'.$driver->id);

    $response->assertStatus(403);
})->skip();
