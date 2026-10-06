<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permissions\ListPermissionsRequest;
use App\Models\Permission;
use App\Models\Profile;
use App\Support\ApiResponder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Permissões', description: 'Consulta das permissões que podem ser atribuídas aos perfis de acesso.')]
class PermissionController extends Controller
{
    /**
     * List the system permissions.
     */
    #[Endpoint('Listar Permissões', description: 'Retorna a lista paginada das permissões (`action` + `module`) que podem ser vinculadas aos perfis de acesso, com busca por ação, ordenação e paginação. Requer a permissão `view` do módulo `profiles`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Lista de todas as permissões',
            'data' => [
                'items' => [
                    [
                        'id' => 1,
                        'action' => 'view',
                        'module' => 'users',
                        'created_at' => '01/09/2026 10:00:00',
                        'updated_at' => '01/09/2026 10:00:00',
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
        description: 'Lista paginada de permissões recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Nenhuma permissão encontrada para esta pesquisa.',
            'data' => null,
        ],
        status: 200,
        description: 'Nenhuma permissão encontrada para os critérios informados (`data` é `null`).'
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
        description: 'Usuário sem a permissão `profiles.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'sort' => ['O campo ordenação selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function index(ListPermissionsRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $permissions = Permission::query()
            ->when($request->filled('search'), fn ($query) => $query->where('action', 'like', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($permissions) === 0) {
            return ApiResponder::success(message: 'Nenhuma permissão encontrada para esta pesquisa.');
        }

        return ApiResponder::success(
            $permissions->toResourceCollection(),
            'Lista de todas as permissões'
        );
    }

    /**
     * Show a permission.
     */
    #[Endpoint('Visualizar Permissão', description: 'Retorna os dados de uma permissão específica a partir do seu ID. Requer a permissão `view` do módulo `profiles`. As datas são retornadas em ISO 8601 (UTC), pois o modelo é serializado diretamente.', authenticated: true)]
    #[UrlParam('permission', type: 'integer', description: 'ID da permissão.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'id' => 1,
                'action' => 'view',
                'module' => 'users',
                'created_at' => '2026-09-01T10:00:00.000000Z',
                'updated_at' => '2026-09-01T10:00:00.000000Z',
            ],
        ],
        status: 200,
        description: 'Permissão recuperada com sucesso.'
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
        description: 'Usuário sem a permissão `profiles.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 400,
            'message' => 'Perfil não encontrado',
            'data' => null,
        ],
        status: 400,
        description: 'Permissão não encontrada (a mensagem menciona "Perfil" por texto legado da API).'
    )]
    public function show(string $id): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        try {
            $permission = Permission::findOrFail($id);
        } catch (ModelNotFoundException) {
            return ApiResponder::error('Perfil não encontrado');
        }

        return ApiResponder::success($permission);
    }
}
