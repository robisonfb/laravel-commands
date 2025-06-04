<?php

namespace App\Trait;

trait HttpResponses
{
    /**
     * Return a success JSON response
     */
    protected function success($data, string $message = null, int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'version' => config('app.version', '1.0.0'),
                'timestamp' => now()->toISOString(),
            ],
        ], $statusCode);
    }

    /**
     * Return an error JSON response
     */
    protected function error($data, string $message = null, int $statusCode = 500): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'version' => config('app.version', '1.0.0'),
                'timestamp' => now()->toISOString(),
            ],
        ], $statusCode);
    }

    /**
     * Return a validation error response (422)
     */
    protected function validationError($errors, string $message = 'Validation failed'): \Illuminate\Http\JsonResponse
    {
        return $this->error($errors, $message, 422);
    }

    /**
     * Return a not found error response (404)
     */
    protected function notFound(string $message = 'Resource not found'): \Illuminate\Http\JsonResponse
    {
        return $this->error([], $message, 404);
    }

    /**
     * Return a created response (201)
     */
    protected function created($data, string $message = 'Resource created successfully'): \Illuminate\Http\JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a no content response (204) - seguindo seu padrão
     */
    protected function deleted(string $message = 'Resource deleted successfully'): \Illuminate\Http\JsonResponse
    {
        return $this->success(null, $message, 204);
    }
}
