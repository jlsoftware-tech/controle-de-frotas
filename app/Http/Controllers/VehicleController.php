<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\ListVehicleRequest;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Support\ApiResponder;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\Response;

#[Group('Endpoints de veículo', 'Gerenciamento de recursos.', true)]
class VehicleController extends Controller
{
    #[Endpoint('Listar Veículos', 'Retorna uma lista paginada de veículos cadastrados no sistema com opções de busca, ordenação e paginação.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'items' => [
                    [
                        'name' => 'Veículo 1',
                        'plate' => 'ABC1D23',
                        'renavam' => '01234567890',
                        'chassi' => '9BWZZZ377VT004251',
                        'brand' => 'Mercedes Benz',
                        'model' => 'Mercedes-Benz C 200',
                        'model_year' => '2019',
                        'fuel_type' => 'GASOLINE',
                        'tank_capacity' => '20',
                        'status' => 'ACTIVE',
                        'secretariat_id' => 1,
                        'created_at' => '01/09/2026 10:00:00',
                        'updated_at' => '01/09/2026 10:00:00',
                        'deleted_at' => null,
                    ],
                ],
                'pagination' => [
                    'numPerPage' => 10,
                    'currPage' => 1,
                    'totalEntries' => 1,
                    'totalPages' => 1,
                ],
            ],
        ]
    )]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Nenhum veículo encontrado para essa pesquisa.',
            'data' => null,
        ],
        status: 200,
        description: 'Nenhum veículo encontrado para os critérios informados.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Não autenticado',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'O campo ordenação selecionado é inválido.',
            'errors' => [
                'order' => ['O campo ordenação selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos parâmetros de consulta.'
    )]
    /**
     * Display a listing of the resource.
     */
    public function index(ListVehicleRequest $request)
    {
        Gate::authorize('viewAny', Vehicle::class);

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
            return $search
                ? ApiResponder::success(message: 'Nenhum veículo encontrado para essa pesquisa.')
                : ApiResponder::success(message: 'Nenhum veículo foi cadastrado ainda.');
        }

        return ApiResponder::success($vehicles->toResourceCollection());
    }

    #[Endpoint('Cadastrar Veículo', 'Registra um novo veículo no sistema.')]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 201,
            'message' => 'Cadastro realizado com sucesso.',
            'data' => [
                'name' => 'Veículo 1',
                'plate' => 'ABC1D23',
                'renavam' => '01234567890',
                'chassi' => '9BWZZZ377VT004251',
                'brand' => 'Mercedes Benz',
                'model' => 'Mercedes-Benz C 200',
                'model_year' => '2019',
                'fuel_type' => 'GASOLINE',
                'tank_capacity' => '20',
                'status' => 'ACTIVE',
                'secretariat_id' => 1,
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 201,
        description: 'Veículo cadastrado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Não autenticado',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'name' => ['O campo nome é obrigatório.', ''],
                'plate' => ['A placa deve estar no formato ABC1234 ou ABC1D23.'],
                'renavam' => ['O campo Renavam deve ser um número.', 'O campo renavam deve ter 11 dígitos.'],
                'chassi' => ['O campo chassi deve conter 17 caracteres alfanuméricos. Carácter acentuado também é inválido.'],
                'brand' => ['O campo marca é obrigatório.'],
                'model' => ['O campo modelo é obrigatório.'],
                'model_year' => ['O campo ano do modelo é obrigatório.'],
                'fuel_type' => ['O tipo de combustível selecionado é inválido.'],
                'tank_capacity' => ['O campo capacidade do tanque deve ser um número inteiro.', 'O campo capacidade do tanque deve estar entre 1 e 200.'],
                'status' => ['O status selecionado é inválido.'],
                'secretariat_id' => ['O campo secretaria é obrigatório.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados.'
    )]
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request)
    {
        Gate::authorize('create', Vehicle::class);

        $created = Vehicle::create($request->validated());

        return ApiResponder::success($created->toResource(), statusCode: 201);
    }

    #[Endpoint('Visualizar Veículo', 'Retorna os dados detalhados de um veículo especificado.', true)]
    #[UrlParam('id', 'integer', 'ID do veículo a ser visualizado.', 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'name' => 'Mercedes Benz',
                'plate' => 'ABC1D23',
                'renavam' => '1234567890',
                'chassi' => '9BWZZZ377VT004251',
                'brand' => 'Mercedes Benz',
                'model' => 'Mercedes-Benz C 200',
                'model_year' => '2019',
                'fuel_type' => 'GASOLINE',
                'tank_capacity' => '20',
                'status' => 'ACTIVE',
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Detalhes do veículo recuperados com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Não autenticado',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Veículo não encontrado.'
    )]
    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        Gate::authorize('view', $vehicle);

        return ApiResponder::success($vehicle->toResource());
    }

    #[Endpoint('Atualizar Veículo', 'Atualiza os dados cadastrais de um veículo especificado.', true)]
    #[UrlParam('id', 'integer', 'ID do veículo a ser atualizado.', 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Dados atualizados com sucesso.',
            'data' => [
                'name' => 'Mercedes Benz',
                'plate' => 'EFG4H56',
                'renavam' => '1234567890',
                'chassi' => '9BWZZZ377VT004251',
                'brand' => 'Mercedes Benz',
                'model' => 'Mercedes-Benz C 200',
                'model_year' => '2019',
                'fuel_type' => 'GASOLINE',
                'tank_capacity' => '20',
                'status' => 'ACTIVE',
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '05/09/2026 09:00:00',
                'deleted_at' => null,
            ]
        ],
        status: 200,
        description: 'Dados atualizados com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Nenhum dado foi enviado.',
            'data' => null,
        ],
        status: 200,
        description: 'Envio de payload vazio.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Não autenticado',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Veículo não encontrado.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'name' => ['A placa já está sendo utilizada.'],
                'plate' => ['A placa deve estar no formato ABC1234 ou ABC1D23.', 'A placa já está sendo utilizada.'],
                'renavam' => ['O campo Renavam deve ser um número.', 'O campo renavam deve ter 11 dígitos.'],
                'chassi' => ['O campo chassi deve conter 17 caracteres alfanuméricos. Carácter acentuado também é inválido.'],
                'model_year' => ['O campo ano do modelo deve ser um número inteiro.', 'O campo ano do modelo deve ser pelo menos 1950.'],
                'fuel_type' => ['O tipo de combustível selecionado é inválido.'],
                'tank_capacity' => ['O campo capacidade do tanque deve ser um número inteiro.', 'O campo capacidade do tanque deve estar entre 1 e 200.'],
                'status' => ['O status selecionado é inválido.'],
                'secretariat_id' => ['O campo secretaria deve ser um número inteiro.', 'A secretaria selecionada é inválida.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados.'
    )]
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);

        $update = $request->validated();

        if (empty($update)) {
            return ApiResponder::success(message: 'Nenhum dado foi enviado.');
        }
        $updated = $vehicle->update($update);

        return $updated
            ? ApiResponder::success($vehicle->toResource(), 'Dados atualizados com sucesso!')
            : ApiResponder::error(
                'Ocorreu um erro ao atualizar os dados.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
    }

    #[Endpoint('Excluir Veículo', description: 'Remove um veículo do sistema a partir do seu ID.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do veículo a ser excluído.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Veículo removido com sucesso.',
            'data' => null,
        ],
        status: 200,
        description: 'Veículo removido com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Não autenticado',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Veículo não encontrado.'
    )]
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        Gate::authorize('delete', $vehicle);

        $vehicle->delete();
        return ApiResponder::success(message: 'Veículo removido com sucesso!');
    }
}
