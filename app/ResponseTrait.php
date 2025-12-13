<?php

namespace App;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{

    public function successResponse($data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
       return response()->json([
        'message' => $message,

        'data' => $data],
        $status);
    }

    public function errorResponse(string $message = 'Error', int $status = 500, array $errors = []): JsonResponse
    {
        // avoids confusion of res.data.data in frontend
        return response()->json([
            'message' => $message,// user friendly
            'errors' => $errors// for devs => logging
        ], $status);

    }
}


