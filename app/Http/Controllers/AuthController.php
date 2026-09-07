<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\Profile;
use App\Models\Secretariat;
use App\Models\User;
use App\Notifications\ResetPasswordApiNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use Symfony\Component\HttpFoundation\Response;

#[Group('Autenticação', description: 'Endpoints para gerenciamento de autenticação, cadastro e recuperação de senha de usuários.')]
class AuthController extends Controller
{
    #[Endpoint('Login', description: 'Autentica um usuário existente com e-mail e senha, retornando o token JWT e as informações do usuário.')]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => 'Login realizado com sucesso.',
            'data' => [
                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
                'user' => [
                    'id' => 1,
                    'name' => 'João Silva',
                    'email' => 'joao.silva@example.com',
                    'profile_id' => 1,
                    'secretariat_id' => 1,
                    'created_at' => '03/09/2026 19:13:32',
                    'updated_at' => '03/09/2026 19:13:32',
                ]
            ],
        ],
        status: Response::HTTP_OK,
        description: 'Login realizado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => Response::HTTP_UNAUTHORIZED,
            'message' => 'Usuário ou senha incorretos.',
            'data' => null,
        ],
        status: Response::HTTP_UNAUTHORIZED,
        description: 'Credenciais inválidas.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'The email field is required.',
            'errors' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ],
        status: 422,
        description: 'Erro de validação nos campos informados.'
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::guard('api')->attempt($credentials);
        if (! $token) {
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Usuário ou senha incorretos.',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => 'Login realizado com sucesso.',
            'data' => [
                'token' => $token,
                'user' => Auth::guard('api')->user()->toResource()
            ],
        ]);
    }

    #[Endpoint('Logout', description: 'Realiza o logout do usuário invalidando o token JWT atual.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => 'Logout realizado com sucesso.',
            'data' => null,
        ],
        status: Response::HTTP_OK,
        description: 'Logout realizado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'Unauthenticated.',
        ],
        status: Response::HTTP_UNAUTHORIZED,
        description: 'Token de autenticação não fornecido ou inválido.'
    )]
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        try {
            JWTAuth::parseToken()->invalidate(true);
        } catch (JWTException $e) {
            response()->json([
                'success' => false,
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro ao encerrar sua sessão. Tente novamente ou contate o administrador.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => 'Logout realizado com sucesso.',
            'data' => null,
        ]);
    }

    #[Endpoint('Renovar Token', description: 'Renova o token JWT de autenticação atual e retorna um novo token.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => [
                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
                'user' => [
                    'id' => 1,
                    'name' => 'João Silva',
                    'email' => 'joao.silva@example.com',
                    'profile_id' => 1,
                    'secretariat_id' => 1,
                    'created_at' => '03/09/2026 19:13:32',
                    'updated_at' => '03/09/2026 19:13:32',
                ],
            ],
        ],
        status: Response::HTTP_OK,
        description: 'Token renovado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'Unauthenticated.',
        ],
        status: Response::HTTP_UNAUTHORIZED,
        description: 'Token não fornecido ou inválido para renovação.'
    )]
    public function refresh()
    {
        try {
            $token = Auth::guard('api')->refresh();
        } catch (JWTException) {
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Sua sessão expirou.',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Define o novo token do usuário
        // Se não definir, o usuário continuaria com o token invalidado pelo refresh()
        Auth::guard('api')->setToken($token)->authenticate();

        return response()->json([
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => [
                'token' => $token,
                'user' => Auth::guard('api')->user(),
            ],
        ]);
    }

    #[Endpoint('Dados do Usuário Autenticado', description: 'Retorna os dados do usuário atualmente autenticado.', authenticated: true)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'João Silva',
                'email' => 'joao.silva@example.com',
                'profile_id' => 1,
                'secretariat_id' => 1,
                'created_at' => '05/09/2026 10:22',
                'updated_at' => '05/09/2026 10:22',
            ],
        ],
        status: Response::HTTP_OK,
        description: 'Dados do usuário autenticado recuperados com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'Unauthenticated.',
        ],
        status: Response::HTTP_UNAUTHORIZED,
        description: 'Token não fornecido ou inválido.'
    )]
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Auth::guard('api')->user(),
        ]);
    }

    #[Endpoint('Esqueci minha senha', description: 'Envia um e-mail com instruções e token para redefinição de senha.')]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => 'Link de recuperação de senha enviado para seu E-mail.',
            'data' => null,
        ],
        status: Response::HTTP_OK,
        description: 'E-mail de recuperação enviado com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => 'Erro ao enviar o link via E-mail. Tente novamente mais tarde ou contate o administrador.',
            'data' => null,
        ],
        status: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Falha ao processar o envio do link de recuperação.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'The email field is required.',
            'errors' => [
                'email' => ['The email field is required.'],
            ],
        ],
        status: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Erro de validação no e-mail informado.'
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email'),
            function (User $user, $token) {
                $user->notify(new ResetPasswordApiNotification($token));
            }
        );

        return $status === Password::RESET_LINK_SENT ?
            response()->json([
                'success' => true,
                'status_code' => Response::HTTP_OK,
                'message' => 'Link de recuperação de senha enviado para seu E-mail.',
                'data' => null,
            ]) :
            response()->json([
                'success' => false,
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Erro ao enviar o link via E-mail. Tente novamente mais tarde ou contate o administrador.',
                'data' => null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Endpoint('Redefinir Senha', description: 'Redefine a senha do usuário utilizando o token recebido por e-mail.', authenticated: false)]
    #[ResponseAtt(
        content: [
            'success' => true,
            'status_code' => Response::HTTP_OK,
            'message' => 'Sua senha foi redefinida com sucesso.',
            'data' => null,
        ],
        status: Response::HTTP_OK,
        description: 'Senha redefinida com sucesso.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => Response::HTTP_UNAUTHORIZED,
            'message' => 'Tempo limite atingido para redefinir sua senha. Tente novamente ou contate o administrador.',
            'data' => null,
        ],
        status: Response::HTTP_UNAUTHORIZED,
        description: 'Token inválido ou expirado.'
    )]
    #[ResponseAtt(
        content: [
            'message' => 'The password field confirmation does not match.',
            'errors' => [
                'password' => ['The password field confirmation does not match.'],
            ],
        ],
        status: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Erro de validação nos campos informados.'
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET ?
            response()->json([
                'success' => true,
                'status_code' => Response::HTTP_OK,
                'message' => 'Sua senha foi redefinida com sucesso.',
                'data' => null,
            ]) :
            response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Tempo limite atingido para redefinir sua senha. Tente novamente ou contate o administrador.',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
    }
}
