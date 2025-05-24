<?php

namespace App\Trait;

use App\Enums\HttpResponseStatus;

trait HttpResponses
{
    protected function success($data, $message = null, $statusCode = 200)
    {
        return response()->json([
            "status"  => HttpResponseStatus::SUCCESS,
            "message" => $message,
            "data"    => $data,
        ], $statusCode); // status code as second argument
    }

    protected function error($data, $message = null, $statusCode)
    {
        return response()->json([
            "status"  => HttpResponseStatus::ERROR,
            "message" => $message,
            "data"    => $data,
        ], $statusCode); // status code as second argument
    }
}
