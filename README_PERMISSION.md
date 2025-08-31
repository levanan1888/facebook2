# Hệ thống Phân quyền với Spatie Laravel Permission

## Tổng quan

Hệ thống phân quyền này sử dụng package **Spatie Laravel Permission** để quản lý quyền truy cập theo từng chức năng và filter. Hệ thống bao gồm:

- **Roles**: Vai trò của user (Super Admin, Admin, Manager, User)
- **Permissions**: Quyền cụ thể cho từng chức năng
- **User Management**: Quản lý user và gán role
- **Middleware**: Kiểm tra quyền truy cập

## Cài đặt

### 1. Cài đặt package
```bash
composer require spatie/laravel-permission
```

### 2. Publish config và migration
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 3. Chạy migration
```bash
php artisan migrate
```

### 4. Chạy seeder
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 5. Tạo user admin
```bash
php artisan make:admin admin@example.com --name="Super Admin" --password="password123"
```

## Cấu trúc

### Roles mặc định
- **Super Admin**: Có tất cả quyền
- **Admin**: Quản lý user, role, permission, dashboard, settings
- **Manager**: Xem user, dashboard, settings
- **User**: Chỉ xem dashboard và settings

### Permissions mặc định
- **User Management**: `user.view`, `user.create`, `user.edit`, `user.delete`
- **Role Management**: `role.view`, `role.create`, `role.edit`, `role.delete`
- **Permission Management**: `permission.view`, `permission.create`, `permission.edit`, `permission.delete`
- **Dashboard**: `dashboard.view`, `dashboard.analytics`
- **Settings**: `settings.view`, `settings.edit`

## Sử dụng

### 1. Kiểm tra quyền trong Controller
```php
// Kiểm tra role
if (auth()->user()->hasRole('Admin')) {
    // Logic cho Admin
}

// Kiểm tra permission
if (auth()->user()->hasPermissionTo('user.create')) {
    // Logic tạo user
}

// Kiểm tra nhiều permission
if (auth()->user()->hasAnyPermission(['user.create', 'user.edit'])) {
    // Logic
}
```

### 2. Sử dụng Middleware
```php
// Trong routes
Route::middleware(['auth', 'permission:user.view'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

// Trong controller
public function __construct()
{
    $this->middleware('permission:user.create')->only(['create', 'store']);
    $this->middleware('permission:user.edit')->only(['edit', 'update']);
    $this->middleware('permission:user.delete')->only(['destroy']);
}
```

### 3. Sử dụng Blade Directives
```blade
{{-- Kiểm tra role --}}
@role('Admin')
    <div>Nội dung chỉ Admin thấy</div>
@endrole

{{-- Kiểm tra permission --}}
@permission('user.create')
    <button>Tạo User</button>
@endpermission

{{-- Kiểm tra nhiều role --}}
@anyrole(['Admin', 'Manager'])
    <div>Nội dung cho Admin hoặc Manager</div>
@endanyrole
```

### 4. Sử dụng Gate
```php
// Trong AppServiceProvider
Gate::define('manage-users', function ($user) {
    return $user->hasPermissionTo('user.manage');
});

// Trong view hoặc controller
if (Gate::allows('manage-users')) {
    // Logic
}
```

## Quản lý Admin

### Truy cập Admin Panel
- URL: `/admin/users`, `/admin/roles`, `/admin/permissions`
- Chỉ user có role Admin hoặc Super Admin mới truy cập được

### Tạo Role mới
1. Vào `/admin/roles/create`
2. Đặt tên role
3. Chọn permissions cần thiết
4. Lưu

### Tạo Permission mới
1. Vào `/admin/permissions/create`
2. Đặt tên permission (VD: `post.publish`)
3. Chọn roles sẽ có permission này
4. Lưu

### Gán Role cho User
1. Vào `/admin/users/edit/{id}`
2. Chọn roles cần thiết
3. Lưu

## Tùy chỉnh

### Thêm Permission mới
1. Tạo permission trong database
2. Cập nhật seeder nếu cần
3. Sử dụng trong controller/view

### Tạo Role mới
1. Tạo role trong database
2. Gán permissions phù hợp
3. Gán cho users cần thiết

### Custom Middleware
```php
// Tạo middleware mới
php artisan make:middleware CheckRole

// Sử dụng
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Routes chỉ Admin truy cập
});
```

## Bảo mật

- Luôn kiểm tra quyền ở cả Frontend và Backend
- Sử dụng middleware để bảo vệ routes
- Không expose sensitive permissions ra Frontend
- Log các hoạt động quan trọng

## Troubleshooting

### Lỗi thường gặp
1. **Permission không hoạt động**: Kiểm tra cache, chạy `php artisan cache:clear`
2. **Role không hiển thị**: Kiểm tra database, chạy lại seeder
3. **Middleware lỗi**: Kiểm tra tên permission/role có đúng không

### Debug
```php
// Kiểm tra user có gì
dd(auth()->user()->getAllPermissions());
dd(auth()->user()->getRoleNames());

// Kiểm tra permission có tồn tại không
dd(Permission::all());
dd(Role::all());
```

## Liên hệ

Nếu có vấn đề gì, hãy kiểm tra:
1. Log Laravel (`storage/logs/laravel.log`)
2. Database permissions và roles
3. Cache permissions
4. User roles và permissions

