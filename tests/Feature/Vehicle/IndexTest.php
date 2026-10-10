<?php

test('listar veículos estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'veiculo a']);
    createVehicle(['name' => 'veiculo b']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'data' => [
            'items' => [
                '*' => [
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
            ],
            'pagination' => ['numPerPage', 'currPage', 'totalEntries', 'totalPages'],
        ],
    ]);
    $response->assertJsonPath('data.pagination.totalEntries', 2);
});

test('lista os veículos ordenados por nome de forma crescente por padrão', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'veiculo c']);
    createVehicle(['name' => 'veiculo a']);
    createVehicle(['name' => 'veiculo b']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles');

    $response->assertStatus(200);
    expect(collect($response->json('data.items'))->pluck('name')->all())
        ->toBe(['veiculo a', 'veiculo b', 'veiculo c']);
});

test('lista os veículos ordenados conforme sort e order informados', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'veiculo a', 'model_year' => 2018]);
    createVehicle(['name' => 'veiculo b', 'model_year' => 2022]);
    createVehicle(['name' => 'veiculo c', 'model_year' => 2020]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles?sort=model_year&order=desc');

    $response->assertStatus(200);
    expect(collect($response->json('data.items'))->pluck('model_year')->all())
        ->toBe([2022, 2020, 2018]);
});

test('pagina os veículos conforme page e per_page informados', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'veiculo a']);
    createVehicle(['name' => 'veiculo b']);
    createVehicle(['name' => 'veiculo c']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles?per_page=2&page=2');

    $response->assertStatus(200);
    $response->assertJsonPath('data.pagination.numPerPage', 2);
    $response->assertJsonPath('data.pagination.currPage', 2);
    $response->assertJsonPath('data.pagination.totalEntries', 3);
    $response->assertJsonPath('data.pagination.totalPages', 2);
    expect(collect($response->json('data.items'))->pluck('name')->all())->toBe(['veiculo c']);
});

test('filtra os veículos pelo nome com o termo de busca', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'chevrolet onix']);
    createVehicle(['name' => 'fiat uno']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles?search=onix');

    $response->assertStatus(200);
    $response->assertJsonPath('data.pagination.totalEntries', 1);
    expect(collect($response->json('data.items'))->pluck('name')->all())->toBe(['chevrolet onix']);
});

test('não lista veículos excluídos', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'veiculo ativo']);
    createVehicle(['name' => 'veiculo excluido'])->delete();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles');

    $response->assertStatus(200);
    $response->assertJsonPath('data.pagination.totalEntries', 1);
    expect(collect($response->json('data.items'))->pluck('name')->all())->toBe(['veiculo ativo']);
});

test('retorna mensagem quando nenhum veículo foi cadastrado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Nenhum veículo foi cadastrado ainda.',
        'data' => null,
    ]);
});

test('retorna mensagem quando a busca não encontra veículos', function () {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    createVehicle(['name' => 'chevrolet onix']);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles?search=inexistente');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Nenhum veículo encontrado para essa pesquisa.',
        'data' => null,
    ]);
});

test('não lista veículos com parâmetros de consulta inválidos', function (string $query, string $field) {
    $user = getUserWithPermission(createUser(), 'view', 'vehicles');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles?'.$query);

    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor($field, 'data');
})->with([
    'sort fora das colunas permitidas' => ['sort=renavam', 'sort'],
    'order diferente de asc e desc' => ['order=sideways', 'order'],
    'per_page acima de 100' => ['per_page=101', 'per_page'],
    'per_page menor que 1' => ['per_page=0', 'per_page'],
    'page menor que 1' => ['page=0', 'page'],
]);

test('não permite listar veículos sem autenticação', function () {
    $response = $this->getJson('/api/v1/vehicles');

    $response->assertStatus(401);
});

test('não permite listar veículos sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/vehicles');

    $response->assertStatus(403);
});
