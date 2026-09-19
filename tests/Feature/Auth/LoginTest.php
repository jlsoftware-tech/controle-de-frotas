<?php

test('login', function () {
    $user = createUser();

    $response = $this->postJson('/api/v1/auth/login',
        [
            'email' => $user->email,
            'password' => 'senha123',
            'remember' => true
        ]);

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            'token',
            'user',
        ],
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Login realizado com sucesso.',
        'data' => [],
    ]);

});
