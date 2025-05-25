<?php

namespace App\Trait;

trait HttpResponses
{
    protected function success($data, $message = null, $statusCode = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'version' => config('app.version'),
            ],
        ], $statusCode);
    }

    protected function error($data, $message = null, $statusCode)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'version' => config('app.version'),
            ],
        ], $statusCode);
    }
}
