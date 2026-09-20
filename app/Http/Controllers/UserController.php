<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ListUserPermissionsRequest;
use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Models\Permission;
use App\Models\User;
use App\Support\ApiResponder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\Response;

#[Group('Endpoints de usuário', 'Gerenciamento de recursos.', true)]
class UserController extends Controller
{
    #[Endpoint('Listar recursos da barra lateral (sidebar)',
        description: 'Lista dos recursos permitidos de acordo com o perfil do usuário.',
        authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'icon' => 'FaUser',
                'name_menu' => 'Usuário',
                'sub_menu' => [
                    [
                        'icon' => 'FaUsers',
                        'name_sub_menu' => 'Gerenciar usuários',
                        'url' => '/usuarios',
                    ],
                    [
                        'icon' => 'FaUserShield',
                        'name_mub_menu' => 'Perfis de acesso',
                        'url' => '/perfis',
                    ],
                ],
            ],
        ],
        status: 200,
        description: 'Opções do menu sidebar de acordo com as permissões do usuário.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => 403,
            'message' => 'Não autorizado.',
            'data' => null,
        ],
        status: 403,
        description: 'Conta não encontrada ou inexistente.'
    )]
    /**
     * Lista todas as ações conforme o perfil do usuário
     */
    public function sidebar()
    {
        // verifica quais ações que usuário autenticado tem permissão de usar,
        // e monta a estrutura da resposta, do contrário retorna null
        $canAccessMenu = function (array $subMenu, User $user, array $modules) {
            return array_map(
                function ($item) use ($modules) {
                    if (! in_array($item[3], $modules)) {
                        return null;
                    }

                    // retorna a estrutura da resposta do subMenu,
                    // indicando que o usuário tem permissão para tal ação
                    return [
                        'icon' => $item[0],
                        'name_sub_menu' => $item[1],
                        'url' => $item[2],
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
         * padrão: ['nome_do_icone', 'nome_do_sub_menu', 'rota_do_front', 'nome_do_modulo']
         */
        $subMenuOptions = [
            'user' => [
                ['FaUsers', 'Gerenciar usuários', '/usuarios', 'users'],
                ['FaUserShield', 'Perfis de acesso', '/perfis', 'profiles'],
                ['FaUserLock', 'Permissões de usuário', '/permisoes', 'permissions'],
            ],
            'secretariat' => [
                ['FaLandmark', 'Gerenciar secretarias', '/secretarias', 'secretariats'],
            ],
        ];

        // array de todos possíveis menus do sidebar do usuário
        $sidebar = $makeMenu($menuOptions, $subMenuOptions, $modules, $user);

        // remove do array subMenus valores nulos
        foreach ($sidebar as &$menu) {
            $menu['sub_menu'] = array_filter($menu['sub_menu'], function ($subMenu) {
                return $subMenu && count($subMenu);
            });
        }
        unset($menu);

        $sidebar = array_filter($sidebar, function ($menu) {
            return (bool) count($menu['sub_menu']);
        });

        // reindexa os itens do menu, evita a exibição de índices na resposta da api
        // $sidebar = array_values($sidebar);

        // verifica se o usuário tem alguma permissão
        if (count($sidebar) == 0) {
            return ApiResponder::error('Não autorizado.', 403);
        }

        return ApiResponder::success($sidebar);
    }

    #[Endpoint('Listar Usuários', description: 'Retorna uma lista paginada de usuários cadastrados no sistema com opções de busca, ordenação e paginação.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'statusCode' => 200,
            'data' => [
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Maria Santos',
                        'email' => 'maria.santos@example.com',
                        'profile_id' => 1,
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
        ],
        status: 200,
        description: 'Lista paginada de usuários recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'statusCode' => 400,
            'data' => null,
            'pagination' => null,
        ],
        status: 400,
        description: 'Nenhum usuário encontrado para os critérios informados.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
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
    public function index(ListUsersRequest $request): UserCollection|JsonResponse
    {
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

        return new UserCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Endpoint('Cadastrar Usuário', description: 'Cadastra um novo usuário no sistema com perfil e secretaria vinculados.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso!',
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'profile_id' => 1,
                'secretariat_id' => 1,
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Usuário cadastrado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'O campo nome é obrigatório.',
            'errors' => [
                'name' => ['O campo nome é obrigatório.'],
                'email' => ['O campo e-mail é obrigatório.'],
                'password' => ['O campo senha é obrigatório.'],
                'profile_id' => ['O campo perfil é obrigatório.'],
                'secretariat_id' => ['O campo secretaria é obrigatório.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados.'
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
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
     * Display the specified resource.
     */
    #[Endpoint('Visualizar Usuário', description: 'Retorna os dados detalhados de um usuário específico a partir do seu ID.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do usuário a ser visualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'profile_id' => 1,
                'secretariat_id' => 1,
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Detalhes do usuário recuperados com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Usuário não encontrado.'
    )]
    public function show(User $user): JsonResponse
    {
        return ApiResponder::success(
            $user->toResource(),
            '',
            Response::HTTP_OK,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    #[Endpoint('Atualizar Usuário', description: 'Atualiza os dados cadastrais de um usuário existente a partir do seu ID.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do usuário a ser atualizado.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'message' => 'Dados atualizados com sucesso',
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos Silva',
                'email' => 'maria.silva@example.com',
                'profile_id' => 1,
                'secretariat_id' => 1,
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:05:00',
                'deleted_at' => null,
            ],
        ],
        status: 200,
        description: 'Dados atualizados com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'errors' => [
                'email' => ['O campo e-mail deve ser um endereço de e-mail válido.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Usuário não encontrado.'
    )]
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
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
     * Remove the specified resource from storage.
     */
    #[Endpoint('Excluir Usuário', description: 'Remove um usuário do sistema a partir do seu ID.', authenticated: true)]
    #[UrlParam('id', type: 'integer', description: 'ID do usuário a ser excluído.', example: 1)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'message' => 'Usuário removido com sucesso!',
            'data' => [
                'id' => 1,
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'profile_id' => 1,
                'secretariat_id' => 1,
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:10:00',
                'deleted_at' => '01/09/2026 10:15:00',
            ],
        ],
        status: 200,
        description: 'Usuário removido com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Recurso não encontrado.',
            'data' => null,
        ],
        status: 404,
        description: 'Usuário não encontrado.'
    )]
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return ApiResponder::success(message: 'Usuário removido com sucesso.');
    }

    /**
     * Retorna os dados do perfil de acesso do usuário autenticado.
     */
    #[Endpoint('Perfil de acesso do usuário', description: 'Retorna os dados do perfil de acesso associado ao usuário autenticado.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                'id' => 1,
                'name' => 'Administrador',
                'description' => 'Perfil com acesso total ao sistema.',
                'created_at' => '01/09/2026 10:00:00',
                'updated_at' => '01/09/2026 10:00:00',
            ],
        ],
        status: 200,
        description: 'Perfil de acesso recuperado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    public function profile(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return ApiResponder::success(
            $user->profile
        );
    }

    /**
     * Retorna as permissões do perfil do usuário autenticado.
     */
    #[Endpoint('Permissões que o usuário possui', description: 'Retorna a lista de todas as permissões associadas ao perfil do usuário autenticado.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'message' => 'Sucesso.',
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Criar Usuário',
                    'module' => 'users',
                    'created_at' => '01/09/2026 10:00:00',
                    'updated_at' => '01/09/2026 10:00:00',
                    'pivot' => [
                        'profile_id' => 1,
                        'permission_id' => 1,
                    ],
                ],
            ],
        ],
        status: 200,
        description: 'Lista de permissões recuperada com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'message' => 'Token não fornecido.',
            'data' => null,
        ],
        status: 401,
        description: 'Token de autenticação não fornecido ou inválido.'
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
                    ])
                ]
            )
            ->toArray();

        return ApiResponder::success(
            $permissions
        );
    }
}
