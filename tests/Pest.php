<?php

use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;
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
 *
 * @param array|null $attributes
 * @return User
 */
function createUser(array $attributes = null): User
{
    $_attributes = [
        'email' => fake()->unique()->email,
        'password' => 'senha123',
        'profile_id' => Profile::create(['name' => 'test', 'description' => 'test'])->id,
        'secretariat_id' => Secretariat::create(['name' => 'test', 'acronym' => 'test'])->id,
    ];
    if (!is_null($attributes)) {
        foreach ($attributes as $key => $value) {
            $_attributes[$key] = $value;
        }
    }

    return User::factory()->create($_attributes);
}
