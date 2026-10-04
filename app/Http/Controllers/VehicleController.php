<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\ListVehicleRequest;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListVehicleRequest $request)
    {
//        Gate::authorize('viewAny', Vehicle::class);

        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $vehicles = Vehicle::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($vehicles) === 0) {
            return ApiResponder::error('Nenhum veículo encontrado para essa pesquisa.');
        }

        return ApiResponder::success($vehicles->toResourceCollection());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request)
    {
//        Gate::authorize('create', Vehicle::class);

        $created = Vehicle::create($request->validated());

        return ApiResponder::success($created);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
//        Gate::authorize('view', $vehicle);

        return ApiResponder::success($vehicle->toResource());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);

        $updated = $vehicle->update($request->validated());

        return $updated
            ? ApiResponder::success($vehicle->toResource(), 'Dados atualizados com sucesso!')
            : ApiResponder::error(
                'Ocorreu um erro ao atualizar os dados.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
//        Gate::authorize('delete', $vehicle);

        $vehicle->delete();
        return ApiResponder::success(message: 'Veículo removido com sucesso!');
    }
}
