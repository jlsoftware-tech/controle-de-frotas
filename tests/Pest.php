<?php

use App\Models\Permission;
use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
 * Helpers para os testes
 * */

/**
 * Cria um novo usuário com valores padrão, se não for passado nenhum argumento para a função.
 * Não é obrigatório informar todos os campos do usuário, os campos não informados serão preenchidos automaticamente.
 *
 * email: test@example.com
 *
 * password: senha123
 */
function createUser(?array $attributes = null): User
{
    $_attributes = [
        'email' => fake()->unique()->email,
        'password' => 'senha123',
        'profile_id' => Profile::create(['name' => 'test', 'description' => 'test'])->id,
        'secretariat_id' => Secretariat::create(['name' => 'test', 'acronym' => 'test'])->id,
    ];
    if (! is_null($attributes)) {
        foreach ($attributes as $key => $value) {
            $_attributes[$key] = $value;
        }
    }

    return User::factory()->create($_attributes);
}

function authenticateUser(User $user)
{
    $token = JWTAuth::fromUser($user);
    Auth::guard('api')->setUser($user);

    return $token;
}

function getUserWithPermission($user, $action, $module)
{
    $user->permissions()->attach(Permission::create(['action' => $action, 'module' => $module]));

    return $user;
}

/**
 * Cria um veículo com valores padrão, se não for passado nenhum argumento para a função.
 * Os campos não informados serão preenchidos automaticamente, inclusive a secretaria.
 */
function createVehicle(array $attributes = []): Vehicle
{
    $_attributes = [
        'name' => fake()->unique()->lexify('veiculo ?????'),
        'brand' => 'Chevrolet',
        'model' => 'Onix',
        'model_year' => 2020,
        'secretariat_id' => $attributes['secretariat_id']
            ?? Secretariat::create(['name' => fake()->unique()->lexify('Secretaria ?????'), 'acronym' => 'SEC'])->id,
    ];

    return Vehicle::create(array_merge($_attributes, $attributes));
}

/**
 * Monta um payload válido para cadastro de veículo. Informe apenas os campos que devem ser sobrescritos.
 */
function validVehiclePayload(int $secretariatId, array $overrides = []): array
{
    return array_merge([
        'name' => 'chevrolet onix 1.0',
        'plate' => 'ABC1D23',
        'renavam' => '01234567890',
        'chassi' => '9BWZZZ377VT004251',
        'brand' => 'Chevrolet',
        'model' => 'Onix',
        'model_year' => 2020,
        'fuel_type' => 'GASOLINE',
        'tank_capacity' => 44,
        'status' => 'AVAILABLE',
        'secretariat_id' => $secretariatId,
    ], $overrides);
}
