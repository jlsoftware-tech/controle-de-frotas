<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Usuários', description: 'Endpoints para gerenciamento de usuários do sistema.')]
class UserController extends Controller
{
    // trait para tratamento dos erros de validação
    use ApiResponse;


    /**
     * Display a listing of the resource.
     */
    #[Endpoint('Listar Usuários', description: 'Retorna uma lista paginada de usuários cadastrados no sistema com opções de busca, ordenação e paginação.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'statusCode' => 200,
            'data' => [
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
            return response()->json([
                'success' => false,
                'statusCode' => 400,
                'data' => null,
                'pagination' => null,
            ], 400);
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
            return $this->error(
                'Ocorreu um erro ao cadastrar o usuário. Por favor, tente novamente.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->success(
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
        return $this->success(
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

        return $status ?
            $this->success(
                $user->toResource(),
                'Dados atualizados com sucesso.',
                Response::HTTP_OK,
            ):
            $this->error(
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
        return $this->success(
            message: 'Usuário removido com sucesso.',
            statusCode: Response::HTTP_OK,
        );
    }
}
