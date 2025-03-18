<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class BaseResponse
{
    public static function success(string $message, $data = null): JsonResponse
    {
        $response = [
            'message' => $message,
            'status' => 200,
            'data' => $data,
        ];

        return response()->json($response, 200);
    }



    public static function error(string $message, int $statusCode, string $error): JsonResponse
    {
        return response()->json([
            "message" => $message,
            'status' => $statusCode,
            "error" => $error
        ], $statusCode);
    }
}
