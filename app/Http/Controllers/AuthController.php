<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordApiNotification;
use App\Support\ApiResponder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAtt;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

#[Group('Autenticação', description: 'Endpoints para gerenciamento de autenticação, cadastro e recuperação de senha de usuários.')]
class AuthController extends Controller
{
    /**
     * Authenticate the user and issue a JWT token.
     */
    #[Endpoint(
        'Login',
        description: 'Autentica um usuário existente com e-mail e senha, retornando o token JWT e as informações do usuário. O token expira em 1 dia (padrão) ou em 7 dias quando `remember` é `true`. Deve ser enviado nas demais rotas no cabeçalho `Authorization: Bearer {token}`.',
        authenticated: false
    )]
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
                    'profile' => [
                        'id' => 1,
                        'name' => 'Perfil de teste',
                    ],
                    'secretariat' => [
                        'id' => 1,
                        'name' => 'Secretaria',
                        'acronym' => 'AJS',
                    ],
                    'created_at' => '03/09/2026 19:13:32',
                    'updated_at' => '03/09/2026 19:13:32',
                    'deleted_at' => null,
                ],
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
            'success' => false,
            'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'email' => ['O campo e-mail é obrigatório.'],
                'password' => ['O campo senha é obrigatório.'],
            ],
        ],
        status: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Erro de validação nos campos informados. `data` é indexado pelo nome do campo.'
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = (bool) $request->input('remember', false);

        // define o ttl do token conforme a checkbox lembrar-me
        if ($remember) {
            // define o ttl do token para 7 dias
            $token = Auth::guard('api')->setTTL(10080)->attempt($credentials);
        } else {
            // define o ttl do token com o valor padrão de 1 dia
            $token = Auth::guard('api')->attempt($credentials);
        }

        if (! $token) {
            return ApiResponder::error('Usuário ou senha incorretos.', Response::HTTP_UNAUTHORIZED);
        }

        return ApiResponder::success([
            'token' => $token,
            'user' => Auth::guard('api')->user()->toResource(),
        ],
            'Login realizado com sucesso.',
        );
    }

    /**
     * End the user's session by invalidating the current JWT token.
     */
    #[Endpoint('Logout', description: 'Realiza o logout do usuário invalidando o token JWT atual (o token é colocado na blacklist e não pode mais ser utilizado). A rota responde ao método `GET`.', authenticated: true)]
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
            'success' => false,
            'status_code' => Response::HTTP_UNAUTHORIZED,
            'message' => 'Token expirado',
            'data' => null,
        ],
        status: Response::HTTP_UNAUTHORIZED,
        description: 'Token não fornecido (`Não autenticado`), inválido (`Token inválido`) ou expirado (`Token expirado`).'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => 'Ocorreu algum erro ao encerrar sua sessão. Tente novamente ou contate o administrador.',
            'data' => null,
        ],
        status: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Falha ao invalidar o token.'
    )]
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        try {
            JWTAuth::parseToken()->invalidate(true);
        } catch (JWTException) {
            return ApiResponder::error(
                'Ocorreu algum erro ao encerrar sua sessão. Tente novamente ou contate o administrador.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponder::success(null, 'Logout realizado com sucesso.');
    }

    /**
     * Send the password recovery link by e-mail.
     */
    #[Endpoint(
        'Esqueci minha senha',
        description: 'Envia um e-mail com o token para redefinição de senha. O token deve ser utilizado em `POST /api/v1/auth/reset-password`. O mesmo erro 500 é retornado quando o e-mail não está cadastrado ou quando há muitas solicitações em sequência (throttle).',
        authenticated: false
    )]
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
            'success' => false,
            'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'email' => ['O campo e-mail é obrigatório.'],
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

        return $status === Password::RESET_LINK_SENT
            ? ApiResponder::success(
                null,
                'Link de recuperação de senha enviado para seu E-mail.')
            : ApiResponder::error(
                'Erro ao enviar o link via E-mail. Tente novamente mais tarde ou contate o administrador.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
    }

    /**
     * Reset the password using the token received by e-mail.
     */
    #[Endpoint('Redefinir Senha', description: 'Redefine a senha do usuário utilizando o token recebido por e-mail (ver `POST /api/v1/auth/forgot-password`). Não exige autenticação.', authenticated: false)]
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
        description: 'Token inválido ou expirado, ou e-mail que não corresponde ao token.'
    )]
    #[ResponseAtt(
        content: [
            'success' => false,
            'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Os dados enviados são inválidos.',
            'data' => [
                'password' => ['A confirmação do campo senha não confere.'],
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

        return $status === Password::PASSWORD_RESET
            ? ApiResponder::success(
                null,
                'Sua senha foi redefinida com sucesso.')
            : ApiResponder::error(
                'Tempo limite atingido para redefinir sua senha. Tente novamente ou contate o administrador.',
                Response::HTTP_UNAUTHORIZED
            );
    }
}
