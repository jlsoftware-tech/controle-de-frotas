<?php

namespace App\Http\Middleware;

use App\Support\ApiResponder;
use Closure;
use Illuminate\Http\Request;
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

            // Registra o usuário no guard 'api' e no resolver da requisição
            //Auth::guard('api')->setUser($user);
            //$request->setUserResolver(fn () => $user);

        } catch (TokenExpiredException) {
            return ApiResponder::error('Token expirado', Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException) {
            return ApiResponder::error('Token inválido', Response::HTTP_UNAUTHORIZED);
        } catch (JWTException) {
            return ApiResponder::error('Não autenticado', Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
