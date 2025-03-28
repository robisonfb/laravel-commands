<?php

namespace App\Traits;

use App\Utils\ResponseUtil;
use Illuminate\Support\Facades\Response;

trait GenericApiService
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }
    public function sendResponsePagination($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, [
            'data'       => $result->items(),
            'pagination' => [
                'total'        => $result->total(),
                'per_page'     => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'from'         => $result->firstItem(),
                'to'           => $result->lastItem(),
            ],
        ]));
    }

    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }
}
