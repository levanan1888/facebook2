<x-layouts.app.sidebar title="Quản lý Permissions">
    <flux:main>
        <div class="p-6">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 text-gray-900">Quản lý Hệ thống</h1>
                        <p class="mt-2 text-lg text-gray-600 text-gray-600">Quản lý người dùng và phân quyền trong hệ thống</p>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ route('admin.users.index') }}" 
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('admin.users.*') ? 'border-blue-500 text-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-gray-600 hover:text-gray-700' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Người dùng
                        </div>
                    </a>
                    <a href="{{ route('admin.roles.index') }}" 
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('admin.roles.*') ? 'border-blue-500 text-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-gray-600 hover:text-gray-700' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Vai trò
                        </div>
                    </a>
                    <a href="{{ route('admin.permissions.index') }}" 
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('admin.permissions.*') ? 'border-blue-500 text-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-gray-600 hover:text-gray-700' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Quyền hạn
                        </div>
                    </a>
                    <a href="{{ route('admin.login-activities.index') }}" 
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('admin.login-activities.*') ? 'border-blue-500 text-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-gray-600 hover:text-gray-700' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Hoạt động đăng nhập
                        </div>
                    </a>
                </nav>
            </div>

            <!-- Action Buttons -->
            <div class="mb-6 flex justify-end">
                <a href="{{ route('admin.permissions.create') }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                    Tạo Permission
                </a>
            </div>

            <!-- Stats Cards - Cải thiện layout -->
            <div class="flex flex-wrap gap-4 mb-8">
                <div class="w-full sm:w-1/2 lg:w-1/4">
                    <div class="bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 h-full hover:shadow-lg transition-shadow duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="p-3 bg-green-100 bg-green-100 rounded-xl mb-3">
                                <svg class="w-8 h-8 text-green-600 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Tổng Permissions</p>
                            <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $permissions->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 lg:w-1/4">
                    <div class="bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 h-full hover:shadow-lg transition-shadow duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="p-3 bg-blue-100 bg-blue-100 rounded-xl mb-3">
                                <svg class="w-8 h-8 text-blue-600 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Đã gán Role</p>
                            <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $permissions->where('roles_count', '>', 0)->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 lg:w-1/4">
                    <div class="bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 h-full hover:shadow-lg transition-shadow duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="p-3 bg-yellow-100 bg-yellow-100 rounded-xl mb-3">
                                <svg class="w-8 h-8 text-yellow-600 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Chưa gán Role</p>
                            <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $permissions->where('roles_count', 0)->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 lg:w-1/4">
                    <div class="bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 h-full hover:shadow-lg transition-shadow duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="p-3 bg-purple-100 bg-purple-100 rounded-xl mb-3">
                                <svg class="w-8 h-8 text-purple-600 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Sử dụng nhiều nhất</p>
                            <p class="text-lg font-bold text-gray-900 text-gray-900">{{ $permissions->sortByDesc('roles_count')->first()?->name ?? 'Không có' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Permissions Table - Cải thiện giao diện -->
            <div class="bg-white bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200 border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 border-gray-200 bg-gray-50 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 text-gray-900">Danh sách Permissions</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500 text-gray-600">Hiển thị {{ $permissions->count() }} / {{ $permissions->total() }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 divide-gray-200">
                        <thead class="bg-gray-50 bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Thông tin Permission
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Roles được gán
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Thống kê sử dụng
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white bg-white divide-y divide-gray-200 divide-gray-200">
                            @foreach($permissions as $permission)
                            <tr class="hover:bg-gray-50 hover:bg-gray-100 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if(str_contains($permission->name, 'create'))
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @elseif(str_contains($permission->name, 'edit') || str_contains($permission->name, 'update'))
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                    </svg>
                                                </div>
                                            @elseif(str_contains($permission->name, 'delete') || str_contains($permission->name, 'destroy'))
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center shadow-lg">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @elseif(str_contains($permission->name, 'view'))
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shadow-lg">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center shadow-lg">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 text-gray-900">{{ $permission->name }}</div>
                                            <div class="text-sm text-gray-500 text-gray-600">ID: {{ $permission->id }}</div>
                                            <div class="text-xs text-gray-400 text-gray-400">Tạo: {{ $permission->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        @if($permission->roles->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($permission->roles->take(3) as $role)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 bg-green-100 text-green-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                        {{ $role->name }}
                                                    </span>
                                                @endforeach
                                                @if($permission->roles->count() > 3)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 bg-gray-50 text-gray-800">
                                                        +{{ $permission->roles->count() - 3 }} khác
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 text-gray-600">
                                                {{ $permission->roles->count() }} roles đã gán
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 text-gray-600">Chưa được gán</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-900 text-gray-900">{{ $permission->roles->count() }} roles</span>
                                        </div>
                                        @if($permission->roles->count() > 0)
                                            @php
                                                $totalUsers = $permission->roles->sum(function($role) {
                                                    return $role->users()->count();
                                                });
                                            @endphp
                                            <div class="text-xs text-gray-500 text-gray-600">
                                                {{ $totalUsers }} người dùng tổng cộng
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                           class="inline-flex items-center px-3 py-2 bg-blue-100 hover:bg-blue-200 bg-blue-100 hover:bg-blue-200 text-blue-700 dark:text-blue-300 rounded-lg transition-colors duration-150 font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Sửa
                                        </a>
                                        
                                        @if($permission->roles()->count() == 0)
                                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 bg-red-100 hover:bg-red-200 text-red-700 dark:text-red-300 rounded-lg transition-colors duration-150 font-medium"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa permission này?')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Xóa
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center px-3 py-2 bg-gray-100 bg-gray-50 text-gray-500 text-gray-600 rounded-lg text-sm font-medium">
                                                Đang sử dụng
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($permissions->hasPages())
                <div class="mt-8">
                    {{ $permissions->links() }}
                </div>
            @endif
        </div>
    </flux:main>
</x-layouts.app.sidebar>
