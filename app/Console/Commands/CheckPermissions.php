<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class CheckPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và tạo permission cần thiết';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Kiểm tra permission system...');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Kiểm tra permissions cần thiết
        $requiredPermissions = [
            'facebook.overview',
            'facebook.hierarchy',
            'facebook.analytics',
            'facebook.data_raw',
            'analytics.filter',
            'dashboard.analytics',
        ];

        $this->info('📋 Kiểm tra permissions cần thiết...');
        foreach ($requiredPermissions as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            if ($perm->wasRecentlyCreated) {
                $this->line("✅ Tạo permission: {$permission}");
            } else {
                $this->line("ℹ️  Permission đã tồn tại: {$permission}");
            }
        }

        // Kiểm tra roles
        $this->info('👥 Kiểm tra roles...');
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        // Gán permissions cho Super Admin
        $superAdmin->givePermissionTo(Permission::all());
        $this->line("✅ Super Admin có tất cả permissions");

        // Gán permissions cho Admin
        $admin->syncPermissions(Permission::all());
        $this->line("✅ Admin có tất cả permissions");

        // Gán permissions cơ bản cho User
        $user->givePermissionTo([
            'dashboard.view',
            'facebook.overview',
            'view-facebook-data',
        ]);
        $this->line("✅ User có permissions cơ bản");

        // Kiểm tra user admin
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
            $this->line("✅ Tạo user admin: admin@example.com / password");
        }

        if (!$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole('Super Admin');
            $this->line("✅ Gán role Super Admin cho user admin");
        }

        $this->info('🎉 Permission system đã được kiểm tra và cập nhật!');
        return Command::SUCCESS;
    }
}
