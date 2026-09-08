<?php

use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;

test('logout', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'senha123',
        'profile_id' => Profile::create(['name' => 'test', 'description' => 'test'])->id,
        'secretariat_id' => Secretariat::create(['name' => 'test', 'acronym' => 'test'])->id,
    ]);

    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/v1/auth/logout');

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
        'message' => 'Logout realizado com sucesso.',
        'data' => null,
    ]);

});
