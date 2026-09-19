<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permissions\ListPermissionsRequest;
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
    public function index(ListPermissionsRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $permissions = Permission::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($permissions) === 0) {
            return ApiResponder::success(message: 'Nenhuma permissão encontrada para esta pesquisa.');
        }

        return ApiResponder::success(
            $permissions->toResourceCollection(),
            'Lista de todas as permissões'
        );
    }

    /**
     * Mostrar uma permissão.
     */
    public function show(string $id): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        try {
            $permission = Permission::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => true,
                'message' => 'Recurso não encontrado.',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $permission,
        ]);
    }
}
