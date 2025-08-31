<div class="space-y-6">
    <!-- Data Source Configuration Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Cấu hình nguồn dữ liệu</h2>
                <p class="text-gray-600 mt-1">Kết nối và quản lý các nền tảng marketing</p>
            </div>
            <button id="btnAddDataSource" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Thêm nguồn dữ liệu</span>
            </button>
        </div>

        <!-- Data Sources Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Facebook Ads -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Facebook Ads</h3>
                            <p class="text-sm text-gray-600">Quảng cáo & Insights</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Trạng thái:</span>
                        <span class="text-green-600 font-medium">Đã kết nối</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cập nhật:</span>
                        <span class="text-gray-900">{{ now()->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dữ liệu:</span>
                        <span class="text-gray-900">{{ number_format($data['totals']['ads'] ?? 0) }} ads</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="flex-1 px-3 py-2 text-sm bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition-colors">
                        Cấu hình
                    </button>
                    <button class="flex-1 px-3 py-2 text-sm bg-green-50 text-green-700 rounded hover:bg-green-100 transition-colors">
                        Đồng bộ
                    </button>
                </div>
            </div>

            <!-- Facebook Posts -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Facebook Posts</h3>
                            <p class="text-sm text-gray-600">Bài đăng & Engagement</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Trạng thái:</span>
                        <span class="text-green-600 font-medium">Đã kết nối</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cập nhật:</span>
                        <span class="text-gray-900">{{ now()->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dữ liệu:</span>
                        <span class="text-gray-900">{{ number_format($data['totals']['posts'] ?? 0) }} posts</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="flex-1 px-3 py-2 text-sm bg-green-50 text-green-700 rounded hover:bg-green-100 transition-colors">
                        Cấu hình
                    </button>
                    <button class="flex-1 px-3 py-2 text-sm bg-green-50 text-green-700 rounded hover:bg-green-100 transition-colors">
                        Đồng bộ
                    </button>
                </div>
            </div>

            <!-- Google Ads (Placeholder) -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow opacity-60">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Google Ads</h3>
                            <p class="text-sm text-gray-600">Quảng cáo Google</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Trạng thái:</span>
                        <span class="text-gray-500">Chưa kết nối</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cập nhật:</span>
                        <span class="text-gray-500">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dữ liệu:</span>
                        <span class="text-gray-500">0</span>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="w-full px-3 py-2 text-sm bg-gray-100 text-gray-600 rounded cursor-not-allowed">
                        Kết nối
                    </button>
                </div>
            </div>

            <!-- TikTok Ads (Placeholder) -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow opacity-60">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31 0 2.61.01 3.91.07.07 0 .27.02.39.07.16.08.25.19.25.36V7.6c0 .27-.13.53-.34.69-.21.16-.42.21-.69.21-.07 0-.15-.01-.24-.04l-.38-.13L13.83 8.35c-.11.07-.23.1-.36.1-.48 0-.87-.39-.87-.87V1.1c0-.16.09-.28.25-.36.12-.05.32-.07.39-.07.07 0 .15.01.24.04l.38.13.12.04c.11.07.23.1.36.1z"/>
                                <path d="M12.525 12.02c-1.31 0-2.61-.01-3.91-.07-.07 0-.27-.02-.39-.07-.16-.08-.25-.19-.25-.36V4.4c0-.27.13-.53.34-.69.21-.16.42-.21.69-.21.07 0 .15.01.24.04l.38.13.12.04c.11.07.23.1.36.1.48 0 .87.39.87.87v6.53c0 .16-.09.28-.25.36-.12.05-.32.07-.39.07-.07 0-.15-.01-.24-.04l-.38-.13-.12-.04c-.11-.07-.23-.1-.36-.1z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">TikTok Ads</h3>
                            <p class="text-sm text-gray-600">Quảng cáo TikTok</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Trạng thái:</span>
                        <span class="text-gray-500">Chưa kết nối</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cập nhật:</span>
                        <span class="text-gray-500">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dữ liệu:</span>
                        <span class="text-gray-500">0</span>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="w-full px-3 py-2 text-sm bg-gray-100 text-gray-600 rounded cursor-not-allowed">
                        Kết nối
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Data View -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Dữ liệu thống nhất</h2>
                <p class="text-gray-600 mt-1">Xem và phân tích dữ liệu từ tất cả các nguồn</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Date Range Picker -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Khoảng thời gian:</label>
                    <select id="dateRange" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="7">7 ngày qua</option>
                        <option value="30" selected>30 ngày qua</option>
                        <option value="90">90 ngày qua</option>
                        <option value="365">1 năm qua</option>
                        <option value="custom">Tùy chỉnh</option>
                    </select>
                </div>
                
                <!-- Data Source Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Nguồn dữ liệu:</label>
                    <select id="dataSource" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" selected>Tất cả</option>
                        <option value="facebook_ads">Facebook Ads</option>
                        <option value="facebook_posts">Facebook Posts</option>
                        <option value="google_ads">Google Ads</option>
                        <option value="tiktok_ads">TikTok Ads</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Unified Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Tổng chi tiêu</p>
                        <p class="text-2xl font-bold text-blue-900">{{ number_format($data['totals']['spend'] ?? 0) }} VND</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600">+12.5%</span>
                    <span class="text-gray-600 ml-2">so với tháng trước</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">Tổng impressions</p>
                        <p class="text-2xl font-bold text-green-900">{{ number_format($data['totals']['impressions'] ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600">+8.3%</span>
                    <span class="text-gray-600 ml-2">so với tháng trước</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">Tổng clicks</p>
                        <p class="text-2xl font-bold text-purple-900">{{ number_format($data['totals']['clicks'] ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.122 2.122"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-red-600">-2.1%</span>
                    <span class="text-gray-600 ml-2">so với tháng trước</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">Tổng reach</p>
                        <p class="text-2xl font-bold text-orange-900">{{ number_format($data['totals']['reach'] ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600">+15.7%</span>
                    <span class="text-gray-600 ml-2">so với tháng trước</span>
                </div>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="bg-gray-50 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Hiệu suất theo thời gian</h3>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Ngày</button>
                    <button class="px-3 py-1 text-sm bg-blue-600 text-white border border-blue-600 rounded-lg">Tuần</button>
                    <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Tháng</button>
                </div>
            </div>
            <div class="h-64 bg-white rounded-lg border border-gray-200 flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p>Biểu đồ hiệu suất sẽ được hiển thị ở đây</p>
                    <p class="text-sm">Tích hợp với Chart.js hoặc ApexCharts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Comparison Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">So sánh hiệu suất</h2>
                <p class="text-gray-600 mt-1">Đánh giá hiệu suất giữa các nguồn dữ liệu</p>
            </div>
            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Xuất báo cáo
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chỉ số</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facebook Ads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facebook Posts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Google Ads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TikTok Ads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Chi tiêu</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['spend'] ?? 0) }} VND</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">{{ number_format($data['totals']['spend'] ?? 0) }} VND</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Impressions</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['impressions'] ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">{{ number_format($data['totals']['impressions'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Clicks</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['clicks'] ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">{{ number_format($data['totals']['clicks'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CTR</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(($data['totals']['ctr'] ?? 0) * 100, 2) }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">{{ number_format(($data['totals']['ctr'] ?? 0) * 100, 2) }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data Source Management
    const btnAddDataSource = document.getElementById('btnAddDataSource');
    const dateRange = document.getElementById('dateRange');
    const dataSource = document.getElementById('dataSource');

    // Add Data Source Modal (placeholder)
    btnAddDataSource.addEventListener('click', function() {
        alert('Tính năng thêm nguồn dữ liệu mới sẽ được phát triển trong tương lai');
    });

    // Date Range Change Handler
    dateRange.addEventListener('change', function() {
        if (this.value === 'custom') {
            // Implement custom date picker
            alert('Chọn khoảng thời gian tùy chỉnh');
        } else {
            // Reload data with new date range
            console.log('Loading data for', this.value, 'days');
        }
    });

    // Data Source Filter Handler
    dataSource.addEventListener('change', function() {
        console.log('Filtering by data source:', this.value);
        // Implement data filtering logic
    });

    // Real-time data updates (placeholder)
    setInterval(function() {
        // Update data source status indicators
        const indicators = document.querySelectorAll('.w-3.h-3.bg-green-500');
        indicators.forEach(indicator => {
            indicator.classList.toggle('animate-pulse');
        });
    }, 3000);
});
</script>
@endpush
