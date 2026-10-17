<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\ListDriversRequest;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;
use App\Models\Driver;
use App\Models\User;
use App\Support\ApiResponder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class DriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    public function index(ListDriversRequest $request): JsonResponse
    {
        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $drivers = Driver::withUser()
            ->when($request->filled('search'), fn ($q) => $q->where('users.name', 'ilike', "%{$search}%"))
            ->orderBy('users.'.$sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($drivers) === 0) {
            return ApiResponder::success(message: 'Nenhum motorista encontrado.');
        }

        return ApiResponder::success(
            $drivers->toResourceCollection()
        );
    }

    /**
     * Store a newly created driver in storage.
     */
    public function store(StoreDriverRequest $request): JsonResponse
    {
        try {
            $driver = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'profile_id' => $request->profile_id,
                    'secretariat_id' => $request->secretariat_id,
                ]);

                return Driver::create([
                    'cpf' => $request->cpf,
                    'cnh' => $request->cnh,
                    'cnh_expiration_date' => $request->cnh_expiration_date,
                    'cnh_category' => $request->cnh_category,
                    'phone_number' => $request->phone_number,
                    'status' => $request->status,
                    'user_id' => $user->id
                ]);
            });
        }
        catch (\Exception) {
            return ApiResponder::error(
                'Ocorreu um erro ao cadastrar motorista.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponder::success(
            $driver->toResource(),
            'Motorista cadastrado com sucesso!',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified driver.
     */
    public function show(Driver $driver)
    {
        Gate::authorize('view', $driver);

        return ApiResponder::success(
            $driver->toResource()
        );
    }

    /**
     * Update the specified driver in storage.
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        try {
            $updated = DB::transaction(function () use ($request, $driver) {
                $updatedUser = $driver->user->update($request->only([
                    'name', 'email'
                ]));

                $updatedDriver = $driver->update($request->only([
                    'cpf', 'cnh', 'cnh_expiration_date', 'cnh_category', 'phone_number', 'status'
                ]));

                return $updatedDriver || $updatedUser;
            });
        }
        catch(\Exception) {
            return ApiResponder::error('Erro ao atualizar motorista');
        }

        return $updated ?
            ApiResponder::success(
                $driver->toResource(),
                'Dados atualizados com sucesso',
                Response::HTTP_OK
            )
            :
            ApiResponder::error(
                message: 'Motorista não foi atualizado',
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
    }

    /**
     * Remove the specified driver from storage.
     */
    public function destroy(Driver $driver)
    {
        Gate::authorize('delete', $driver);

        $driver->delete();

        return ApiResponder::success(message: "Motorista removido com sucesso");
    }
}
