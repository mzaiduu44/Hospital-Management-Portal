<?php

if (!function_exists('apiResponse')) {
    /**
     * Create a standardized API response.
     *
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    function apiResponse(bool $success, string $message, $data = null, int $statusCode = 200)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('apiSuccess')) {
    /**
     * Create a successful API response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    function apiSuccess(string $message = 'Success', $data = null, int $statusCode = 200)
    {
        return apiResponse(true, $message, $data, $statusCode);
    }
}

if (!function_exists('apiError')) {
    /**
     * Create an error API response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    function apiError(string $message = 'Error', $data = null, int $statusCode = 400)
    {
        return apiResponse(false, $message, $data, $statusCode);
    }
}

if (!function_exists('apiValidationError')) {
    /**
     * Create a validation error API response.
     *
     * @param mixed $errors
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    function apiValidationError($errors, string $message = 'Validation failed')
    {
        return apiResponse(false, $message, ['errors' => $errors], 422);
    }
}

if (!function_exists('apiNotFound')) {
    /**
     * Create a not found API response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    function apiNotFound(string $message = 'Resource not found')
    {
        return apiResponse(false, $message, null, 404);
    }
}

if (!function_exists('apiUnauthorized')) {
    /**
     * Create an unauthorized API response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    function apiUnauthorized(string $message = 'Unauthorized')
    {
        return apiResponse(false, $message, null, 401);
    }
}
