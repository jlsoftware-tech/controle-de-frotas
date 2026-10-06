<?php

namespace App\Http\Controllers;

use App\Http\Requests\Secretariat\ListSecretariatRequest;
use App\Http\Requests\Secretariat\StoreSecretariatRequest;
use App\Http\Requests\Secretariat\UpdateSecretariatRequest;
use App\Models\Secretariat;
use App\Support\ApiResponder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\Response;

#[Group('Secretarias', description: 'Gerenciamento das secretarias do sistema.')]
class SecretariatController extends Controller
{
    /**
     * List all secretariats.
     */
    #[Endpoint('Listar Secretarias', description: 'Retorna a lista paginada de secretarias, com busca por nome, ordenação e paginação. Requer a permissão `view` do módulo `secretariats`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Lista de todas as secretarias',
            'data' => [
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Secretaria de Administração',
                        'acronym' => 'SECAD',
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
        ],
        status: 200,
        description: 'Lista paginada de secretarias recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Nenhuma secretaria encontrada para esta pesquisa.',
            'data' => null,
        ],
        status: 200,
        description: 'Nenhuma secretaria encontrada para os critérios informados (`data` é `null`).'
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
        description: 'Usuário sem a permissão `secretariats.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'order' => ['O campo ordenação selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function index(ListSecretariatRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Secretariat::class);

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

        return ApiResponder::success(
            $secretariats->toResourceCollection(),
            'Lista de todas as secretarias'
        );
    }

    /**
     * Create a new secretariat.
     */
    #[Endpoint('Cadastrar Secretaria', description: 'Cadastra uma nova secretaria. Requer a permissão `create` do módulo `secretariats`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 201,
            'message' => 'Secretaria cadastrada com sucesso!',
            'data' => [
                'id' => 1,
                'name' => 'Secretaria de Administração',
                'acronym' => 'SECAD',
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 201,
        description: 'Secretaria cadastrada com sucesso.'
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
        description: 'Usuário sem a permissão `secretariats.create`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'name' => ['O campo nome é obrigatório.'],
                'acronym' => ['O campo sigla é obrigatório.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 500,
            'message' => 'Ocorreu um erro ao cadastrar a secretaria. Por favor, tente novamente.',
            'data' => null,
        ],
        status: 500,
        description: 'Falha ao salvar a secretaria.'
    )]
    public function store(StoreSecretariatRequest $request): JsonResponse
    {
        Gate::authorize('create', Secretariat::class);

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
     * Show a secretariat.
     */
    #[Endpoint('Visualizar Secretaria', description: 'Retorna os dados de uma secretaria a partir do seu ID. Requer a permissão `view` do módulo `secretariats`.', authenticated: true)]
    #[UrlParam('secretariat', type: 'integer', description: 'ID da secretaria.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => '',
            'data' => [
                'id' => 1,
                'name' => 'Secretaria de Administração',
                'acronym' => 'SECAD',
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Secretaria recuperada com sucesso (`message` vazia).'
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
        description: 'Usuário sem a permissão `secretariats.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Secretaria não encontrada.'
    )]
    public function show(Secretariat $secretariat)
    {
        Gate::authorize('view', $secretariat);

        return ApiResponder::success(
            $secretariat->toResource(),
            '',
            Response::HTTP_OK,
        );
    }

    /**
     * Update a secretariat.
     */
    #[Endpoint('Atualizar Secretaria', description: 'Atualiza o nome e/ou a sigla de uma secretaria a partir do seu ID. Requer a permissão `update` do módulo `secretariats`.', authenticated: true)]
    #[UrlParam('secretariat', type: 'integer', description: 'ID da secretaria a ser atualizada.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Dados atualizados com sucesso',
            'data' => [
                'id' => 1,
                'name' => 'Secretaria de Administração',
                'acronym' => 'SECAD',
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Secretaria atualizada com sucesso.'
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
        description: 'Usuário sem a permissão `secretariats.update`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Secretaria não encontrada.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'acronym' => ['O campo sigla não pode ter mais de 16 caracteres.'],
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
        description: 'Falha ao atualizar a secretaria.'
    )]
    public function update(UpdateSecretariatRequest $request, Secretariat $secretariat)
    {
        Gate::authorize('update', $secretariat);

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
     * Delete a secretariat.
     */
    #[Endpoint('Excluir Secretaria', description: 'Remove uma secretaria a partir do seu ID. Requer a permissão `delete` do módulo `secretariats`.', authenticated: true)]
    #[UrlParam('secretariat', type: 'integer', description: 'ID da secretaria a ser removida.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Secretaria removida com sucesso.',
            'data' => null,
        ],
        status: 200,
        description: 'Secretaria removida com sucesso.'
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
        description: 'Usuário sem a permissão `secretariats.delete`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Secretaria não encontrada.'
    )]
    public function destroy(Secretariat $secretariat)
    {
        Gate::authorize('delete', $secretariat);

        $secretariat->delete();

        return ApiResponder::success(message: 'Secretaria removida com sucesso.');
    }
}
