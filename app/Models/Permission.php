<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Lấy danh sách roles có quyền này
     */
    public function getRolesListAttribute()
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Kiểm tra quyền có được gán cho role cụ thể không
     */
    public function isAssignedToRole($role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }
}

