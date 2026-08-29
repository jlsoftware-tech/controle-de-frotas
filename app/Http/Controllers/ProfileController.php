<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Exception;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $profiles = Profile::query()
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                return $q->whereLike('name', "%{$request->input('search')}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lista de todos os perfis de acesso',
            'data' => $profiles->toResourceCollection()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'unique:profiles', 'max:50'],
            'description' => ['max:255']
        ]);

        try {
            $newProfile = Profile::create([
                'name' => $request->input('name'),
                'description' => $request->input('description')
            ]);

            $newProfile->save();
        }
        catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: perfil não foi criado',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil criado com sucesso',
            'data' => [ $newProfile ]
        ]);
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
    public function update(Request $request, Profile $profile): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'unique:profiles', 'max:50'],
            'description' => ['max:255']
        ]);

        try {
            $profile->update($request->toArray());
        }
        catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: perfil não foi atualizado',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado',
            'data' => [ $profile ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile): JsonResponse
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
