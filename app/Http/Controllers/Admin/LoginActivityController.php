<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use Illuminate\Http\Request;

class LoginActivityController extends Controller
{
    /**
     * Hiển thị danh sách login activities
     */
    public function index(Request $request)
    {
        $query = LoginActivity::with('user')
            ->orderBy('created_at', 'desc');

        // Filter theo user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter theo action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter theo ngày
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $loginActivities = $query->paginate(20);

        // Thống kê
        $stats = [
            'total_logins' => LoginActivity::where('action', 'login')->count(),
            'total_failed' => LoginActivity::where('action', 'failed')->count(),
            'today_logins' => LoginActivity::where('action', 'login')
                ->whereDate('created_at', today())->count(),
            'unique_users_today' => LoginActivity::where('action', 'login')
                ->whereDate('created_at', today())
                ->distinct('user_id')->count(),
        ];

        return view('admin.login-activities.index', compact('loginActivities', 'stats'));
    }

    /**
     * Hiển thị chi tiết login activity
     */
    public function show(LoginActivity $loginActivity)
    {
        return view('admin.login-activities.show', compact('loginActivity'));
    }

    /**
     * Xóa login activity
     */
    public function destroy(LoginActivity $loginActivity)
    {
        $loginActivity->delete();

        return redirect()->route('admin.login-activities.index')
            ->with('success', 'Đã xóa hoạt động đăng nhập thành công.');
    }

    /**
     * Xóa tất cả login activities cũ
     */
    public function clearOld(Request $request)
    {
        $days = $request->get('days', 30);
        
        LoginActivity::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.login-activities.index')
            ->with('success', "Đã xóa tất cả hoạt động đăng nhập cũ hơn {$days} ngày.");
    }
}
