<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Lấy danh sách quyền của role
     */
    public function getPermissionsListAttribute()
    {
        return $this->permissions->pluck('name')->toArray();
    }

    /**
     * Kiểm tra role có quyền cụ thể không
     */
    public function hasPermission($permission): bool
    {
        return $this->hasPermissionTo($permission);
    }
}

