<?php

use App\Models\Secretariat;

test('exibir os dados de uma secretaria específica estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'secretariats');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/secretariats/'.$secretariat->id);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => ['id', 'name', 'acronym', 'created_at', 'updated_at', 'deleted_at'],
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => '',
        'data' => [
            'id' => $secretariat->id,
            'name' => $secretariat->name,
            'acronym' => $secretariat->acronym,
        ],
    ]);
});

test('retorna 404 ao exibir secretaria inexistente', function () {
    $user = getUserWithPermission(createUser(), 'view', 'secretariats');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/secretariats/99999');

    $response->assertStatus(404);

    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não permite exibir secretaria sem autenticação', function () {
    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->getJson('/api/v1/secretariats/'.$secretariat->id);

    $response->assertStatus(401);
});

test('não permite visualizar secretarias sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria de Teste', 'acronym' => 'ST']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/secretariats/'.$secretariat->id);

    $response->assertStatus(403);
});
