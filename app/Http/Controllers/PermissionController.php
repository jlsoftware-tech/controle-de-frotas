<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Profile;
use App\Support\ApiResponder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * Listar todas as permissões.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        return ApiResponder::success(Permission::all());
    }

    /**
     * Mostrar uma permissão.
     */
    public function show(string $id): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        try {
            $permission = Permission::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return ApiResponder::error('Perfil não encontrado');
        }

        return ApiResponder::success($permission);
    }
}
