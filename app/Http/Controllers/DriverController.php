<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\ListDriversRequest;
use App\Models\Driver;
use App\Support\ApiResponder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $drivers = Driver::query()
            ->with('user')
            ->whereHas('user', function (Builder $query) use ($request, $search, $sort, $order) {
                return $query
                    ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orderBy($sort, $order);
            })
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified driver.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified driver in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified driver from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
