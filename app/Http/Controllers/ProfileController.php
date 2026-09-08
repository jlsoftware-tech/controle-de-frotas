<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Exception;

class ProfileController extends Controller
{
    /**
     * Listar todos os perfis.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        $profiles = Profile::query()
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                return $q->whereLike('name', "%{$request->input('search')}%");
            })
            ->orderBy($request->input('sort', 'name'), $request->input('order', 'desc'))
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Lista de todos os perfis de acesso',
            'data' => ProfileResource::collection($profiles),
        ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao cadastrar o perfil. Por favor, tente novamente.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil cadastrado com sucesso!',
            'data' => [$newProfile],
        ]);
    }

    /**
     * Mostrar um perfil.
     */
    public function show(Profile $profile): JsonResponse
    {
        Gate::authorize('view', $profile);

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
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
            response()->json([
                'success' => true,
                'message' => 'Dados atualizados com sucesso',
                'data' => $profile,
            ]) :
            response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao atualizar os dados.',
                'data' => null,
            ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao deletar perfil! Tente novamente.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil removido com sucesso.',
            'data' => null,
        ]);
    }
}
