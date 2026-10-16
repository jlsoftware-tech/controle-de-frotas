<?php

use App\Models\Profile;
use App\Models\Secretariat;
use Symfony\Component\HttpFoundation\Response;

function getFieldsForDriverCreation($fields = [])
{
    $data = [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => $pass = fake()->password(8),
        'password_confirmation' => $pass,
        'cpf' => fake()->cpf(false),
        'cnh' => fake()->unique()->numerify('###########'),
        'cnh_category' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE']),
        'cnh_expiration_date' => fake()
            ->dateTimeBetween('tomorrow', '+1 year')
            ->format('Y-m-d'),
        'phone_number' => fake()->e164PhoneNumber(),
        'profile_id' => Profile::create(['name' => 'test', 'description' => 'test'])->id,
        'secretariat_id' => Secretariat::create(['name' => 'test', 'acronym' => 'test'])->id,
    ];

    if (! empty($fields)) {
        foreach ($fields as $key => $value) {
            $data[$key] = $value;
        }
    }

    return $data;
}

test('cria motorista com dados válidos estando autenticado', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $fields = getFieldsForDriverCreation();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', $fields);

    $response->assertStatus(Response::HTTP_CREATED);
    $response->assertJsonStructure([
        'success',
        'status_code',
        'data' => [
            'id', 'cpf', 'status', 'name', 'email', 'cnh', 'cnh_category', 'cnh_expiration_date', 'phone_number',
            'profile' => ['id', 'name'],
            'secretariat' => ['id', 'name', 'acronym'],
            'created_at', 'updated_at', 'deleted_at'
        ]
    ]);

    $response->assertJson([
        'success' => true,
        'status_code' => Response::HTTP_CREATED,
        'message' => 'Motorista cadastrado com sucesso!',
        'data' => [
            'name' => $fields['name'],
            'email' => $fields['email'],
            'cpf' => $fields['cpf'],
            'cnh' => $fields['cnh'],
            'cnh_category' => $fields['cnh_category'],
            'cnh_expiration_date' => $fields['cnh_expiration_date'],
            'phone_number' => $fields['phone_number']
        ],
    ]);
});

test('não cria motorista sem dados obrigatórios', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', []);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com cpf inválido', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation([
            'cpf' => '123a'
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com cpf repetido', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response1 = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation());

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation([
            'cpf' => $response1['data']['cpf']
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com cnh inválida', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation([
            'cnh' => '123a'
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com cnh repetida', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response1 = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation());

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation([
            'cnh' => $response1['data']['cnh']
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com cnh vencida', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation([
            'cnh_expiration_date' => now()->subDay()
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista com telefone inválido', function () {
    $user = getUserWithPermission(createUser(), 'create', 'drivers');
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation([
            'phone_number' => '1234'
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('não cria motorista sem autenticação', function () {
    $response = $this->postJson('/api/v1/drivers', getFieldsForDriverCreation());

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('não cria motorista sem autorização', function () {
    $user = createUser();
    $token = authenticateUser($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/drivers', getFieldsForDriverCreation());

    $response->assertStatus(Response::HTTP_FORBIDDEN);
})->skip();
