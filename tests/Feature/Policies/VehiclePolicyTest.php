<?php

use App\Models\Vehicle;
use Illuminate\Support\Facades\Gate;

/**
 * Habilidades da policy e a ação de permissão que cada uma exige no módulo de veículos.
 */
dataset('habilidades de veículo', [
    'viewAny exige view' => ['viewAny', 'view'],
    'view exige view' => ['view', 'view'],
    'create exige create' => ['create', 'create'],
    'update exige update' => ['update', 'update'],
    'delete exige delete' => ['delete', 'delete'],
]);

/**
 * viewAny e create recebem a classe; as demais habilidades recebem uma instância do veículo.
 */
function vehiclePolicyTarget(string $ability): Vehicle|string
{
    return in_array($ability, ['viewAny', 'create'], true) ? Vehicle::class : createVehicle();
}

test('permite a habilidade quando o usuário tem a permissão correspondente em veículos', function (string $ability, string $action) {
    $user = getUserWithPermission(createUser(), $action, 'vehicles');

    $allowed = Gate::forUser($user)->allows($ability, vehiclePolicyTarget($ability));

    expect($allowed)->toBeTrue();
})->with('habilidades de veículo');

test('nega a habilidade quando o usuário não tem nenhuma permissão', function (string $ability) {
    $user = createUser();

    $allowed = Gate::forUser($user)->allows($ability, vehiclePolicyTarget($ability));

    expect($allowed)->toBeFalse();
})->with(['viewAny', 'view', 'create', 'update', 'delete']);

test('nega a habilidade quando o usuário tem apenas as outras ações de veículos', function (string $ability, string $action) {
    $user = createUser();
    foreach (array_diff(['view', 'create', 'update', 'delete'], [$action]) as $otherAction) {
        getUserWithPermission($user, $otherAction, 'vehicles');
    }

    $allowed = Gate::forUser($user)->allows($ability, vehiclePolicyTarget($ability));

    expect($allowed)->toBeFalse();
})->with('habilidades de veículo');

test('nega a habilidade quando o usuário tem a mesma ação apenas em outros módulos', function (string $ability, string $action) {
    $user = createUser();
    foreach (['users', 'profiles', 'secretariats'] as $module) {
        getUserWithPermission($user, $action, $module);
    }

    $allowed = Gate::forUser($user)->allows($ability, vehiclePolicyTarget($ability));

    expect($allowed)->toBeFalse();
})->with('habilidades de veículo');

test('nega restaurar e excluir permanentemente mesmo com todas as permissões de veículos', function (string $ability) {
    $user = createUser();
    foreach (['view', 'create', 'update', 'delete'] as $action) {
        getUserWithPermission($user, $action, 'vehicles');
    }

    $denied = Gate::forUser($user)->denies($ability, createVehicle());

    expect($denied)->toBeTrue();
})->with(['restore', 'forceDelete']);
