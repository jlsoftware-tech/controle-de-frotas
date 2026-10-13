<?php

use App\Models\Driver;

test('listar secretarias estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'view', 'secretariats');
    $token = authenticateUser($user);

    $user1 = createUser(['name' => 'user 1']);
    $user2 = createUser(['name' => 'user 2']);

    Driver::create(['cpf' => '00011122233', 'cnh' => '11111111111', 'cnh_category' => 'AB', 'cnh_expiration_date' => now(), 'phone_number' => '88988888888', 'user_id' => $user1->id]);
    Driver::create(['cpf' => '00011122234', 'cnh' => '11111111112', 'cnh_category' => 'AB', 'cnh_expiration_date' => now(), 'phone_number' => '88988888888', 'user_id' => $user2->id]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/drivers');

    $response->assertStatus(200);
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

    $response->assertStatus(401);
});
