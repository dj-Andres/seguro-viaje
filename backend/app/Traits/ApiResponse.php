<?php

namespace App\Traits;

use App\Support\ApiResponse as ApiResponseBuilder;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operación realizada con éxito.',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        return ApiResponseBuilder::success($data, $message, $status, $meta);
    }

    protected function errorResponse(
        string $message,
        int $status = 400,
        array $errors = [],
    ): JsonResponse {
        return ApiResponseBuilder::error($message, $status, $errors);
    }
}
