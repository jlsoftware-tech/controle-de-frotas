<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'data' => null,
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Registra o usuário no guard 'api' e no resolver da requisição
            //Auth::guard('api')->setUser($user);
            //$request->setUserResolver(fn () => $user);

        } catch (TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token expirado.',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido.',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $next($request);
    }
}
