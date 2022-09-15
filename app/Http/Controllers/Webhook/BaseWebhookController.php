<?php

namespace App\Http\Controllers\Webhook;

use Illuminate\Http\Response;

class BaseWebhookController extends \App\Http\Controllers\Controller
{
    private function _result(array $additionaData, int $code)
    {
        return response()->json($additionaData, $code);
    }
    
    public function success(array $additionaData = [], int $code = Response::HTTP_OK, string $status = "success")
    {
        return $this->_result($additionaData, $code, $status);
    }

    public function error(array $additionaData = [], int $code = Response::HTTP_BAD_REQUEST, string $status = "error")
    {
        return $this->_result($additionaData, $code, $status);
    }
}
