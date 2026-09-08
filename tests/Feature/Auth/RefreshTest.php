<?php

use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;

test('refresh token', function () {
    $user = createUser();

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/v1/auth/refresh');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [
            'token',
            'user'
        ],
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Sucesso.',
        'data' => [],
    ]);
});
