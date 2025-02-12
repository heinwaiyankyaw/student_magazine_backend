<?php
namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    public function successResponse($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Error response
     */
    public function errorResponse($message, $status = 400, $errors = [])
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
