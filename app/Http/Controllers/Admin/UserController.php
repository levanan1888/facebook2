<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách users
     */
    public function index()
    {
        $users = User::with('roles')
            ->withCount('roles')
            ->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị form tạo user mới
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Lưu user mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User đã được tạo thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Cập nhật user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        // Sync roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User đã được cập nhật thành công.');
    }

    /**
     * Xóa user
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa chính mình.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User đã được xóa thành công.');
    }
}
