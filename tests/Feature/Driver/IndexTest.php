<?php

use Symfony\Component\HttpFoundation\Response;

test('listar secretarias estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'drivers');
    $token = authenticateUser($user);

    createValidDriver();
    createValidDriver();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/drivers');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'data' => [
            'items' => [
                '*' => ['id', 'cpf', 'status', 'name', 'email', 'cnh', 'cnh_category', 'cnh_expiration_date', 'phone_number',
                    'profile' => ['id', 'name'],
                    'secretariat' => ['id', 'name', 'acronym'],
                    'created_at', 'updated_at', 'deleted_at'],
            ],
            'pagination' => ['numPerPage', 'currPage', 'totalEntries', 'totalPages'],
        ],
    ]);
});

test('não permite listar motoristas sem autenticação', function () {
    $response = $this->getJson('/api/v1/drivers');

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('não permite listar motoristas sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/drivers');

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});
