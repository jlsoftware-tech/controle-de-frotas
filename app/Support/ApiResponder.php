<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponder
{
    public static function success(mixed $data = null, string $message = 'Sucesso.', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success'     => true,
            'status_code' => $statusCode,
            'message'     => $message,
            'data'        => $data,
        ], $statusCode, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function error(string $message = 'Erro.', int $statusCode = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success'     => false,
            'status_code' => $statusCode,
            'message'     => $message,
            'data'        => $data,
        ], $statusCode, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
