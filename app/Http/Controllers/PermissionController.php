<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * Listar todas as permissões.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        return response()->json([
            'status' => true,
            'message' => 'Listando todas as permissões',
            'data' => Permission::all()
        ]);
    }

    /**
     * Mostrar uma permissão.
     */
    public function show(string $id): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        try {
            $permission = Permission::findOrFail($id);
        }
        catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => true,
                'message' => 'Recurso não encontrado.',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $permission
        ]);
    }
}
