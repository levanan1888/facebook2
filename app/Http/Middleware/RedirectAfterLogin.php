<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAfterLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Kiểm tra nếu user vừa đăng nhập và đang ở trang dashboard
        if (Auth::check() && $request->is('dashboard')) {
            // Redirect về trang facebook/overview thay vì dashboard
            return redirect()->route('facebook.overview');
        }

        return $response;
    }
}
