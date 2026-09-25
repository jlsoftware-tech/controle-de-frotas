<?php

use App\Models\Secretariat;

test('excluir secretaria estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'secretariats');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/secretariats/'.$secretariat->id);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Secretaria removida com sucesso.',
        'data' => null,
    ]);

    $this->assertSoftDeleted('secretariats', ['id' => $secretariat->id]);
});

test('retorna 404 ao excluir secretaria inexistente', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'secretariats');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/secretariats/99999');

    $response->assertStatus(404);
});

test('não permite excluir secretaria sem autenticação', function () {
    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->deleteJson('/api/v1/secretariats/'.$secretariat->id);

    $response->assertStatus(401);

    $this->assertDatabaseHas('secretariats', [
        'id' => $secretariat->id,
        'deleted_at' => null,
    ]);
});

test('não permite excluir secretaria sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/secretariats/'.$secretariat->id);

    $response->assertStatus(403);
});
