<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Hiển thị danh sách permissions
     */
    public function index()
    {
        $permissions = Permission::with('roles')
            ->withCount('roles')
            ->paginate(15);
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Hiển thị form tạo permission mới
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.permissions.create', compact('roles'));
    }

    /**
     * Lưu permission mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'roles' => 'array',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            foreach ($roles as $role) {
                $role->givePermissionTo($permission);
            }
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission đã được tạo thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa permission
     */
    public function edit(Permission $permission)
    {
        $roles = Role::all();
        $permissionRoles = $permission->roles->pluck('id')->toArray();
        
        return view('admin.permissions.edit', compact('permission', 'roles', 'permissionRoles'));
    }

    /**
     * Cập nhật permission
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'roles' => 'array',
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        // Sync roles
        $roles = Role::all();
        foreach ($roles as $role) {
            if (in_array($role->id, $request->roles ?? [])) {
                $role->givePermissionTo($permission);
            } else {
                $role->revokePermissionTo($permission);
            }
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission đã được cập nhật thành công.');
    }

    /**
     * Xóa permission
     */
    public function destroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Không thể xóa permission đang được sử dụng bởi roles.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission đã được xóa thành công.');
    }
}
