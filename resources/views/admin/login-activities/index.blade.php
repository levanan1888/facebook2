<x-layouts.app.sidebar title="Quản lý Hệ thống">
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
            <div class="mb-6 flex justify-end space-x-3">
                <form action="{{ route('admin.login-activities.clear-old') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả hoạt động đăng nhập cũ?')">
                        Xóa dữ liệu cũ
                    </button>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="flex flex-wrap gap-4 mb-8">
                <div class="w-full sm:w-1/2 lg:w-1/6 bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 bg-green-100 bg-green-100 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-green-600 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Tổng đăng nhập</p>
                        <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $stats['total_logins'] }}</p>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 lg:w-1/6 bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 bg-red-100 bg-red-100 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-red-600 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Đăng nhập thất bại</p>
                        <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $stats['total_failed'] }}</p>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 lg:w-1/6 bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 bg-blue-100 bg-blue-100 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-blue-600 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Hôm nay</p>
                        <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $stats['today_logins'] }}</p>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 lg:w-1/6 bg-white bg-white rounded-xl shadow-md border border-gray-200 border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 bg-purple-100 bg-purple-100 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-purple-600 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600 text-gray-600 mb-1">Users hôm nay</p>
                        <p class="text-2xl font-bold text-gray-900 text-gray-900">{{ $stats['unique_users_today'] }}</p>
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

            <!-- Login Activities Table -->
            <div class="bg-white bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200 border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 border-gray-200 bg-gray-50 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 text-gray-900">Hoạt động đăng nhập</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500 text-gray-600">Hiển thị {{ $loginActivities->count() }} / {{ $loginActivities->total() }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 divide-gray-200">
                        <thead class="bg-gray-50 bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Người dùng
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Hành động
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Thông tin
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Thời gian
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 text-gray-700 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white bg-white divide-y divide-gray-200 divide-gray-200">
                            @foreach($loginActivities as $activity)
                            <tr class="hover:bg-gray-50 hover:bg-gray-100 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                <span class="text-sm font-bold text-white">{{ $activity->user->initials() }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 text-gray-900">{{ $activity->user->name }}</div>
                                            <div class="text-sm text-gray-500 text-gray-600">{{ $activity->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($activity->action === 'login')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Đăng nhập
                                        </span>
                                    @elseif($activity->action === 'logout')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 bg-gray-50 text-gray-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-2.293 2.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Đăng xuất
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            Thất bại
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @if($activity->ip_address)
                                            <div class="text-sm text-gray-900 text-gray-900">
                                                <span class="font-medium">IP:</span> {{ $activity->ip_address }}
                                            </div>
                                        @endif
                                        @if($activity->location)
                                            <div class="text-sm text-gray-500 text-gray-600">
                                                <span class="font-medium">Vị trí:</span> {{ $activity->location }}
                                            </div>
                                        @endif
                                        @if($activity->user_agent)
                                            <div class="text-xs text-gray-400 text-gray-400 truncate max-w-xs">
                                                {{ Str::limit($activity->user_agent, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-gray-600">
                                    {{ $activity->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.login-activities.show', $activity) }}" 
                                           class="text-blue-600 hover:text-blue-900 text-blue-600 hover:text-blue-700">
                                            Xem chi tiết
                                        </a>
                                        
                                        <form action="{{ route('admin.login-activities.destroy', $activity) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 text-red-600 hover:text-red-700"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa hoạt động này?')">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($loginActivities->hasPages())
                <div class="mt-8">
                    {{ $loginActivities->links() }}
                </div>
            @endif
        </div>
    </flux:main>
</x-layouts.app.sidebar>
