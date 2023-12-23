<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CustomBaseException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $message = $this->message . ($this->code > 0 ? ' (code: ' . $this->code . ')' : '');
    
        if ($request->wantsJson()) {
            return $this->_result(['message' => $message], Response::HTTP_BAD_REQUEST);
        } else {
            return back()->withInput()->with('error', $message);
        }
    }

    private function _result(array $additionaData, int $code)
    {
        return response()->json($additionaData, $code);
    }
}
