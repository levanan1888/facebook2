<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Tạo permissions
        $permissions = [
            // User management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            
            // Role management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            
            // Permission management
            'permission.view',
            'permission.create',
            'permission.edit',
            'permission.delete',
            
            // Dashboard
            'dashboard.view',
            'dashboard.analytics',
            // Facebook sections
            'facebook.overview',
            'facebook.hierarchy',
            'facebook.analytics',
            'facebook.data_raw',
            'view-facebook-data',
            // Facebook hierarchy API access (for module-only API)
            'facebook.hierarchy.api',
            // Analytics filter UI permission
            'analytics.filter',
            // Filter fields granular
            'analytics.filter.time',
            'analytics.filter.scope',
            'analytics.filter.metrics',
            'analytics.filter.sort',
            // Analytics API granular
            'analytics.options',
            'analytics.summary',
            'analytics.breakdown',
            'analytics.series',
            'analytics.ad-details',
            // Facebook sync
            'facebook.sync',
            
            // Settings
            'settings.view',
            'settings.edit',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Tạo roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        // Gán permissions cho Super Admin (tất cả quyền)
        $superAdmin->givePermissionTo(Permission::all());

        // Gán full quyền cho Admin
        $admin->syncPermissions(Permission::all());

        // Gán permissions cho Manager
        $manager->givePermissionTo([
            'user.view', 'user.edit',
            'dashboard.view', 'dashboard.analytics',
            'facebook.overview', 'facebook.hierarchy', 'facebook.analytics', 'facebook.data_raw', 'view-facebook-data', 'facebook.hierarchy.api',
            'analytics.filter', 'analytics.filter.time', 'analytics.filter.scope', 'analytics.filter.metrics', 'analytics.filter.sort',
            'analytics.options', 'analytics.summary', 'analytics.breakdown', 'analytics.series', 'analytics.ad-details',
            'settings.view',
            // Quản lý có thể đồng bộ nếu cần
            'facebook.sync',
        ]);

        // Gán permissions cho User
        $user->givePermissionTo([
            'dashboard.view',
            'settings.view',
            'facebook.overview',
            'view-facebook-data',
            // Cho phép xem cấu trúc nếu cần (tùy chính sách):
            // 'facebook.hierarchy.api',
        ]);

        // Tạo user admin mặc định nếu chưa có
        $adminUser = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('password'),
        ]);

        $adminUser->assignRole('Super Admin');
    }
}
