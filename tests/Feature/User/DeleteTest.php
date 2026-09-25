<?php

test('excluir usuário', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'users');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('api/v1/users/'.$user->id);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Usuário removido com sucesso.',
        'data' => null,
    ]);
});

test('retorna 404 ao excluir usuário inexistente', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'users');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('/api/v1/users/99999');

    $response->assertStatus(404);
});

test('não permite excluir usuário sem autenticação', function () {
    $user = getUserWithPermission(createUser(), 'delete', 'users');

    $response = $this->deleteJson('/api/v1/users/'.$user->id);

    $response->assertStatus(401);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data',
    ]);

});

test('não permite excluir usuário sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->deleteJson('api/v1/users/'.$user->id);

    $response->assertStatus(403);
});
