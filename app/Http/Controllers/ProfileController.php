<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ListProfilesRequest;
use App\Http\Requests\Profile\StoreProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Profile;
use App\Support\ApiResponder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Exception;

class ProfileController extends Controller
{
    /**
     * Listar todos os perfis.
     */
    public function index(ListProfilesRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        $search = $request->validated('search');
        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $profiles = Profile::query()
            ->when($request->filled('search'), fn (Builder $q) => $q->whereLike('name', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($profiles) === 0) {
            return ApiResponder::error('Nenhum profile encontrado para esta pesquisa.');
        }

        return ApiResponder::success(
            $profiles->toResourceCollection(),
            'Lista de todos os perfis de acesso'
        );
    }

    /**
     * Criar um novo perfil.
     */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        Gate::authorize('create', Profile::class);

        try {
            $newProfile = Profile::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            $newProfile->permissions()->attach($request->input('permissions'));

            $newProfile->save();
        } catch (Exception $e) {
            return ApiResponder::error();
        }

        return ApiResponder::success($newProfile, 'Perfil criado com sucesso');
    }

    /**
     * Mostrar um perfil.
     */
    public function show(Profile $profile): JsonResponse
    {
        Gate::authorize('view', $profile);

        return ApiResponder::success($profile);
    }

    /**
     * Atualizar um perfil.
     */
    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        Gate::authorize('update', $profile);

        $profile->permissions()->sync($request->input('permissions'));

        $status = $profile->update($request->only(['name', 'description']));

        return $status ?
            ApiResponder::success(message: 'Dados atualizados com sucesso') : ApiResponder::error();
    }

    /**
     * Remover um perfil.
     */
    public function destroy(Profile $profile): JsonResponse
    {
        Gate::authorize('delete', $profile);

        try {
            $profile->deleteOrFail();
        } catch (Exception $e) {
            return ApiResponder::error();
        }

        return ApiResponder::success(message: 'Perfil removido com sucesso');
    }
}
