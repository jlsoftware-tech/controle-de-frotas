<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ListProfilesRequest;
use App\Http\Requests\Profile\StoreProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Profile;
use App\Support\ApiResponder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\Response;

#[Group('Perfis de acesso', description: 'Gerenciamento dos perfis de acesso e das permissões vinculadas a cada um.')]
class ProfileController extends Controller
{
    /**
     * List all profiles.
     */
    #[Endpoint('Listar Perfis', description: 'Retorna a lista paginada de perfis de acesso, com busca por nome, ordenação e paginação. Requer a permissão `view` do módulo `profiles`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Lista de todos os perfis de acesso',
            'data' => [
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Administrador',
                        'description' => 'Acesso geral ao sistema',
                        'permissions' => [],
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
        description: 'Lista paginada de perfis recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 400,
            'message' => 'Nenhum profile encontrado para esta pesquisa.',
            'data' => null,
        ],
        status: 400,
        description: 'Nenhum perfil encontrado para os critérios informados.'
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
                'order' => ['O campo ordenação selecionado é inválido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function index(ListProfilesRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Profile::class);

        $search = $request->validated('search');
        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $profiles = Profile::query()
            ->when($request->filled('search'), fn (Builder $q) => $q->whereLike('name', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($profiles) === 0) {
            return ApiResponder::error('Nenhum profile encontrado para esta pesquisa.');
        }

        return ApiResponder::success(
            $profiles->toResourceCollection(),
            'Lista de todos os perfis de acesso'
        );
    }

    /**
     * Create a new profile.
     */
    #[Endpoint('Cadastrar Perfil', description: 'Cadastra um novo perfil de acesso e vincula as permissões informadas. Requer a permissão `create` do módulo `profiles`. Responde com status 200 (e não 201).', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Perfil criado com sucesso',
            'data' => [
                'id' => 1,
                'name' => 'Administrador',
                'description' => 'Acesso geral ao sistema',
                'created_at' => '2026-09-01T10:00:00.000000Z',
                'updated_at' => '2026-09-01T10:00:00.000000Z',
            ],
        ],
        status: 200,
        description: 'Perfil criado com sucesso. As datas são retornadas em ISO 8601 (UTC).'
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
        description: 'Usuário sem a permissão `profiles.create`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'name' => ['O campo nome é obrigatório.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function store(StoreProfileRequest $request): JsonResponse
    {
        Gate::authorize('create', Profile::class);

        try {
            $newProfile = Profile::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            $newProfile->permissions()->attach($request->input('permissions'));

            $newProfile->save();
        } catch (Exception $e) {
            return ApiResponder::error();
        }

        return ApiResponder::success($newProfile, 'Perfil criado com sucesso');
    }

    /**
     * Show a profile.
     */
    #[Endpoint('Visualizar Perfil', description: 'Retorna os dados de um perfil de acesso a partir do seu ID (sem a lista de permissões). Requer a permissão `view` do módulo `profiles`.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do perfil de acesso.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'id' => 1,
                'name' => 'Administrador',
                'description' => 'Acesso geral ao sistema',
                'created_at' => '2026-09-01T10:00:00.000000Z',
                'updated_at' => '2026-09-01T10:00:00.000000Z',
            ],
        ],
        status: 200,
        description: 'Perfil recuperado com sucesso. As datas são retornadas em ISO 8601 (UTC).'
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
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Perfil não encontrado.'
    )]
    public function show(Profile $profile): JsonResponse
    {
        Gate::authorize('view', $profile);

        return ApiResponder::success($profile);
    }

    /**
     * Update a profile.
     */
    #[Endpoint('Atualizar Perfil', description: 'Atualiza nome, descrição e permissões de um perfil de acesso. O conjunto de permissões do perfil é substituído pelo enviado em `permissions`. Requer a permissão `update` do módulo `profiles`.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do perfil de acesso a ser atualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Dados atualizados com sucesso',
            'data' => null,
        ],
        status: 200,
        description: 'Perfil atualizado com sucesso (`data` é `null`).'
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
        description: 'Usuário sem a permissão `profiles.update`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Perfil não encontrado.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'name' => ['O campo nome é obrigatório.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        Gate::authorize('update', $profile);

        $profile->permissions()->sync($request->input('permissions'));

        $status = $profile->update($request->only(['name', 'description']));

        return $status ?
            ApiResponder::success(message: 'Dados atualizados com sucesso') : ApiResponder::error();
    }

    /**
     * Delete a profile.
     */
    #[Endpoint('Excluir Perfil', description: 'Remove um perfil de acesso a partir do seu ID. Requer a permissão `delete` do módulo `profiles`.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do perfil de acesso a ser removido.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Perfil removido com sucesso',
            'data' => null,
        ],
        status: 200,
        description: 'Perfil removido com sucesso.'
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
        description: 'Usuário sem a permissão `profiles.delete`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Perfil não encontrado.'
    )]
    public function destroy(Profile $profile): JsonResponse
    {
        Gate::authorize('delete', $profile);

        try {
            $profile->deleteOrFail();
        } catch (Exception $e) {
            return ApiResponder::error(
                'Ocorreu um erro. Tente novamente mais tarde.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponder::success(message: 'Perfil removido com sucesso');
    }
}
