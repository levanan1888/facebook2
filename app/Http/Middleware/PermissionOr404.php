<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionOr404
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user() || ! $request->user()->can($permission)) {
            // Nếu là API request, trả về 403 JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập',
                    'error' => 'permission_denied'
                ], 403);
            }
            
            // Nếu là web request, redirect về trang chính
            return redirect()->route('facebook.overview')->with('error', 'Không có quyền truy cập');
        }

        return $next($request);
    }
}
