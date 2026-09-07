<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Sucesso.', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success'     => true,
            'status_code' => $statusCode,
            'message'     => $message,
            'data'        => $data,
        ], $statusCode);
    }

    protected function error(string $message = 'Erro.', int $statusCode = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success'     => false,
            'status_code' => $statusCode,
            'message'     => $message,
            'data'        => $data,
        ], $statusCode);
    }
}
