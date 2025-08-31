<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseControllerClass;
use Illuminate\Http\JsonResponse;

class BaseController extends BaseControllerClass
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Trả về response thành công
     */
    protected function successResponse($data = null, string $message = 'Thành công', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Trả về response lỗi
     */
    protected function errorResponse(string $message = 'Có lỗi xảy ra', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Trả về response không tìm thấy
     */
    protected function notFoundResponse(string $message = 'Không tìm thấy dữ liệu'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Trả về response không có quyền
     */
    protected function forbiddenResponse(string $message = 'Bạn không có quyền truy cập'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Trả về response lỗi server
     */
    protected function serverErrorResponse(string $message = 'Lỗi server'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }
}
