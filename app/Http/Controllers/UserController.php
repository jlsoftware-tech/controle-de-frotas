<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), fn ($q) =>
                $q->where('name', 'like' , "%{$request->search}%"))
            ->orderBy($request->input('sort', 'name'), $request->input('order', 'desc'))
            ->paginate($request->input('per_page', 10));

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao cadastrar o usuário. Por favor, tente novamente.',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso!',
            'data' => $user->toResource()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->toResource(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $status = $user->update($request->all());

        return $status ?
            response()->json([
                'success' => true,
                'message' => 'Dados atualizados com sucesso',
                'data' => $user->toResource()
            ]) :
            response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao atualizar os dados.',
                'data' => null
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Usuário removido com sucesso!',
            'data' => $user->toResource(),
        ]);
    }
}
