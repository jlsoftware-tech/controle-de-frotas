<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Secretariat;
use App\Notifications\ResetPasswordApiNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário ou senha incorretos.',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => 'Login realizado com sucesso.',
            'data' => Auth::guard('api')->user()->toArray(),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        try {
            $profile = Profile::findOrFail($request->profile_id);
            $secretariat = Secretariat::findOrFail($request->secretariat_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: '.$e->getMessage(),
                'data' => null,
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_id' => $profile->id,
            'secretariat_id' => $secretariat->id,
        ]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar.',
                'data' => null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        Auth::guard('api')->login($user);
        $token = Auth::guard('api')->tokenById($user->id);
        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => 'Cadastro realizado com sucesso.',
            'data' => Auth::guard('api')->user(),
        ], Response::HTTP_CREATED);
    }

    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.',
            'data' => null,
        ]);
    }

    public function refresh()
    {
        return [
            'success' => true,
            'token' => Auth::guard('api')->refresh(),
            'data' => Auth::guard('api')->user()
        ];
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Auth::guard('api')->user()
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email'),
            function (User $user, $token) {
                $user->notify(new ResetPasswordApiNotification($token));
            }
        );

        return $status === Password::RESET_LINK_SENT ?
            response()->json([
                'success' => true,
                'message' => 'Link enviado com sucesso.'
            ]) :
            response()->json([
                'success' => false,
                'message' => 'Não foi possível enviar o link.'
            ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|string|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($request) {
                $user->forceFill(['password' => Hash::make($password)])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET ?
            response()->json([
                'success' => true,
                'message' => 'Sua senha foi redefinida com sucesso.'
            ]) :
            response()->json([
                'success' => false,
                'message' => 'Token inválido ou expirado.'
            ]);
    }
}
