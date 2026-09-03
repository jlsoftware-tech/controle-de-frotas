<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Mockery\Exception;

class UserController extends Controller
{
    #[Endpoint('Lista de recursos permitidos (sidebar)',
        description: 'Lista dos recursos permitidos de acordo com o perfil do usuario.',
        authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'icon' => 'fa fa-user',
                'nameMenu' => 'Usuário',
                'subMenu' => [
                    [
                        'icon' => 'fa fa-users',
                        'nameSubMenu' => 'Listar',
                        'link' => 'https://localhost/api/v1/auth/users',
                    ],
                    [
                        'icon' => 'fa fa-users',
                        'nameSubMenu' => 'Cadastrar',
                        'link' => 'https://localhost/api/v1/auth/users',
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
     * Lista todas as ações de acordo com o perfil do usuário
     */
    public function profile()
    {
        // verifica quais ações que usuário autenticado tem permissão de usar,
        // e monta a estrutura da resposta, do contrário retorna null
        $canAccessMenu = function (Model $model, User $user, array $abilities, string $icon)
        {
            return array_map(
                function ($keyAbility, $valueAbility) use ($model, $user, $icon): ?array
                {
                    // caso o usuário não tem permissão,
                    // será retornado null como valor do subMenu
                    if ($user->cannot($keyAbility, $model::class))
                        return null;

                    // retorna a estrutura da resposta do subMenu,
                    // indicando que o usuário tem permissão para tal ação
                    return [
                        'icon' => $icon,
                        'nameSubMenu' => $valueAbility,
                        'link' => url('api/v1/auth/'.$model->getTable()),
                    ];
                },
                array_keys($abilities),
                $abilities
            );
        };

        $user = Auth::guard('api')->user();
        $profile = $user->profile;

        // array de ações usadas pelo usuário caso tenha permissão
        $abilities = [
            'viewAny' => 'Listar',
            'create' => 'Cadastrar',
        ];

        // array de todos possíveis menus do sidebar do usuário
        $sidebar = [
            [
                'model' => $user,
                'icon' => 'fa fa-users',
                'nameMenu' => 'Usuário',
                'subMenu' => [],
            ],
            [
                'model' => $profile,
                'icon' => 'fa fa-profiles',
                'nameMenu' => 'Perfil',
                'subMenu' => [],
            ],
        ];

        // remove do array subMenus valores nulos
        foreach ($sidebar as &$menu) {
            $menu['subMenu'] = $canAccessMenu($menu['model'], $user, $abilities, $menu['icon']);
            $menu['subMenu'] = array_filter($menu['subMenu']);
        }
        unset($menu);

        $sidebar = array_filter($sidebar, function ($menu) {
            return (bool) count($menu['subMenu']);
        });

        // verifica se o usuário tem alguma permissão
        if (count($sidebar) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado.',
                'status_code' => 403,
                'data' => null,
            ], 403);
        }

        // retorna uma resposta com apenas os campos importantes para o frontend
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'data' => array_map(
                fn ($item) =>  [
                    'icon' => $item['icon'],
                    'nameMenu' => $item['nameMenu'],
                    'subMenu' => $item['subMenu'],
                ],
                $sidebar
            ),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), fn ($q) =>
                $q->where('name', 'like' , "%{$request->search}%"))
            ->orderBy($request->input('sort', 'name'), $request->input('order', 'desc'))
            ->paginate($request->input('per_page', 10));

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_id' => $request->profile_id,
                'secretariat_id' => $request->secretariat_id,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao cadastrar o usuário. Por favor, tente novamente.',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso!',
            'data' => $user->toResource()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->toResource(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $status = $user->update($request->all());

        return $status ?
            response()->json([
                'success' => true,
                'message' => 'Dados atualizados com sucesso',
                'data' => $user->toResource()
            ]) :
            response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao atualizar os dados.',
                'data' => null
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Usuário removido com sucesso!',
            'data' => $user->toResource(),
        ]);
    }
}
