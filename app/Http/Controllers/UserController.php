<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ListUserPermissionsRequest;
use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateInfoRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use App\Support\ApiResponder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\Response;

#[Group('Usuários', description: 'Gerenciamento de usuários e consulta dos dados, perfil e permissões do usuário autenticado.', authenticated: true)]
class UserController extends Controller
{
    /**
     * List the sidebar resources allowed by the authenticated user's permissions.
     */
    #[Endpoint('Listar recursos da barra lateral (sidebar)', description: 'Retorna os menus e submenus (com ícone, nome, descrição e URL do front-end) aos quais o usuário autenticado tem acesso, de acordo com as permissões do seu perfil. Menus sem nenhum submenu permitido são omitidos.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                [
                    'icon' => 'FaUser',
                    'name_menu' => 'Usuários',
                    'sub_menu' => [
                        [
                            'icon' => 'FaUsers',
                            'name_sub_menu' => 'Gerenciar usuários',
                            'description' => 'Cadastre, edite e gerencie os usuários do sistema.',
                            'url' => '/usuarios',
                        ],
                        [
                            'icon' => 'FaUserShield',
                            'name_sub_menu' => 'Perfis de acesso',
                            'description' => 'Crie e gerencie os perfis de acesso para definir quais recursos cada usuário pode utilizar.',
                            'url' => '/perfis',
                        ],
                    ],
                ],
            ],
        ],
        status: 200,
        description: 'Menus permitidos para o usuário.'
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
            'message' => 'Não autorizado.',
            'data' => null,
        ],
        status: 403,
        description: 'O usuário não possui permissão para nenhum menu.'
    )]
    public function sidebar()
    {
        // verifica quais ações que usuário autenticado tem permissão de usar,
        // e monta a estrutura da resposta, do contrário retorna null
        $canAccessMenu = function (array $subMenu, User $user, array $modules) {
            return array_map(
                function ($item) use ($modules) {
                    if (! in_array($item[4], $modules)) {
                        return null;
                    }

                    // retorna a estrutura da resposta do subMenu,
                    // indicando que o usuário tem permissão para tal ação
                    return [
                        'icon' => $item[0],
                        'name_sub_menu' => $item[1],
                        'description' => $item[2],
                        'url' => $item[3],
                    ];
                },
                $subMenu
            );
        };

        $makeMenu = function (
            array $menuOptions,
            array $subMenuOptions,
            array $modules,
            User $user
        ) use ($canAccessMenu) {
            return array_map(
                function ($item) use ($user, $canAccessMenu, $modules, $subMenuOptions) {
                    return [
                        'icon' => $item[0],
                        'name_menu' => $item[1],
                        'sub_menu' => $canAccessMenu($subMenuOptions[$item[2]], $user, $modules),
                    ];
                },
                $menuOptions
            );
        };

        $user = Auth::guard('api')->user();
        $modules = $user->permissions->select(['module'])->toArray();
        $modules = array_unique(array_column($modules, 'module'));

        /* opções principais do menu
         * padrão: ['nome_do_icone', 'nome_do_menu', 'nome_do_modulo_no_singular']
         * ícones do Font Awesome 5: https://react-icons.github.io/react-icons/icons/fa/
         */
        $menuOptions = [
            ['FaUser', 'Usuários', 'user'],
            ['FaLandmark', 'Secretarias', 'secretariat'],
        ];

        /* opções do sub menu de cada menu principal
         * padrão: ['nome_do_icone', 'nome_do_sub_menu', 'descricao', 'rota_do_front', 'nome_do_modulo']
         */
        $subMenuOptions = [
            'user' => [
                ['FaUsers', 'Gerenciar usuários', 'Cadastre, edite e gerencie os usuários do sistema.', '/usuarios', 'users'],
                ['FaUserShield', 'Perfis de acesso', 'Crie e gerencie os perfis de acesso para definir quais recursos cada usuário pode utilizar.', '/perfis', 'profiles'],
                ['FaUserLock', 'Permissões de usuário', 'Configure as permissões individuais de acesso dos usuários às funcionalidades do sistema.', '/permissoes', 'permissions'],
            ],
            'secretariat' => [
                ['FaLandmark', 'Gerenciar secretarias', 'Cadastre e gerencie as secretarias e suas informações no sistema.', '/secretarias', 'secretariats'],
            ],
        ];

        // array de todos possíveis menus do sidebar do usuário
        $sidebar = $makeMenu($menuOptions, $subMenuOptions, $modules, $user);

        // remove do array subMenus nulos, e evita indexação
        // explícita em caso de remoção de alguma permissão
        foreach ($sidebar as &$menu) {
            $menu['sub_menu'] = array_values(
                array_filter($menu['sub_menu'], function ($subMenu) {
                    return $subMenu && count($subMenu);
                })
            );
        }
        unset($menu);

        $sidebar = array_filter($sidebar, function ($menu) {
            return (bool) count($menu['sub_menu']);
        });

        // reindexa os itens do menu, evita a exibição de índices na resposta da api
        $sidebar = array_values($sidebar);

        // verifica se o usuário tem alguma permissão
        if (count($sidebar) == 0) {
            return ApiResponder::error('Não autorizado.', 403);
        }

        return ApiResponder::success($sidebar);
    }

    /**
     * List all users.
     */
    #[Endpoint('Listar Usuários', description: 'Retorna a lista paginada de usuários cadastrados, com busca por nome, ordenação e paginação. Requer a permissão `view` do módulo `users`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Lista de todos os usuário',
            'data' => [
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Maria Santos',
                        'email' => 'maria.santos@example.com',
                        'profile' => [
                            'id' => 1,
                            'name' => 'Administrador',
                        ],
                        'secretariat' => [
                            'id' => 1,
                            'name' => 'Secretaria de Administração',
                            'acronym' => 'SECAD',
                        ],
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
        description: 'Lista paginada de usuários recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 400,
            'message' => 'Nenhum usuário encontrado para essa pesquisa.',
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
        description: 'Usuário sem a permissão `users.view`.'
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
    public function index(ListUsersRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $page = $request->validated('page');
        $per_page = $request->validated('per_page');
        $search = $request->validated('search');
        $sort = $request->validated('sort');
        $order = $request->validated('order');

        $users = User::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $order)
            ->paginate($per_page, ['*'], 'page', $page);

        if (count($users) === 0) {
            return ApiResponder::error('Nenhum usuário encontrado para essa pesquisa.');
        }

        return ApiResponder::success(
            $users->toResourceCollection(),
            'Lista de todos os usuário',
        );
    }

    /**
     * Create a new user.
     */
    #[Endpoint('Cadastrar Usuário', description: 'Cadastra um novo usuário com perfil e secretaria vinculados. Requer a permissão `create` do módulo `users`.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 201,
            'message' => 'Usuário cadastrado com sucesso!',
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'profile' => [
                    'id' => 1,
                    'name' => 'Administrador',
                ],
                'secretariat' => [
                    'id' => 1,
                    'name' => 'Secretaria de Administração',
                    'acronym' => 'SECAD',
                ],
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 201,
        description: 'Usuário cadastrado com sucesso.'
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
        description: 'Usuário sem a permissão `users.create`.'
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
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 500,
            'message' => 'Ocorreu um erro ao cadastrar o usuário. Por favor, tente novamente.',
            'data' => null,
        ],
        status: 500,
        description: 'Falha ao salvar o usuário.'
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_id' => $request->profile_id,
                'secretariat_id' => $request->secretariat_id,
            ]);
        } catch (Exception) {
            return ApiResponder::error(
                'Ocorreu um erro ao cadastrar o usuário. Por favor, tente novamente.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return ApiResponder::success(
            $user->toResource(),
            'Usuário cadastrado com sucesso!',
            Response::HTTP_CREATED,
        );
    }

    /**
     * Show a user.
     */
    #[Endpoint('Visualizar Usuário', description: 'Retorna os dados detalhados de um usuário a partir do seu ID. Requer a permissão `view` do módulo `users`.', authenticated: true)]
    #[UrlParam('user', type: 'integer', description: 'ID do usuário a ser visualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => '',
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'profile' => [
                    'id' => 1,
                    'name' => 'Administrador',
                ],
                'secretariat' => [
                    'id' => 1,
                    'name' => 'Secretaria de Administração',
                    'acronym' => 'SECAD',
                ],
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Usuário recuperado com sucesso (`message` vazia).'
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
        description: 'Usuário sem a permissão `users.view`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Usuário não encontrado.'
    )]
    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        return ApiResponder::success(
            $user->toResource(),
            '',
            Response::HTTP_OK,
        );
    }

    /**
     * Update a user.
     */
    #[Endpoint('Atualizar Usuário', description: 'Atualiza parcialmente os dados cadastrais de um usuário a partir do seu ID; apenas os campos enviados são alterados. Requer a permissão `update` do módulo `users`.', authenticated: true)]
    #[UrlParam('user', type: 'integer', description: 'ID do usuário a ser atualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Dados atualizados com sucesso.',
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos Silva',
                'email' => 'maria.silva@example.com',
                'profile' => [
                    'id' => 1,
                    'name' => 'Administrador',
                ],
                'secretariat' => [
                    'id' => 1,
                    'name' => 'Secretaria de Administração',
                    'acronym' => 'SECAD',
                ],
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Usuário atualizado com sucesso.'
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
        description: 'Usuário sem a permissão `users.update`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Usuário não encontrado.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'email' => ['O campo e-mail deve ser um endereço de e-mail válido.'],
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
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $status = $user->update($request->all());

        return $status
            ? ApiResponder::success(
                $user->toResource(),
                'Dados atualizados com sucesso.',
                Response::HTTP_OK,
            )
            : ApiResponder::error(
                'Ocorreu um erro ao atualizar os dados.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
    }

    /**
     * Delete a user.
     */
    #[Endpoint('Excluir Usuário', description: 'Remove um usuário (exclusão lógica) a partir do seu ID. Requer a permissão `delete` do módulo `users`.', authenticated: true)]
    #[UrlParam('user', type: 'integer', description: 'ID do usuário a ser excluído.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Usuário removido com sucesso.',
            'data' => null,
        ],
        status: 200,
        description: 'Usuário removido com sucesso.'
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
        description: 'Usuário sem a permissão `users.delete`.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 404,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Usuário não encontrado.'
    )]
    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return ApiResponder::success(message: 'Usuário removido com sucesso.');
    }

    /**
     * Return the access profile of the authenticated user.
     */
    #[Endpoint('Perfil de acesso do usuário', description: 'Retorna os dados do perfil de acesso associado ao usuário autenticado. As datas são retornadas em ISO 8601 (UTC), pois o modelo é serializado diretamente.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'id' => 1,
                'name' => 'Administrador',
                'description' => 'Perfil com acesso total ao sistema.',
                'created_at' => '2026-09-01T10:00:00.000000Z',
                'updated_at' => '2026-09-01T10:00:00.000000Z',
            ],
        ],
        status: 200,
        description: 'Perfil de acesso recuperado com sucesso.'
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
    public function profile(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return ApiResponder::success(
            $user->profile
        );
    }

    /**
     * Return the permissions of the authenticated user.
     */
    #[Endpoint('Permissões que o usuário possui', description: 'Retorna, agrupadas por módulo, cada ação existente no sistema (`view`, `create`, `update`, `delete`) indicando com `true` ou `false` se o usuário autenticado pode executá-la. Use `modules[]` para filtrar os módulos retornados.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'users' => [
                    'view' => true,
                    'create' => true,
                    'update' => true,
                    'delete' => false,
                ],
                'profiles' => [
                    'view' => true,
                    'create' => false,
                    'update' => false,
                    'delete' => false,
                ],
            ],
        ],
        status: 200,
        description: 'Permissões do usuário agrupadas por módulo.'
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
            'message' => 'O campo modules deve ser uma lista.',
            'errors' => [
                'modules' => ['O campo modules deve ser uma lista.'],
            ],
        ],
        status: 422,
        description: '`modules` não é uma lista. Este request usa o formato de erro padrão do Laravel (`message` e `errors`).'
    )]
    public function permissions(ListUserPermissionsRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $permissions = Permission::query()
            ->when($request->filled('modules'), function (Builder $q) use ($request) {
                return $q->whereIn('module', $request->input('modules'));
            })
            ->get(['module', 'action'])
            ->groupBy('module')
            ->mapWithKeys(fn ($items, $module) => [
                $module => $items->mapWithKeys(
                    fn ($permission) => [
                        $permission->action => $user->can($permission->action, Relation::getMorphedModel($module)),
                    ]),
            ]
            )
            ->toArray();

        return ApiResponder::success(
            $permissions
        );
    }

    /**
     * Update the authenticated user's personal data.
     */
    #[Endpoint('Atualização dos dados pessoais', description: 'Atualiza o nome e/ou o e-mail do usuário autenticado. Pelo menos um campo deve ser enviado. A resposta traz o modelo do usuário (sem `profile` e `secretariat` aninhados), com datas em ISO 8601 (UTC).', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Dados atualizados com sucesso',
            'data' => [
                'id' => 1,
                'name' => 'Jorge Luis Fonseca',
                'email' => 'jorge_lois@gmail.com',
                'email_verified_at' => null,
                'profile_id' => 1,
                'secretariat_id' => 1,
                'created_at' => '2026-09-01T10:00:00.000000Z',
                'updated_at' => '2026-09-01T10:05:00.000000Z',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Dados atualizados com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 400,
            'message' => 'Nenhum dado foi enviado',
            'data' => null,
        ],
        status: 400,
        description: 'Nenhum campo (`name` ou `email`) foi enviado.'
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
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'email' => ['O campo e-mail deve ser um endereço de e-mail válido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function updateInfo(UpdateInfoRequest $request): JsonResponse
    {

        $update = $request->validated();
        if (empty($update)) {
            return ApiResponder::error('Nenhum dado foi enviado');
        }

        Auth::guard('api')->user()->update($update);

        return ApiResponder::success(
            Auth::guard('api')->user(),
            'Dados atualizados com sucesso'
        );
    }

    /**
     * Reset the authenticated user's password.
     */
    #[Endpoint('Redefinição de senha', description: 'Redefine a senha do usuário autenticado. Se a nova senha for igual à atual, nada é alterado e a resposta (200) informa que deve ser enviada uma senha diferente.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sua senha foi redefinida com sucesso',
            'data' => null,
        ],
        status: 200,
        description: 'Senha redefinida com sucesso. Quando a nova senha é igual à atual, retorna 200 com a mensagem `Informe uma senha diferente para redefinir sua senha` e a senha não é alterada.'
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
            'status_code' => 422,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'password' => ['Informe uma senha'],
            ],
        ],
        status: 422,
        description: '`password` ausente (`Informe uma senha`), com menos de 8 caracteres ou sem `password_confirmation` correspondente (`A confirmação do campo senha não confere.`). `data` é indexado pelo nome do campo.'
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if (Hash::check($request->password, $user->password)) {
            return ApiResponder::success(message: 'Informe uma senha diferente para redefinir sua senha');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return ApiResponder::success(message: 'Sua senha foi redefinida com sucesso');
    }
}
