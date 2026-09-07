<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    // trait para tratamento dos erros de validação
    use ApiResponse;


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
                'password' => Hash::make($request->password),
                'profile_id' => $request->profile_id,
                'secretariat_id' => $request->secretariat_id,
            ]);
        } catch (Exception) {
            return $this->error(
                'Ocorreu um erro ao cadastrar o usuário. Por favor, tente novamente.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->success(
            $user->toResource(),
            'Usuário cadastrado com sucesso!',
            Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->success(
            $user->toResource(),
            '',
            Response::HTTP_OK,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $status = $user->update($request->all());

        return $status ?
            $this->success(
                $user->toResource(),
                'Dados atualizados com sucesso.',
                Response::HTTP_OK,
            ):
            $this->error(
                'Ocorreu um erro ao atualizar os dados.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->success(
            message: 'Usuário removido com sucesso.',
            statusCode: Response::HTTP_OK,
        );
    }
}
