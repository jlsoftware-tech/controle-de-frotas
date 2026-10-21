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
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\Response;

#[Group('Motoristas', description: 'Gerenciamento de motoristas.', authenticated: true)]
class DriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    #[Endpoint('Listar Motoristas', description: 'Retorna a lista paginada de motoristas cadastrados, com busca por nome, ordenação, paginação e filtragem por status (por padrão, retorna os motoristas ativos) e/ou categoria da cnh. Requer a permissão `view` do módulo `drivers`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Lista de todos os usuário',
            'data' => [
                'items' => [
                    [
                        'id' => 1,
                        'cpf' => '70516699660',
                        'status' => 'ACTIVE',
                        'name' => 'Miss Angie Ondricka',
                        'email' => 'Jadyn.Rolfson23@example.com',
                        'cnh' => '74156436272',
                        'cnh_category' => 'AB',
                        'cnh_expiration_date' => '2027-04-04',
                        'phone_number' => '+250302923654',
                        'profile' => [
                            'id' => 1,
                            'name' => 'Test Profile'
                        ],
                        'secretariat' => [
                            'id' => 1,
                            'name' => 'Secretaria de Administração',
                            'acronym' => 'SECAD',
                        ],
                        'created_at' => '19/10/2026 12:08:40',
                        'updated_at' => '19/10/2026 12:08:40',
                        'deleted_at' => null
                    ],
                ],
                'pagination' => [
                    'numPerPage' => 10,
                    'currPage' => 1,
                    'totalEntries' => 1,
                    'totalPages' => 1,
                ],
            ],
        ],
        status: 200,
        description: 'Lista paginada de usuários recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 400,
            'message' => 'Nenhum motorista encontrado.',
            'data' => null,
        ],
        status: 400,
        description: 'Nenhum usuário encontrado para os critérios informados.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Token expirado',
            'data' => null,
        ],
        status: 401,
        description: 'Token não fornecido (`Não autenticado`), inválido (`Token inválido`) ou expirado (`Token expirado`).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 403,
            'message' => 'Você não tem permissão para executar esta ação.',
            'data' => null,
        ],
        status: 403,
        description: 'Usuário sem a permissão `drivers.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'order' => ['O campo ordenação selecionado é inválido.'],
                'status' => ['O status selecionado é inválido.'],
                'cnh_category' => ['A categoria de CNH selecionada é inválida']
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function index(ListDriversRequest $request): JsonResponse
    {
        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');
        $status = $request->validated('status');
        $cnh_category = $request->validated('cnh_category');

        $drivers = Driver::withUser()
            ->when($status != 'ALL', fn ($q) => $q->where('status', $status))
            ->when($cnh_category != 'ALL', fn ($q) => $q->where('cnh_category', $cnh_category))
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
    #[Endpoint('Cadastrar Usuário', description: 'Cadastra um novo usuário com perfil e secretaria vinculados. Requer a permissão `create` do módulo `users`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'id' => 1,
                'cpf' => '70516699660',
                'status' => 'ACTIVE',
                'name' => 'Miss Angie Ondricka',
                'email' => 'Jadyn.Rolfson23@example.com',
                'cnh' => '74156436272',
                'cnh_category' => 'AB',
                'cnh_expiration_date' => '2027-04-04',
                'phone_number' => '+250302923654',
                'profile' => [
                    'id' => 1,
                    'name' => 'Test Profile'
                ],
                'secretariat' => [
                    'id' => 1,
                    'name' => 'Secretaria de Administração',
                    'acronym' => 'SECAD',
                ],
                'created_at' => '19/10/2026 12:08:40',
                'updated_at' => '19/10/2026 12:08:40',
                'deleted_at' => null
            ]
        ],
        status: 201,
        description: 'Motorista cadastrado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Token expirado',
            'data' => null,
        ],
        status: 401,
        description: 'Token não fornecido (`Não autenticado`), inválido (`Token inválido`) ou expirado (`Token expirado`).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 403,
            'message' => 'Você não tem permissão para executar esta ação.',
            'data' => null,
        ],
        status: 403,
        description: 'Usuário sem a permissão `drivers.create`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'name' => ['O campo nome é obrigatório.'],
                'email' => ['O campo e-mail é obrigatório.'],
                'password' => ['O campo senha é obrigatório.'],
                'profile_id' => ['O campo perfil é obrigatório.'],
                'secretariat_id' => ['O campo secretaria é obrigatório.'],
                'cpf' => ['O campo cpf é obrigatório.'],
                'cnh' => ['O campo cnh é obrigatório.'],
                'cnh_category' => ['O campo cnh_category é obrigatório.'],
                'cnh_expiration_date' => ['O campo cnh_expiration_date é obrigatório.'],
                'phone_number' => ['O campo phone_number é obrigatório.'],
                'status' => ['O status selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'email' => ['O campo e-mail deve ser um endereço de e-mail válido.'],
                'cnh' => ['O CNH já está sendo utilizado.'],
                'cpf' => ['O cpf já está sendo utilizado.'],
                'cnh_expiration' => ['O campo cnh expiration date deve ser uma data posterior a today.'],
                'profile_id' => ['O perfil selecionado é inválido.'],
                'secretariat_id' => ['O secretaria selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Outros erros de validação nos campos. `data` é indexado pelo nome do campo.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 500,
            'message' => 'Ocorreu um erro ao cadastrar o motorista. Por favor, tente novamente.',
            'data' => null,
        ],
        status: 500,
        description: 'Falha ao salvar o motorista.'
    )]
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
    #[Endpoint('Visualizar Motorista', description: 'Retorna os dados detalhados de um motorista a partir do seu ID. Requer a permissão `view` do módulo `drivers`.', authenticated: true)]
    #[UrlParam('driver', type: 'integer', description: 'ID do motorista a ser visualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'id' => 1,
                'cpf' => '70516699660',
                'status' => 'ACTIVE',
                'name' => 'Miss Angie Ondricka',
                'email' => 'Jadyn.Rolfson23@example.com',
                'cnh' => '74156436272',
                'cnh_category' => 'AB',
                'cnh_expiration_date' => '2027-04-04',
                'phone_number' => '+250302923654',
                'profile' => [
                    'id' => 1,
                    'name' => 'Test Profile'
                ],
                'secretariat' => [
                    'id' => 1,
                    'name' => 'Test Secretariat',
                    'acronym' => 'Test Secretariat'
                ],
                'created_at' => '19/10/2026 12:08:40',
                'updated_at' => '19/10/2026 12:08:40',
                'deleted_at' => null
            ]
        ],
        status: 200,
        description: 'Motorista recuperado com sucesso (`message` vazia).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Token expirado',
            'data' => null,
        ],
        status: 401,
        description: 'Token não fornecido (`Não autenticado`), inválido (`Token inválido`) ou expirado (`Token expirado`).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 403,
            'message' => 'Você não tem permissão para executar esta ação.',
            'data' => null,
        ],
        status: 403,
        description: 'Usuário sem a permissão `drivers.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Motorista não encontrado.'
    )]
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
    #[Endpoint('Atualizar Motorista', description: 'Atualiza parcialmente os dados cadastrais de um motorista a partir do seu ID; apenas os campos enviados são alterados. Requer a permissão `update` do módulo `drivers`.', authenticated: true)]
    #[UrlParam('driver', type: 'integer', description: 'ID do motorista a ser atualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Dados atualizados com sucesso.',
            'data' => [
                'id' => 1,
                'cpf' => '70516699660',
                'status' => 'ACTIVE',
                'name' => 'Miss Angie Ondricka',
                'email' => 'Jadyn.Rolfson23@example.com',
                'cnh' => '74156436272',
                'cnh_category' => 'AB',
                'cnh_expiration_date' => '2027-04-04',
                'phone_number' => '+250302923654',
                'profile' => [
                    'id' => 1,
                    'name' => 'Test Profile'
                ],
                'secretariat' => [
                    'id' => 1,
                    'name' => 'Test Secretariat',
                    'acronym' => 'Test Secretariat'
                ],
                'created_at' => '19/10/2026 12:08:40',
                'updated_at' => '19/10/2026 12:08:40',
                'deleted_at' => null
            ],
        ],
        status: 200,
        description: 'Motorista atualizado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Token expirado',
            'data' => null,
        ],
        status: 401,
        description: 'Token não fornecido (`Não autenticado`), inválido (`Token inválido`) ou expirado (`Token expirado`).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 403,
            'message' => 'Você não tem permissão para executar esta ação.',
            'data' => null,
        ],
        status: 403,
        description: 'Usuário sem a permissão `drivers.update`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Motorista não encontrado.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'email' => ['O campo e-mail deve ser um endereço de e-mail válido.'],
                'cpf' => ['O cpf já está sendo utilizado.'],
                'cnh' => ['O CNH já está sendo utilizado.'],
                'cnh_expiration' => ['O campo cnh expiration date deve ser uma data posterior a today.'],
                'profile_id' => ['O perfil selecionado é inválido.'],
                'secretariat_id' => ['O secretaria selecionado é inválido.'],
                'status' => ['O status selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 500,
            'message' => 'Ocorreu um erro ao atualizar os dados.',
            'data' => null,
        ],
        status: 500,
        description: 'Falha ao atualizar o usuário.'
    )]
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
    #[Endpoint('Excluir Motorista', description: 'Remove um motorista (exclusão lógica) a partir do seu ID. Requer a permissão `delete` do módulo `drivers`.', authenticated: true)]
    #[UrlParam('driver', type: 'integer', description: 'ID do motorista a ser excluído.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Usuário removido com sucesso.',
            'data' => null,
        ],
        status: 200,
        description: 'Motorista removido com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 401,
            'message' => 'Token expirado',
            'data' => null,
        ],
        status: 401,
        description: 'Token não fornecido (`Não autenticado`), inválido (`Token inválido`) ou expirado (`Token expirado`).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 403,
            'message' => 'Você não tem permissão para executar esta ação.',
            'data' => null,
        ],
        status: 403,
        description: 'Motorista sem a permissão `drivers.delete`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Motorista não encontrado.'
    )]
    public function destroy(Driver $driver)
    {
        Gate::authorize('delete', $driver);

        $driver->delete();

        return ApiResponder::success(message: 'Motorista removido com sucesso');
    }
}
