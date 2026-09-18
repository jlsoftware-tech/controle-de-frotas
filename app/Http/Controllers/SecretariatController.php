<?php

namespace App\Http\Controllers;

use App\Http\Requests\Secretariat\ListSecretariatRequest;
use App\Http\Requests\Secretariat\StoreSecretariatRequest;
use App\Http\Requests\Secretariat\UpdateSecretariatRequest;
use App\Http\Resources\SecretariatCollection;
use App\Models\Secretariat;
use App\Support\ApiResponder;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SecretariatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListSecretariatRequest $request)
    {
        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $secretariats = Secretariat::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($secretariats) === 0) {
            return ApiResponder::success(message: 'Nenhuma secretaria encontrada para esta pesquisa.');
        }

        return new SecretariatCollection($secretariats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSecretariatRequest $request): JsonResponse
    {
        try {
            $secretariat = Secretariat::create([
                'name' => $request->name,
                'acronym' => $request->acronym,
            ]);
        } catch (Exception $e) {
            return ApiResponder::error(
                'Ocorreu um erro ao cadastrar a secretaria. Por favor, tente novamente.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return ApiResponder::success(
            $secretariat->toResource(),
            'Secretaria cadastrada com sucesso!',
            Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Secretariat $secretariat)
    {
        return ApiResponder::success(
            $secretariat->toResource(),
            '',
            Response::HTTP_OK,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSecretariatRequest $request, Secretariat $secretariat)
    {
        $status = $secretariat->update($request->all());

        return $status
            ? ApiResponder::success(
                $secretariat->toResource(),
                'Dados atualizados com sucesso',
                Response::HTTP_OK,
            )
            : ApiResponder::error(
                'Ocorreu um erro ao atualizar os dados.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Secretariat $secretariat)
    {
        $secretariat->delete();

        return ApiResponder::success(message: 'Secretaria removida com sucesso.');
    }
}
