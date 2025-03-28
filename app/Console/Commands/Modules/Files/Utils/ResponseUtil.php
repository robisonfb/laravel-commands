<?php

namespace App\Utils;

class ResponseUtil
{
    public static function makeResponse($message, $data)
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];
    }

    public static function makeError($error)
    {
        return [
            'success' => false,
            'message' => $error,
        ];
    }
}
