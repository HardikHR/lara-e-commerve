<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    public function sendResponse($result)
    {
        return response()->json(['data' => $result], 200);
    }

    public function sendError($errorMessages = [], $code = 404)
    {
        $response['data'] = $errorMessages;
        return response()->json($response, $code);
    }
}
