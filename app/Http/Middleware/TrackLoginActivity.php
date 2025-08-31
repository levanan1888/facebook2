<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginActivity;
use Symfony\Component\HttpFoundation\Response;

class TrackLoginActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Chỉ track khi user đã đăng nhập
        if (Auth::check()) {
            $user = Auth::user();
            
            // Ghi lại hoạt động đăng nhập
            LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => $this->getLocation($request->ip()),
                'action' => 'login',
                'logged_in_at' => now(),
            ]);
        }

        return $response;
    }

    /**
     * Lấy thông tin vị trí từ IP (có thể tích hợp với service bên ngoài)
     */
    private function getLocation($ip)
    {
        // Đây là placeholder, bạn có thể tích hợp với IP Geolocation service
        // Ví dụ: ipapi.co, ipinfo.io, hoặc MaxMind
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }
        
        return 'Unknown';
    }
}
