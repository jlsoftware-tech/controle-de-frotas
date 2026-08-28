<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileCollection;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SebastianBergmann\Diff\Exception;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de todos os perfis de acesso',
            'data' => Profile::all()->toResourceCollection()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Perfil encontrado',
            'data' => [ $profile ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        try {
            $profile->deleteOrFail();
        }
        catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar perfil',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil deletado',
            'data' => null
        ]);
    }
}
