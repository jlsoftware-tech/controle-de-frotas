<?php

use App\Models\Secretariat;

test('atualiza secretaria com dados válidos estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'update', 'secretariats');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/secretariats/'.$secretariat->id, [
            'name' => 'Secretaria Alterada',
            'acronym' => 'ALT',
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Dados atualizados com sucesso',
        'data' => [
            'name' => 'Secretaria Alterada',
            'acronym' => 'ALT',
        ],
    ]);

    $this->assertDatabaseHas('secretariats', [
        'id' => $secretariat->id,
        'name' => 'Secretaria Alterada',
        'acronym' => 'ALT',
    ]);
});

test('atualiza apenas o campo enviado, mantendo os demais inalterados', function () {
    $user = getUserWithPermission(createUser(), 'update', 'secretariats');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->patchJson('/api/v1/secretariats/'.$secretariat->id, [
            'name' => 'Secretaria Alterada',
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'data' => [
            'name' => 'Secretaria Alterada',
            'acronym' => 'SA',
        ],
    ]);

    $this->assertDatabaseHas('secretariats', [
        'id' => $secretariat->id,
        'name' => 'Secretaria Alterada',
        'acronym' => 'SA',
    ]);
});

test('não atualiza secretaria inexistente', function () {
    $user = getUserWithPermission(createUser(), 'update', 'secretariats');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/secretariats/99999', [
            'name' => 'Secretaria Alterada',
        ]);

    $response->assertStatus(404);

    $response->assertJson([
        'success' => false,
        'status_code' => 404,
        'message' => 'Recurso não encontrado.',
        'data' => null,
    ]);
});

test('não atualiza secretaria sem autenticação', function () {
    $secretariat = Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);

    $response = $this->putJson('/api/v1/secretariats/'.$secretariat->id, [
        'name' => 'Secretaria Alterada',
    ]);

    $response->assertStatus(401);

    $this->assertDatabaseHas('secretariats', [
        'id' => $secretariat->id,
        'name' => 'Secretaria A',
    ]);
});

test('não atualiza secretaria com nome maior que o permitido', function () {
    $user = getUserWithPermission(createUser(), 'update', 'secretariats');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->patchJson('/api/v1/secretariats/'.$secretariat->id, [
            'name' => str_repeat('a', 256),
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('name', 'data');
});

test('não atualiza secretaria com sigla maior que o permitido', function () {
    $user = getUserWithPermission(createUser(), 'update', 'secretariats');
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->patchJson('/api/v1/secretariats/'.$secretariat->id, [
            'acronym' => str_repeat('a', 17),
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('acronym', 'data');
});

test('não permite atualizar secretarias sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $secretariat = Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->putJson('/api/v1/secretariats/'.$secretariat->id, [
            'name' => 'Secretaria Alterada',
            'acronym' => 'ALT',
        ]);

    $response->assertStatus(403);
});
