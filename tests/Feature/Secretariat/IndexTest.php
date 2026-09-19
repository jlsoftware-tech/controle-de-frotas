<?php

use App\Models\Secretariat;

test('listar secretarias estando autenticado', function () {
    $user = createUser();
    $token = authenticateUser($user);

    Secretariat::create(['name' => 'Secretaria A', 'acronym' => 'SA']);
    Secretariat::create(['name' => 'Secretaria B', 'acronym' => 'SB']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/secretariats');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'data' => [
            'items' => [
                '*' => ['id', 'name', 'acronym', 'created_at', 'updated_at', 'deleted_at'],
            ],
            'pagination' => ['numPerPage', 'currPage', 'totalEntries', 'totalPages'],
        ],
    ]);
});

test('não permite listar secretarias sem autenticação', function () {
    $response = $this->getJson('/api/v1/secretariats');

    $response->assertStatus(401);
});
