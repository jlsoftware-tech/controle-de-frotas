<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Secretariat;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
                'message' => 'Erro ao logar.',
                'data' => null,
            ]);
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
            ]);
        }

        $token = Auth::guard('api')->tokenById($user->id);
        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => 'Cadastro realizado com sucesso.',
            'data' => ['user' => auth()->user()],
        ]);
    }
}
