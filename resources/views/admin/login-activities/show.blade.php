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
                <a href="{{ route('admin.login-activities.index') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    Quay lại
                </a>
            </div>

            <!-- Login Activity Details -->
            <div class="bg-white bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200 border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 border-gray-200 bg-gray-50 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 text-gray-900">Chi tiết hoạt động đăng nhập</h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Information -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900 text-gray-900">Thông tin người dùng</h4>
                            <div class="bg-gray-50 bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                                            <span class="text-lg font-bold text-white">{{ $loginActivity->user->initials() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 text-gray-900">{{ $loginActivity->user->name }}</div>
                                        <div class="text-sm text-gray-500 text-gray-600">{{ $loginActivity->user->email }}</div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-600 text-gray-600">
                                    <span class="font-medium">ID:</span> {{ $loginActivity->user->id }}
                                </div>
                            </div>
                        </div>

                        <!-- Activity Information -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900 text-gray-900">Thông tin hoạt động</h4>
                            <div class="bg-gray-50 bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600 text-gray-600">Hành động:</span>
                                    @if($loginActivity->action === 'login')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 bg-green-100 text-green-800">
                                            Đăng nhập
                                        </span>
                                    @elseif($loginActivity->action === 'logout')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 bg-gray-50 text-gray-800">
                                            Đăng xuất
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 bg-red-100 text-red-800">
                                            Thất bại
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 text-gray-600">
                                    <span class="font-medium">Thời gian:</span> {{ $loginActivity->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-sm text-gray-600 text-gray-600">
                                    <span class="font-medium">Cách đây:</span> {{ $loginActivity->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <!-- Technical Information -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900 text-gray-900">Thông tin kỹ thuật</h4>
                            <div class="bg-gray-50 bg-gray-50 rounded-lg p-4 space-y-3">
                                @if($loginActivity->ip_address)
                                    <div class="text-sm text-gray-600 text-gray-600">
                                        <span class="font-medium">IP Address:</span> {{ $loginActivity->ip_address }}
                                    </div>
                                @endif
                                @if($loginActivity->location)
                                    <div class="text-sm text-gray-600 text-gray-600">
                                        <span class="font-medium">Vị trí:</span> {{ $loginActivity->location }}
                                    </div>
                                @endif
                                @if($loginActivity->user_agent)
                                    <div class="text-sm text-gray-600 text-gray-600">
                                        <span class="font-medium">User Agent:</span>
                                        <div class="mt-1 text-xs text-gray-500 text-gray-600 break-all">
                                            {{ $loginActivity->user_agent }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900 text-gray-900">Timestamps</h4>
                            <div class="bg-gray-50 bg-gray-50 rounded-lg p-4 space-y-3">
                                @if($loginActivity->logged_in_at)
                                    <div class="text-sm text-gray-600 text-gray-600">
                                        <span class="font-medium">Đăng nhập lúc:</span> {{ $loginActivity->logged_in_at->format('d/m/Y H:i:s') }}
                                    </div>
                                @endif
                                @if($loginActivity->logged_out_at)
                                    <div class="text-sm text-gray-600 text-gray-600">
                                        <span class="font-medium">Đăng xuất lúc:</span> {{ $loginActivity->logged_out_at->format('d/m/Y H:i:s') }}
                                    </div>
                                @endif
                                <div class="text-sm text-gray-600 text-gray-600">
                                    <span class="font-medium">Tạo lúc:</span> {{ $loginActivity->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-sm text-gray-600 text-gray-600">
                                    <span class="font-medium">Cập nhật lúc:</span> {{ $loginActivity->updated_at->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
