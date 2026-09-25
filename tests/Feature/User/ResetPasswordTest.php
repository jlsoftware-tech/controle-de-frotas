<?php

use Illuminate\Support\Facades\Hash;

test('redefine a senha do usuário autenticado com sucesso', function () {
    $user = createUser(['password' => 'senha123']);
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => 'novaSenhaForte',
            'password_confirmation' => 'novaSenhaForte',
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'status_code' => 200,
        'message' => 'Sua senha foi redefinida com sucesso',
        'data' => null,
    ]);

    $user->refresh();
    expect(Hash::check('novaSenhaForte', $user->password))->toBeTrue();
});

test('não redefine a senha sem autenticação', function () {
    $response = $this->putJson('/api/v1/user/reset-password', [
        'password' => 'novaSenhaForte',
        'password_confirmation' => 'novaSenhaForte',
    ]);

    $response->assertStatus(401);

    $response->assertJson([
        'success' => false,
        'status_code' => 401,
        'data' => null,
    ]);
});

test('não redefine a senha quando o campo password está vazio', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => '',
            'password_confirmation' => '',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('password', 'data');

    expect($response->json('data.password'))->toContain('Informe uma senha');
});

test('não redefine a senha quando a confirmação não confere', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => 'novaSenhaForte',
            'password_confirmation' => 'senhaDiferente',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('password', 'data');
});

test('não redefine a senha quando é menor que 8 caracteres', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('password', 'data');
});

test('não redefine a senha quando a confirmação não é enviada', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => 'novaSenhaForte',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrorFor('password', 'data');
});

test('avisa quando a nova senha é igual à senha atual', function () {
    $user = createUser(['password' => 'senhaAtual123']);
    $token = authenticateUser($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => 'senhaAtual123',
            'password_confirmation' => 'senhaAtual123',
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'success' => true,
        'message' => 'Informe uma senha diferente para redefinir sua senha',
    ]);
});

test('não altera a senha de outro usuário', function () {
    $userLogado = createUser(['password' => 'senha123']);
    $outroUsuario = createUser(['password' => 'senhaOutro123']);
    $token = authenticateUser($userLogado);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/user/reset-password', [
            'password' => 'novaSenhaForte',
            'password_confirmation' => 'novaSenhaForte',
        ]);

    $response->assertStatus(200);

    $outroUsuario->refresh();
    expect(Hash::check('senhaOutro123', $outroUsuario->password))->toBeTrue();
});
