<x-layouts.app :title="__('Facebook Dashboard - Overview')">
    <div class="p-6">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Tổng quan Facebook Ads</h2>
                    <p class="text-gray-600">Thống kê tổng hợp và phân tích dữ liệu Facebook</p>
                </div>
                <div class="flex space-x-3">
                    <button id="btnToggleFilter" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" title="Bộ lọc">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-1.447.894l-4-2A1 1 0 018 17v-3.586L3.293 6.707A1 1 0 013 6V4z" />
                        </svg>
                        Bộ lọc
                    </button>
                    <button id="btnGuide" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Hướng dẫn
                    </button>
                    <button id="btnRefresh" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Làm mới
                    </button>
                    <button id="btnAiSummary" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700" title="Phân tích AI">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Phân tích AI
                    </button>
                </div>
            </div>

            @can('analytics.filter')
            <div id="filterPanel" class="mt-4 bg-white rounded-lg shadow-lg p-6 border border-gray-200 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Bộ lọc nâng cao</h3>
                    <button type="button" id="btnCloseFilter" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">💡 Hướng dẫn sử dụng bộ lọc:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Nếu không thấy dữ liệu Business Manager, hãy nhấn "Làm mới dữ liệu"</li>
                                <li>Bộ lọc hoạt động theo thứ tự: Business Manager → Tài khoản quảng cáo → Chiến dịch</li>
                                <li>Sử dụng nút "Làm mới dữ liệu" để cập nhật thông tin mới nhất từ Facebook</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <form method="GET" action="{{ route('facebook.overview') }}" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @can('analytics.filter.time')
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Khoảng thời gian</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Từ ngày</label>
                                    <input type="date" name="from" value="{{ $data['filters']['from'] ?? '' }}" 
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Đến ngày</label>
                                    <input type="date" name="to" value="{{ $data['filters']['to'] ?? '' }}" 
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                </div>
                            </div>
                        </div>
                        @endcan
                        
                        @can('analytics.filter.scope')
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Business Manager</label>
                            <select name="business_id" id="businessFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả Business</option>
                                @if(!empty($data['filters']['businesses']))
                                    @foreach($data['filters']['businesses'] as $business)
                                        <option value="{{ $business->id }}" {{ ($data['filters']['business_id'] ?? null) == $business->id ? 'selected' : '' }}>
                                            {{ $business->name ?? 'Business ' . $business->id }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Chưa có dữ liệu Business Manager</option>
                                @endif
                            </select>
                            @if(empty($data['filters']['businesses']))
                                <p class="text-xs text-red-500 mt-1">⚠️ Cần đồng bộ dữ liệu Facebook để load Business Managers</p>
                            @endif
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Tài khoản quảng cáo</label>
                            <select name="account_id" id="accountFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả tài khoản</option>
                                @if(!empty($data['filters']['accounts']))
                                    @foreach($data['filters']['accounts'] as $acc)
                                        <option value="{{ $acc->id }}" data-business="{{ $acc->business_id ?? '' }}" {{ ($data['filters']['account_id'] ?? null) == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->name ?? 'Account ' . $acc->id }} ({{ $acc->account_id ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Chưa có dữ liệu tài khoản quảng cáo</option>
                                @endif
                            </select>
                            @if(empty($data['filters']['accounts']))
                                <p class="text-xs text-red-500 mt-1">⚠️ Cần đồng bộ dữ liệu Facebook để load tài khoản quảng cáo</p>
                            @endif
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Chiến dịch</label>
                            <select name="campaign_id" id="campaignFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả chiến dịch</option>
                                @foreach(($data['filters']['campaigns'] ?? []) as $c)
                                    <option value="{{ $c->id }}" data-account="{{ $c->ad_account_id ?? '' }}" {{ ($data['filters']['campaign_id'] ?? null) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Trang Facebook</label>
                            <select name="page_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả trang</option>
                                @foreach(($data['filters']['pages'] ?? []) as $page)
                                    <option value="{{ $page->id }}" 
                                            data-business="{{ $page->business_id ?? '' }}"
                                            {{ ($data['filters']['page_id'] ?? null) == $page->id ? 'selected' : '' }}>
                                        {{ $page->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endcan
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Loại nội dung</label>
                            <select name="content_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả loại</option>
                                <option value="photo" {{ ($data['filters']['content_type'] ?? null) == 'photo' ? 'selected' : '' }}>Hình ảnh</option>
                                <option value="video" {{ ($data['filters']['content_type'] ?? null) == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="link" {{ ($data['filters']['content_type'] ?? null) == 'link' ? 'selected' : '' }}>Liên kết</option>
                                <option value="text" {{ ($data['filters']['content_type'] ?? null) == 'text' ? 'selected' : '' }}>Văn bản</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả trạng thái</option>
                                <option value="ACTIVE" {{ ($data['filters']['status'] ?? null) == 'ACTIVE' ? 'selected' : '' }}>Đang hoạt động</option>
                                <option value="PAUSED" {{ ($data['filters']['status'] ?? null) == 'PAUSED' ? 'selected' : '' }}>Tạm dừng</option>
                                <option value="DELETED" {{ ($data['filters']['status'] ?? null) == 'DELETED' ? 'selected' : '' }}>Đã xóa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                        <div class="flex space-x-3">
                            <button type="submit" class="px-6 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-1.447.894l-4-2A1 1 0 018 17v-3.586L3.293 6.707A1 1 0 013 6V4z"></path>
                                </svg>
                                Áp dụng bộ lọc
                            </button>
                            <button type="button" onclick="clearFilters()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Xóa bộ lọc
                            </button>
                            <button type="button" onclick="refreshFilterData()" class="px-6 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Làm mới dữ liệu
                            </button>
                        </div>
                        <div class="text-sm text-gray-500">
                            <span id="filterCount">0</span> bộ lọc đang hoạt động
                        </div>
                    </div>
                </form>
            </div>
            @endcan

            <!-- AI Summary Section - Hiển thị dạng popup -->
            <div id="aiSummaryHolder" class="mb-6">
                <div class="bg-white rounded-lg shadow p-6 border border-emerald-200 cursor-pointer hover:shadow-md transition-shadow" 
                     onclick="openAiSummaryPopup()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-emerald-700">Đánh giá tổng quan bởi AI</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500" id="aiSummaryStatus">Đang phân tích...</span>
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-3">Vui lòng đợi trong giây lát.</div>
                    <div class="text-xs text-emerald-600 font-medium">Nhấn để xem chi tiết →</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Business Managers</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['businesses'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tài khoản quảng cáo</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['accounts'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Chiến dịch</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['campaigns'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.122 2.122"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Bài đăng</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['posts'] ?? 0) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                @if(($data['totals']['posts'] ?? 0) > 0)
                                    {{ number_format($data['totals']['ads'] ?? 0) }} quảng cáo
                                @else
                                    Chưa có dữ liệu
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tổng chi tiêu</p>
                            <p class="text-2xl font-bold text-gray-900">${{ number_format($data['stats']['total_spend'] ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                @if(($data['performanceStats']['totalSpend'] ?? 0) > 0)
                                    Dữ liệu từ {{ $data['performanceStats']['totalImpressions'] ?? 0 }} hiển thị
                                @else
                                    Chưa có dữ liệu chi tiêu
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Cập nhật: {{ now()->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tổng hiển thị</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['stats']['total_impressions'] ?? 0) }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                @if(($data['performanceStats']['totalImpressions'] ?? 0) > 0)
                                    Dữ liệu từ Facebook API
                                @else
                                    Chưa có dữ liệu
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tổng lượt click</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['stats']['total_clicks'] ?? 0) }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                @if(($data['performanceStats']['totalClicks'] ?? 0) > 0)
                                    CTR: {{ number_format(($data['stats']['avg_ctr'] ?? 0) * 100, 2) }}%
                                @else
                                    Chưa có dữ liệu
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Hoạt động theo thời gian</h3>
                        <div class="text-sm text-gray-600">
                            @if(!empty($data['last7Days']))
                                @php
                                    $firstDate = \Carbon\Carbon::parse($data['last7Days'][0]['date'] ?? 'now');
                                    $lastDate = \Carbon\Carbon::parse(end($data['last7Days'])['date'] ?? 'now');
                                @endphp
                                <span class="font-medium">Từ:</span> {{ $firstDate->format('d/m/Y') }} 
                                <span class="font-medium ml-2">Đến:</span> {{ $lastDate->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <div class="mb-2">
                        <p class="text-xs text-gray-500 italic">
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                            Dữ liệu hiển thị theo ngày tháng thực tế từ database
                        </p>
                    </div>
                    <div class="h-72"><canvas id="activityChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Phân bố trạng thái Campaigns</h3>
                    <div class="h-72">
                        @if(isset($data['statusStats']['campaigns']) && count($data['statusStats']['campaigns']) > 0)
                            <canvas id="statusChart"></canvas>
                        @else
                            <div class="flex items-center justify-center h-full text-gray-500">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Chưa có dữ liệu</p>
                                    <p class="text-sm">Campaigns chưa được đồng bộ hoặc không có trạng thái</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Top 5 Quảng cáo (Theo thời gian đồng bộ)</h3></div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($data['topAds'] ?? [] as $ad)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ Str::limit($ad->name, 40) }}</h4>
                                        <p class="text-sm text-gray-600">{{ $ad->campaign->name ?? 'Campaign không xác định' }}</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $ad->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : ($ad->status === 'PAUSED' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ $ad->status }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">{{ $ad->last_insights_sync ? $ad->last_insights_sync->format('d/m/Y') : 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">Đồng bộ lúc</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    <p>Chưa có dữ liệu quảng cáo</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Top 5 Posts (Theo hiệu suất)</h3></div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($data['topPosts'] ?? [] as $post)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">
                                            {{ Str::limit($post['message'] ?? 'Post ID: ' . ($post['post_id'] ?? 'N/A'), 50) }}
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            Post ID: {{ $post['post_id'] ?? 'N/A' }}
                                            @if(isset($post['post_id']) && isset($post['page_id']))
                                                <a href="{{ route('facebook.data-management.post-detail', ['postId' => $post['post_id'], 'pageId' => $post['page_id']]) }}" 
                                                   class="ml-2 text-blue-600 hover:text-blue-800 underline text-xs">
                                                    Xem chi tiết
                                                </a>
                                            @endif
                                        </p>
                                        <div class="flex space-x-4 mt-2 text-sm text-gray-500">
                                            <span title="Chi phí">💰 {{ number_format($post['total_spend'] ?? 0, 0) }} VND</span>
                                            <span title="Hiển thị">👁️ {{ number_format($post['total_impressions'] ?? 0) }}</span>
                                            <span title="Click">🖱️ {{ number_format($post['total_clicks'] ?? 0) }}</span>
                                            <span title="CTR">📊 {{ number_format(($post['avg_ctr'] ?? 0) * 100, 2) }}%</span>
                                        </div>
                                        @if(isset($post['permalink_url']))
                                            <div class="mt-2">
                                                <a href="{{ $post['permalink_url'] }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                                    Xem bài viết gốc
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">{{ number_format($post['total_spend'] ?? 0, 0) }}</p>
                                        <p class="text-xs text-gray-500">Chi phí (VND)</p>
                                        @if(isset($post['total_video_views']) && $post['total_video_views'] > 0)
                                            <p class="text-xs text-green-600 mt-1">🎥 {{ number_format($post['total_video_views']) }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <p>Chưa có dữ liệu bài đăng</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Guide Modal - Hiển thị hướng dẫn cho 2 màn hình trong sidebar -->
        <div id="guideModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-10 mx-auto p-6 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Hướng dẫn sử dụng Facebook Dashboard</h3>
                        <button id="closeGuideModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="flex space-x-8" aria-label="Tabs">
                            <button id="overviewTab" class="border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600 tab-button active">
                                📊 Facebook Overview
                            </button>
                            <button id="dataManagementTab" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 tab-button">
                                📋 Data Management
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Tab Content -->
                    <div id="overviewContent" class="tab-content active">
                        <div class="space-y-4 text-sm text-gray-600">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-900 mb-2">🎯 Màn hình Tổng quan (Overview)</h4>
                                <p class="text-blue-800 mb-2">Đây là màn hình chính để xem tổng quan toàn bộ hệ thống Facebook Ads:</p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li><strong>Thống kê tổng hợp:</strong> Business Managers, Ad Accounts, Campaigns, Posts</li>
                                    <li><strong>Biểu đồ hoạt động:</strong> Theo dõi xu hướng 7 ngày gần nhất</li>
                                    <li><strong>Phân bố trạng thái:</strong> Campaigns theo trạng thái hoạt động</li>
                                    <li><strong>Top performers:</strong> 5 Campaigns và Posts hiệu suất cao nhất</li>
                                </ul>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-green-900 mb-2">🔧 Tính năng chính</h4>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li><strong>Bộ lọc nâng cao:</strong> Lọc theo thời gian, Business, Account, Campaign, Page</li>
                                    <li><strong>Phân tích AI:</strong> Nhận đánh giá và khuyến nghị từ AI</li>
                                    <li><strong>Làm mới dữ liệu:</strong> Cập nhật thông tin mới nhất</li>
                                    <li><strong>Xuất báo cáo:</strong> Tải về dữ liệu phân tích</li>
                                </ul>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-purple-900 mb-2">💡 Cách sử dụng hiệu quả</h4>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>Kiểm tra <strong>Business Overview</strong> để nắm tổng quan hệ thống</li>
                                    <li>Sử dụng <strong>Filter Panel</strong> để lọc dữ liệu theo nhu cầu</li>
                                    <li>Nhấn <strong>Phân tích AI</strong> để nhận khuyến nghị cải thiện</li>
                                    <li>Theo dõi <strong>Performance Charts</strong> để đánh giá hiệu suất</li>
                                    <li>Xem <strong>Top Posts</strong> để học hỏi từ nội dung thành công</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div id="dataManagementContent" class="tab-content hidden">
                        <div class="space-y-4 text-sm text-gray-600">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-green-900 mb-2">📊 Màn hình Quản lý dữ liệu (Data Management)</h4>
                                <p class="text-green-800 mb-2">Màn hình này cung cấp công cụ quản lý và phân tích dữ liệu chi tiết:</p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li><strong>Danh sách Posts:</strong> Xem tất cả bài viết với metrics chi tiết</li>
                                    <li><strong>Chi tiết Post:</strong> Phân tích breakdown và insights sâu</li>
                                    <li><strong>Đồng bộ dữ liệu:</strong> Cập nhật từ Facebook API</li>
                                    <li><strong>Phân tích Breakdown:</strong> Theo độ tuổi, giới tính, vị trí, thiết bị</li>
                                    <li><strong>Video Metrics:</strong> Thống kê chi tiết về video content</li>
                                </ul>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-900 mb-2">🔍 Tính năng phân tích</h4>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li><strong>Breakdown Analysis:</strong> Phân tích theo nhiều tiêu chí khác nhau</li>
                                    <li><strong>Performance Tracking:</strong> Theo dõi hiệu suất theo thời gian</li>
                                    <li><strong>Action Insights:</strong> Phân tích hành động người dùng</li>
                                    <li><strong>Comparative Analysis:</strong> So sánh hiệu suất giữa các posts</li>
                                    <li><strong>Export Data:</strong> Xuất dữ liệu để phân tích nâng cao</li>
                                </ul>
                            </div>
                            
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-orange-900 mb-2">📈 Cách sử dụng Data Management</h4>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>Vào <strong>Data Management</strong> từ sidebar để xem danh sách posts</li>
                                    <li>Click vào <strong>Post ID</strong> để xem chi tiết và breakdown</li>
                                    <li>Sử dụng <strong>Filter</strong> để tìm posts cụ thể</li>
                                    <li>Xem <strong>Breakdown Charts</strong> để hiểu audience insights</li>
                                    <li>Phân tích <strong>Video Metrics</strong> nếu có nội dung video</li>
                                    <li>Xuất <strong>Reports</strong> để chia sẻ với team</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button id="closeGuideModalBtn" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">Đã hiểu</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Summary Popup Modal - Hiển thị khi nhấn vào AI Summary section -->
        <div id="aiSummaryPopupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-5 mx-auto p-6 border w-11/12 md:w-5/6 lg:w-4/5 xl:w-3/4 shadow-lg rounded-md bg-white max-h-[95vh] overflow-y-auto">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-700">Đánh giá tổng quan bởi AI</h3>
                        <button id="closeAiSummaryPopup" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div id="aiSummaryPopupContent" class="space-y-4">
                        <div class="bg-emerald-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-emerald-600 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span class="text-emerald-800 font-medium">Đang phân tích dữ liệu...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button id="closeAiSummaryPopupBtn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Analysis Modal - Hiển thị popup khi nhấn Phân tích AI -->
        <div id="aiAnalysisModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-5 mx-auto p-6 border w-11/12 md:w-5/6 lg:w-4/5 xl:w-3/4 shadow-lg rounded-md bg-white max-h-[95vh] overflow-y-auto">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-700">Phân tích AI - Đánh giá tổng quan</h3>
                        <button id="closeAiModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div id="aiModalContent" class="space-y-4">
                        <div class="bg-emerald-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                </svg>
                                <span class="text-emerald-800 font-medium">Đang phân tích dữ liệu...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button id="closeAiModalBtn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        <style>
        /* Tab Navigation Styling */
        .tab-button {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        
        .tab-button:hover {
            color: #1d4ed8;
        }
        
        .tab-button.active {
            border-bottom-color: #3b82f6;
            color: #2563eb;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Modal Styling */
        .modal-overlay {
            backdrop-filter: blur(4px);
        }
        
        /* AI Modal Content Styling */
        .prose {
            color: #374151;
        }
        
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            color: #111827;
            font-weight: 600;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        
        .prose p {
            margin-bottom: 1em;
            line-height: 1.7;
        }
        
        .prose ul, .prose ol {
            margin-bottom: 1em;
            padding-left: 1.5em;
        }
        
        .prose li {
            margin-bottom: 0.5em;
        }
        
        .prose strong {
            color: #111827;
            font-weight: 600;
        }
        </style>
        
        <script>
        function initFacebookOverviewCharts() {
            // modal/UX handlers (once)
            const guideModal = document.getElementById('guideModal');
            const btnGuide = document.getElementById('btnGuide');
            const closeGuideModal = document.getElementById('closeGuideModal');
            const closeGuideModalBtn = document.getElementById('closeGuideModalBtn');
            const btnRefresh = document.getElementById('btnRefresh');
            const btnAiSummary = document.getElementById('btnAiSummary');
            const btnToggleFilter = document.getElementById('btnToggleFilter');
            const filterPanel = document.getElementById('filterPanel');
            
            // AI Analysis Modal
            const aiAnalysisModal = document.getElementById('aiAnalysisModal');
            const closeAiModal = document.getElementById('closeAiModal');
            const closeAiModalBtn = document.getElementById('closeAiModalBtn');
            const aiModalContent = document.getElementById('aiModalContent');
            
            // Tab Navigation
            const overviewTab = document.getElementById('overviewTab');
            const dataManagementTab = document.getElementById('dataManagementTab');
            const overviewContent = document.getElementById('overviewContent');
            const dataManagementContent = document.getElementById('dataManagementContent');

            // Guide Modal handlers
            if (btnGuide && guideModal && closeGuideModal && closeGuideModalBtn) {
                btnGuide.onclick = () => guideModal.classList.remove('hidden');
                const closeModal = () => guideModal.classList.add('hidden');
                closeGuideModal.onclick = closeModal;
                closeGuideModalBtn.onclick = closeModal;
                guideModal.onclick = (e) => { if (e.target === guideModal) closeModal(); };
            }
            
            // AI Analysis Modal handlers
            if (btnAiSummary && aiAnalysisModal && closeAiModal && closeAiModalBtn) {
                btnAiSummary.onclick = async () => {
                    aiAnalysisModal.classList.remove('hidden');
                    await requestAiSummaryForModal();
                };
                
                const closeAiModal = () => aiAnalysisModal.classList.add('hidden');
                closeAiModal.onclick = closeAiModal;
                closeAiModalBtn.onclick = closeAiModal;
                aiAnalysisModal.onclick = (e) => { if (e.target === aiAnalysisModal) closeAiModal(); };
            }
            
            // AI Summary Popup Modal handlers
            const aiSummaryPopupModal = document.getElementById('aiSummaryPopupModal');
            const closeAiSummaryPopup = document.getElementById('closeAiSummaryPopup');
            const closeAiSummaryPopupBtn = document.getElementById('closeAiSummaryPopupBtn');
            
            if (aiSummaryPopupModal && closeAiSummaryPopup && closeAiSummaryPopupBtn) {
                const closeAiSummaryPopupModal = () => aiSummaryPopupModal.classList.add('hidden');
                closeAiSummaryPopup.onclick = closeAiSummaryPopupModal;
                closeAiSummaryPopupBtn.onclick = closeAiSummaryPopupModal;
                aiSummaryPopupModal.onclick = (e) => { if (e.target === aiSummaryPopupModal) closeAiSummaryPopupModal(); };
            }
            
            // Tab Navigation handlers
            if (overviewTab && dataManagementTab && overviewContent && dataManagementContent) {
                overviewTab.onclick = () => {
                    overviewTab.classList.add('active', 'border-blue-500', 'text-blue-600');
                    overviewTab.classList.remove('border-transparent', 'text-gray-500');
                    dataManagementTab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    dataManagementTab.classList.add('border-transparent', 'text-gray-500');
                    
                    overviewContent.classList.remove('hidden');
                    dataManagementContent.classList.add('hidden');
                };
                
                dataManagementTab.onclick = () => {
                    dataManagementTab.classList.add('active', 'border-blue-500', 'text-blue-600');
                    dataManagementTab.classList.remove('border-transparent', 'text-gray-500');
                    overviewTab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    overviewTab.classList.add('border-transparent', 'text-gray-500');
                    
                    dataManagementContent.classList.remove('hidden');
                    overviewContent.classList.add('hidden');
                };
            }

            if (btnRefresh) {
                btnRefresh.onclick = async function() {
                    btnRefresh.disabled = true;
                    btnRefresh.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Đang tải...';
                    try { window.location.href = '{{ route('facebook.overview') }}'; }
                    catch (error) { btnRefresh.disabled = false; btnRefresh.innerHTML = 'Làm mới'; }
                };
            }

            if (btnAiSummary) {
                // AI Summary button now handled in modal handlers above
                // This is kept for backward compatibility
            }

            // Filter toggle handled in initFilterLogic() to avoid duplicate event listeners

            // Charts
            window.__fbCharts ||= {};
            const activityEl = document.getElementById('activityChart');
            const statusEl = document.getElementById('statusChart');
            if (activityEl) {
                const activityCtx = activityEl.getContext('2d');
                const activityData = @json($data['last7Days']);
                
                // Xử lý labels để hiển thị ngày cụ thể từ database
                const formattedLabels = activityData.map(item => {
                    if (item.date) {
                        const date = new Date(item.date);
                        // Kiểm tra nếu là ngày hợp lệ
                        if (!isNaN(date.getTime())) {
                            // Format ngày theo định dạng Việt Nam: dd/mm/yyyy
                            return date.toLocaleDateString('vi-VN', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            });
                        }
                    }
                    // Fallback nếu không có date hoặc date không hợp lệ
                    return item.date || 'N/A';
                });
                
                window.__fbCharts.activity && window.__fbCharts.activity.destroy();
                window.__fbCharts.activity = new Chart(activityCtx, { 
                    type: 'bar', 
                    data: { 
                        labels: formattedLabels, 
                        datasets: [
                            { 
                                label: 'Chiến dịch', 
                                data: activityData.map(item => item.campaigns), 
                                backgroundColor: 'rgba(59,130,246,0.8)', 
                                borderColor: 'rgb(59,130,246)', 
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false
                            },
                            { 
                                label: 'Quảng cáo', 
                                data: activityData.map(item => item.ads), 
                                backgroundColor: 'rgba(16,185,129,0.8)', 
                                borderColor: 'rgb(16,185,129)', 
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false
                            },
                            { 
                                label: 'Bài đăng', 
                                data: activityData.map(item => item.posts), 
                                backgroundColor: 'rgba(245,158,11,0.8)', 
                                borderColor: 'rgb(245,158,11)', 
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false
                            },
                            { 
                                label: 'Chi tiêu ($)', 
                                data: activityData.map(item => item.spend || 0), 
                                backgroundColor: 'rgba(239,68,68,0.8)', 
                                borderColor: 'rgb(239,68,68)', 
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false,
                                yAxisID: 'y1' 
                            }
                        ] 
                    }, 
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { 
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }, 
                            tooltip: { 
                                mode: 'index', 
                                intersect: false,
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: 'rgba(255,255,255,0.2)',
                                borderWidth: 1
                            } 
                        }, 
                        interaction: { 
                            mode: 'index', 
                            intersect: false 
                        }, 
                        scales: { 
                            y: { 
                                type: 'linear', 
                                display: true, 
                                position: 'left', 
                                beginAtZero: true, 
                                grid: { 
                                    color: 'rgba(0,0,0,0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    color: 'rgba(0,0,0,0.6)',
                                    font: {
                                        size: 11
                                    }
                                }
                            }, 
                            y1: { 
                                type: 'linear', 
                                display: true, 
                                position: 'right', 
                                beginAtZero: true, 
                                grid: { 
                                    drawOnChartArea: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: 'rgba(0,0,0,0.6)',
                                    font: {
                                        size: 11
                                    }
                                }
                            }, 
                            x: { 
                                grid: { 
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: 'rgba(0,0,0,0.6)',
                                    font: {
                                        size: 11
                                    },
                                    maxRotation: 45,
                                    minRotation: 0,
                                    callback: function(value, index) {
                                        // Hiển thị ngày rõ ràng hơn trên trục X
                                        const label = this.getLabelForValue(value);
                                        if (label && label !== 'N/A') {
                                            // Nếu label đã được format rồi thì giữ nguyên
                                            return label;
                                        }
                                        // Fallback: hiển thị ngày gốc từ database
                                        const originalDate = activityData[index]?.date;
                                        if (originalDate) {
                                            const date = new Date(originalDate);
                                            if (!isNaN(date.getTime())) {
                                                return date.toLocaleDateString('vi-VN', {
                                                    day: '2-digit',
                                                    month: '2-digit'
                                                });
                                            }
                                        }
                                        return label;
                                    }
                                }
                            } 
                        }, 
                        plugins: { 
                            title: { 
                                display: true, 
                                text: 'Hoạt động 7 ngày gần nhất',
                                color: 'rgba(0,0,0,0.8)',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        // Hiển thị ngày đầy đủ trong tooltip
                                        const dataIndex = context[0].dataIndex;
                                        const originalDate = activityData[dataIndex].date;
                                        if (originalDate) {
                                            const date = new Date(originalDate);
                                            if (!isNaN(date.getTime())) {
                                                return date.toLocaleDateString('vi-VN', {
                                                    weekday: 'long',
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric'
                                                });
                                            }
                                        }
                                        return context[0].label;
                                    }
                                }
                            }
                        },
                        layout: {
                            padding: {
                                top: 20,
                                right: 20,
                                bottom: 20,
                                left: 20
                            }
                        }
                    } 
                });
            }
            if (statusEl) {
                const statusCtx = statusEl.getContext('2d');
                const statusData = @json($data['statusStats']['campaigns'] ?? []);
                
                if (Object.keys(statusData).length > 0) {
                    window.__fbCharts.status && window.__fbCharts.status.destroy();
                    window.__fbCharts.status = new Chart(statusCtx, { 
                        type: 'doughnut', 
                        data: { 
                            labels: Object.keys(statusData), 
                            datasets: [{ 
                                data: Object.values(statusData), 
                                backgroundColor: ['rgb(16,185,129)','rgb(245,158,11)','rgb(239,68,68)','rgb(107,114,128)','rgb(99,102,241)'], 
                                borderWidth: 1, 
                                borderColor: '#fff' 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false, 
                            plugins: { 
                                legend: { position: 'bottom' }, 
                                tooltip: { 
                                    callbacks: { 
                                        label: (ctx) => `${ctx.label}: ${ctx.parsed.toLocaleString()}` 
                                    } 
                                } 
                            } 
                        } 
                    });
                }
            }
        }

        // Singleton fetch: chỉ gọi API 1 lần, các nơi khác dùng chung Promise
        function fetchAiSummaryOnce(breakdownsData, debugFlag = false) {
            if (window.__aiSummaryPromise) return window.__aiSummaryPromise;
            const url = new URL('{{ route('facebook.overview.ai-summary') }}', window.location.origin);
            if (debugFlag) url.searchParams.set('debug','1');
            window.__aiSummaryPromise = fetch(url.toString(), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ breakdowns_data: breakdownsData })
            })
            .then(res => res.json())
            .catch(err => { window.__aiSummaryPromise = null; throw err; });
            return window.__aiSummaryPromise;
        }

        async function requestAiSummary(isManual = false) {
            const holder = document.getElementById('aiSummaryHolder');
            const statusElement = document.getElementById('aiSummaryStatus');
            
            // Kiểm tra xem đã có kết quả AI chưa
            if (!isManual && holder && holder.innerHTML.includes('Hoàn thành')) {
                console.log('AI summary already loaded, skipping...');
                return;
            }
            
            if (statusElement) {
                statusElement.textContent = 'Đang phân tích...';
            }
            
            holder.innerHTML = `
                <div class=\"bg-white rounded-lg shadow p-6 border border-emerald-200 cursor-pointer hover:shadow-md transition-shadow\" onclick=\"openAiSummaryPopup()\">
                    <div class=\"flex items-center justify-between mb-4\">
                        <h3 class=\"text-lg font-semibold text-emerald-700\">Đánh giá tổng quan bởi AI</h3>
                        <div class=\"flex items-center space-x-2\">
                            <span class=\"text-xs text-gray-500\" id=\"aiSummaryStatus\">Đang phân tích...</span>
                            <svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14\" />
                            </svg>
                        </div>
                    </div>
                    <div class=\"text-sm text-gray-500 mb-3\">Vui lòng đợi trong giây lát.</div>
                    <div class=\"text-xs text-emerald-600 font-medium\">Nhấn để xem chi tiết →</div>
                </div>`;
            try {
                if (isManual) {
                    const b = document.getElementById('btnAiSummary');
                    if (b) { b.disabled = true; b.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Đang phân tích...'; }
                }
                
                // Chuẩn bị data breakdowns từ view để gửi cho AI
                const breakdownsData = {
                    breakdowns: @json($data['breakdowns'] ?? []),
                    actions: @json($data['actions'] ?? []),
                    stats: @json($data['stats'] ?? []),
                    totals: @json($data['totals'] ?? []),
                    performanceStats: @json($data['performanceStats'] ?? []),
                    last7Days: @json($data['last7Days'] ?? []),
                    statusStats: @json($data['statusStats'] ?? [])
                };
                
                const data = await fetchAiSummaryOnce(breakdownsData, isManual && (window._aiDebug || false));
                if (data && data.debug) {
                    // In ra console để bạn kiểm tra metrics tổng hợp cuối cùng
                    console.log('AI metrics (debug):', data.metrics);
                    console.log('Breakdowns data sent:', breakdownsData);
                    console.log('Frontend breakdowns received:', data.hasFrontendBreakdowns);
                    console.log('Breakdowns count:', data.breakdownsCount);
                    await renderAiCard('Đang ở chế độ debug – xem metrics trong console.');
                } else {
                    const text = (data && data.summary) ? data.summary : 'Không nhận được kết quả từ AI.';
                    await renderAiCard(text);
                }
            } catch (_) {
                await renderAiCard('Lỗi gọi AI. Vui lòng thử lại.');
            } finally {
                if (isManual) {
                    const b = document.getElementById('btnAiSummary');
                    if (b) { b.disabled = false; b.innerHTML = 'Phân tích AI'; }
                }
            }
        }
        
        // Hàm mới để xử lý AI Summary trong Modal
        async function requestAiSummaryForModal() {
            const aiModalContent = document.getElementById('aiModalContent');
            if (!aiModalContent) return;
            
            aiModalContent.innerHTML = `
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="text-emerald-800 font-medium">Đang phân tích dữ liệu...</span>
                    </div>
                </div>`;
            
            try {
                // Chuẩn bị data breakdowns từ view để gửi cho AI
                const breakdownsData = {
                    breakdowns: @json($data['breakdowns'] ?? []),
                    actions: @json($data['actions'] ?? []),
                    stats: @json($data['stats'] ?? []),
                    totals: @json($data['totals'] ?? []),
                    performanceStats: @json($data['performanceStats'] ?? []),
                    last7Days: @json($data['last7Days'] ?? []),
                    statusStats: @json($data['statusStats'] ?? [])
                };
                
                const data = await fetchAiSummaryOnce(breakdownsData, window._aiDebug || false);
                if (data && data.debug) {
                    console.log('AI metrics (debug):', data.metrics);
                    console.log('Breakdowns data sent:', breakdownsData);
                    await renderAiModalContent('Đang ở chế độ debug – xem metrics trong console.');
                } else {
                    const text = (data && data.summary) ? data.summary : 'Không nhận được kết quả từ AI.';
                    await renderAiModalContent(text);
                }
            } catch (error) {
                console.error('AI Analysis error:', error);
                await renderAiModalContent('Lỗi gọi AI. Vui lòng thử lại.');
            }
        }

        function ensureChartAndInit() {
            if (window.Chart) { initFacebookOverviewCharts(); return; }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            s.onload = initFacebookOverviewCharts;
            document.head.appendChild(s);
        }
        async function renderAiCard(content) {
            const holder = document.getElementById('aiSummaryHolder');
            const statusElement = document.getElementById('aiSummaryStatus');
            
            // Cập nhật status
            if (statusElement) {
                statusElement.textContent = 'Hoàn thành';
            }
            
            // Load a tiny markdown parser for clean output if needed
            async function ensureMarked() {
                if (window.marked) return;
                await new Promise((resolve) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/marked/marked.min.js';
                    s.onload = resolve; document.head.appendChild(s);
                });
            }
            await ensureMarked();
            const md = (window.marked && window.marked.parse) ? window.marked.parse(content) : sanitizePlain(content);
            
            // Tạo preview content (chỉ hiển thị một phần)
            const previewContent = content.length > 200 ? content.substring(0, 200) + '...' : content;
            const previewMd = (window.marked && window.marked.parse) ? window.marked.parse(previewContent) : sanitizePlain(previewContent);
            
            holder.innerHTML = `
                <div class=\"bg-white rounded-lg shadow p-6 border border-emerald-200 cursor-pointer hover:shadow-md transition-shadow\" onclick=\"openAiSummaryPopup()\">
                    <div class=\"flex items-center justify-between mb-4\">
                        <h3 class=\"text-lg font-semibold text-emerald-700\">Đánh giá tổng quan bởi AI</h3>
                        <div class=\"flex items-center space-x-2\">
                            <span class=\"text-xs text-green-600 font-medium\" id=\"aiSummaryStatus\">Hoàn thành</span>
                            <svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14\" />
                            </svg>
                        </div>
                    </div>
                    <div class=\"text-[15px] leading-7 space-y-3 max-h-[200px] overflow-y-auto pr-2\">${previewMd}</div>
                    <div class=\"text-xs text-emerald-600 font-medium mt-3\">Nhấn để xem chi tiết đầy đủ →</div>
                </div>`;
            holder.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Hàm mới để render AI content trong modal
        async function renderAiModalContent(content) {
            const aiModalContent = document.getElementById('aiModalContent');
            if (!aiModalContent) return;
            
            // Load a tiny markdown parser for clean output if needed
            async function ensureMarked() {
                if (window.marked) return;
                await new Promise((resolve) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/marked/marked.min.js';
                    s.onload = resolve; document.head.appendChild(s);
                });
            }
            await ensureMarked();
            const md = (window.marked && window.marked.parse) ? window.marked.parse(content) : sanitizePlain(content);
            
            aiModalContent.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold text-emerald-700">Kết quả phân tích AI</h4>
                        <span class="text-xs text-gray-500">Cập nhật: ${new Date().toLocaleString()}</span>
                    </div>
                    <div class="text-[15px] leading-7 space-y-4 max-h-[60vh] overflow-y-auto pr-2 prose prose-sm max-w-none">
                        ${md}
                    </div>
                </div>`;
        }
        
        // Hàm mới để render AI content trong AI Summary popup
        async function renderAiSummaryPopupContent(content) {
            const aiSummaryPopupContent = document.getElementById('aiSummaryPopupContent');
            if (!aiSummaryPopupContent) return;
            
            // Load a tiny markdown parser for clean output if needed
            async function ensureMarked() {
                if (window.marked) return;
                await new Promise((resolve) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/marked/marked.min.js';
                    s.onload = resolve; document.head.appendChild(s);
                });
            }
            await ensureMarked();
            const md = (window.marked && window.marked.parse) ? window.marked.parse(content) : sanitizePlain(content);
            
            aiSummaryPopupContent.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold text-emerald-700">Kết quả phân tích AI</h4>
                        <span class="text-xs text-gray-500">Cập nhật: ${new Date().toLocaleString()}</span>
                    </div>
                    <div class="text-[15px] leading-7 space-y-4 prose prose-sm max-w-none overflow-y-auto max-h-[70vh] pr-2">
                        ${md}
                    </div>
                </div>`;
        }
        
        // Hàm mở AI Summary popup
        function openAiSummaryPopup() {
            const modal = document.getElementById('aiSummaryPopupModal');
            if (modal) {
                modal.classList.remove('hidden');
                const aiSummaryPopupContent = document.getElementById('aiSummaryPopupContent');
                
                // Luôn load full content từ AI, không dùng preview
                const holder = document.getElementById('aiSummaryHolder');
                if (holder && holder.innerHTML.includes('Hoàn thành')) {
                    // Lấy full content từ AI summary đã load
                    loadFullAiContentForPopup();
                } else {
                    // Nếu chưa có kết quả, load mới
                    loadAiSummaryForPopup();
                }
            }
        }
        
        // Hàm load full AI content cho popup
        async function loadFullAiContentForPopup() {
            const aiSummaryPopupContent = document.getElementById('aiSummaryPopupContent');
            if (!aiSummaryPopupContent) return;
            
            aiSummaryPopupContent.innerHTML = `
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="text-emerald-800 font-medium">Đang tải nội dung đầy đủ...</span>
                    </div>
                </div>`;
            
            try {
                // Gọi API để lấy full content
                const breakdownsData = {
                    breakdowns: @json($data['breakdowns'] ?? []),
                    actions: @json($data['actions'] ?? []),
                    stats: @json($data['stats'] ?? []),
                    totals: @json($data['totals'] ?? []),
                    performanceStats: @json($data['performanceStats'] ?? []),
                    last7Days: @json($data['last7Days'] ?? []),
                    statusStats: @json($data['statusStats'] ?? [])
                };
                
                const data = await fetchAiSummaryOnce(breakdownsData, false);
                const text = (data && data.summary) ? data.summary : 'Không nhận được kết quả từ AI.';
                await renderAiSummaryPopupContent(text);
            } catch (error) {
                console.error('AI Summary error:', error);
                await renderAiSummaryPopupContent('Lỗi gọi AI. Vui lòng thử lại.');
            }
        }
        
        // Hàm load AI Summary cho popup
        async function loadAiSummaryForPopup() {
            const aiSummaryPopupContent = document.getElementById('aiSummaryPopupContent');
            if (!aiSummaryPopupContent) return;
            
            aiSummaryPopupContent.innerHTML = `
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="text-emerald-800 font-medium">Đang phân tích dữ liệu...</span>
                    </div>
                </div>`;
            
            try {
                // Chuẩn bị data breakdowns từ view để gửi cho AI
                const breakdownsData = {
                    breakdowns: @json($data['breakdowns'] ?? []),
                    actions: @json($data['actions'] ?? []),
                    stats: @json($data['stats'] ?? []),
                    totals: @json($data['totals'] ?? []),
                    performanceStats: @json($data['performanceStats'] ?? []),
                    last7Days: @json($data['last7Days'] ?? []),
                    statusStats: @json($data['statusStats'] ?? [])
                };
                
                const data = await fetchAiSummaryOnce(breakdownsData, window._aiDebug || false);
                if (data && data.debug) {
                    console.log('AI metrics (debug):', data.metrics);
                    console.log('Breakdowns data sent:', breakdownsData);
                    await renderAiSummaryPopupContent('Đang ở chế độ debug – xem metrics trong console.');
                } else {
                    const text = (data && data.summary) ? data.summary : 'Không nhận được kết quả từ AI.';
                    await renderAiSummaryPopupContent(text);
                }
            } catch (error) {
                console.error('AI Summary error:', error);
                await renderAiSummaryPopupContent('Lỗi gọi AI. Vui lòng thử lại.');
            }
        }

        function sanitizePlain(t) {
            const esc = t.replace(/[&<>]/g, (s) => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[s]));
            // Bold **text**
            let html = esc.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
            // Convert lines beginning with * or - to bullet list
            const lines = html.split(/\n+/);
            let out = ''; let inList = false;
            for (const line of lines) {
                const trimmed = line.trim();
                if (/^(\*|-)\s+/.test(trimmed)) {
                    if (!inList) { out += '<ul class="list-disc ml-5 space-y-1">'; inList = true; }
                    out += '<li>' + trimmed.replace(/^(\*|-)+\s+/, '') + '</li>';
                } else {
                    if (inList) { out += '</ul>'; inList = false; }
                    if (trimmed) out += '<p>' + trimmed + '</p>';
                }
            }
            if (inList) out += '</ul>';
            return out;
        }

        // Filter Logic - Sửa lỗi SPA conflict
        function initPage() {
            // Chỉ chạy trên trang Overview (có holder AI)
            const hasAiHolder = document.getElementById('aiSummaryHolder');
            if (!hasAiHolder) { return; }
            ensureChartAndInit(); 
            // Chỉ gọi AI summary một lần khi trang load
            if (!window.aiSummaryLoaded) {
                requestAiSummary(false);
                window.aiSummaryLoaded = true;
            }
            initFilterLogic();
        }
        
        // Khởi tạo khi DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPage);
        } else {
            initPage();
        }
        
        // Khởi tạo lại khi Livewire navigate (SPA)
        document.addEventListener('livewire:navigated', function() {
            // Reset flag để có thể load lại
            window.aiSummaryLoaded = false;
            initPage();
        });
        
        function initFilterLogic() {
            const btnToggleFilter = document.getElementById('btnToggleFilter');
            const filterPanel = document.getElementById('filterPanel');
            const btnCloseFilter = document.getElementById('btnCloseFilter');
            const businessFilter = document.getElementById('businessFilter');
            const accountFilter = document.getElementById('accountFilter');
            const campaignFilter = document.getElementById('campaignFilter');
            const filterForm = document.getElementById('filterForm');
            const filterCount = document.getElementById('filterCount');

            // Debug: Kiểm tra xem các element có tồn tại không
            console.log('Filter elements found:', {
                btnToggleFilter: !!btnToggleFilter,
                filterPanel: !!filterPanel,
                btnCloseFilter: !!btnCloseFilter,
                businessFilter: !!businessFilter,
                accountFilter: !!accountFilter,
                campaignFilter: !!campaignFilter,
                filterForm: !!filterForm,
                filterCount: !!filterCount
            });

            // Toggle filter panel
            if (btnToggleFilter && filterPanel) {
                btnToggleFilter.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Toggle filter clicked');
                    filterPanel.classList.toggle('hidden');
                    updateFilterCount();
                });
            } else {
                console.error('btnToggleFilter or filterPanel not found');
            }

            // Close filter panel
            if (btnCloseFilter && filterPanel) {
                btnCloseFilter.addEventListener('click', function() {
                    filterPanel.classList.add('hidden');
                });
            }

            // Business Manager filter change
            if (businessFilter) {
                businessFilter.addEventListener('change', function() {
                    const selectedBusinessId = this.value;
                    filterAccountsByBusiness(selectedBusinessId);
                    filterCampaignsByAccount('');
                    filterPagesByBusiness(selectedBusinessId);
                    updateFilterCount();
                    
                    // Không auto submit, chỉ cập nhật filter count
                    console.log('Business filter changed, waiting for manual submit');
                });
            }

            // Account filter change
            if (accountFilter) {
                accountFilter.addEventListener('change', function() {
                    const selectedAccountId = this.value;
                    filterCampaignsByAccount(selectedAccountId);
                    updateFilterCount();
                    
                    // Không auto submit, chỉ cập nhật filter count
                    console.log('Account filter changed, waiting for manual submit');
                });
            }

            // Campaign filter change
            if (campaignFilter) {
                campaignFilter.addEventListener('change', function() {
                    updateFilterCount();
                    
                    // Không auto submit, chỉ cập nhật filter count
                    console.log('Campaign filter changed, waiting for manual submit');
                });
            }

            // Form submit - Chỉ submit khi nhấn "Áp dụng bộ lọc"
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    updateFilterCount();
                    showFilterLoading();
                    
                    // Scroll to results after form submit
                    setTimeout(function() {
                        const resultsSection = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-4.gap-6');
                        if (resultsSection) {
                            resultsSection.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'start' 
                            });
                        }
                    }, 100);
                });
            }

            // Các filter khác - Không auto submit
            const contentTypeFilter = document.querySelector('select[name="content_type"]');
            const statusFilter = document.querySelector('select[name="status"]');
            const fromFilter = document.querySelector('input[name="from"]');
            const toFilter = document.querySelector('input[name="to"]');
            
            if (contentTypeFilter) {
                contentTypeFilter.addEventListener('change', function() {
                    updateFilterCount();
                    console.log('Content type filter changed, waiting for manual submit');
                });
            }
            
            if (statusFilter) {
                statusFilter.addEventListener('change', function() {
                    updateFilterCount();
                    console.log('Status filter changed, waiting for manual submit');
                });
            }
            
            if (fromFilter) {
                fromFilter.addEventListener('change', function() {
                    updateFilterCount();
                    console.log('From date filter changed, waiting for manual submit');
                });
            }
            
            if (toFilter) {
                toFilter.addEventListener('change', function() {
                    updateFilterCount();
                    console.log('To date filter changed, waiting for manual submit');
                });
            }

            // Initialize filter count
            updateFilterCount();
        }

        function filterAccountsByBusiness(businessId) {
            const accountFilter = document.getElementById('accountFilter');
            if (!accountFilter) return;

            const options = accountFilter.querySelectorAll('option');
            options.forEach(option => {
                if (option.value === '') return; // Skip "Tất cả" option
                
                const accountBusinessId = option.getAttribute('data-business');
                if (businessId === '' || accountBusinessId === businessId) {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });

            // Reset account selection if current selection is not valid
            if (businessId !== '' && accountFilter.value !== '') {
                const selectedOption = accountFilter.querySelector(`option[value="${accountFilter.value}"]`);
                if (selectedOption && selectedOption.disabled) {
                    accountFilter.value = '';
                }
            }
            
            // Reset campaign selection when business changes
            const campaignFilter = document.getElementById('campaignFilter');
            if (campaignFilter) {
                campaignFilter.value = '';
            }
        }

        function filterCampaignsByAccount(accountId) {
            const campaignFilter = document.getElementById('campaignFilter');
            if (!campaignFilter) return;

            const options = campaignFilter.querySelectorAll('option');
            options.forEach(option => {
                if (option.value === '') return; // Skip "Tất cả" option
                
                const campaignAccountId = option.getAttribute('data-account');
                if (accountId === '' || campaignAccountId === accountId) {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });

            // Reset campaign selection if current selection is not valid
            if (accountId !== '' && campaignFilter.value !== '') {
                const selectedOption = campaignFilter.querySelector(`option[value="${campaignFilter.value}"]`);
                if (selectedOption && selectedOption.disabled) {
                    campaignFilter.value = '';
                }
            }
            
            // Reset page selection when account changes
            const pageFilter = document.querySelector('select[name="page_id"]');
            if (pageFilter) {
                pageFilter.value = '';
            }
        }

        function filterPagesByBusiness(businessId) {
            const pageFilter = document.querySelector('select[name="page_id"]');
            if (!pageFilter) return;

            const options = pageFilter.querySelectorAll('option');
            options.forEach(option => {
                if (option.value === '') return; // Skip "Tất cả" option
                
                const pageBusinessId = option.getAttribute('data-business');
                if (businessId === '' || pageBusinessId === businessId) {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });

            // Reset page selection if current selection is not valid
            if (businessId !== '' && pageFilter.value !== '') {
                const selectedOption = pageFilter.querySelector(`option[value="${pageFilter.value}"]`);
                if (selectedOption && selectedOption.disabled) {
                    pageFilter.value = '';
                }
            }
        }

        function updateFilterCount() {
            const filterCount = document.getElementById('filterCount');
            if (!filterCount) return;

            const form = document.getElementById('filterForm');
            if (!form) return;

            const formData = new FormData(form);
            let activeFilters = 0;

            for (let [key, value] of formData.entries()) {
                if (value && value !== '') {
                    activeFilters++;
                }
            }

            filterCount.textContent = activeFilters;
        }
        
        function showFilterLoading() {
            // Hiển thị loading indicator
            const loadingIndicator = document.createElement('div');
            loadingIndicator.id = 'filterLoading';
            loadingIndicator.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            loadingIndicator.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Đang lọc dữ liệu...';
            document.body.appendChild(loadingIndicator);
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                const loading = document.getElementById('filterLoading');
                if (loading) {
                    loading.remove();
                }
            }, 3000);
        }

        function clearFilters() {
            const form = document.getElementById('filterForm');
            if (form) {
                form.reset();
                updateFilterCount();
                
                // Reset dependent filters
                const businessFilter = document.getElementById('businessFilter');
                const accountFilter = document.getElementById('accountFilter');
                const campaignFilter = document.getElementById('campaignFilter');
                const pageFilter = document.querySelector('select[name="page_id"]');
                
                if (businessFilter) businessFilter.value = '';
                if (accountFilter) accountFilter.value = '';
                if (campaignFilter) campaignFilter.value = '';
                if (pageFilter) pageFilter.value = '';
                
                // Show all options
                if (accountFilter) {
                    const options = accountFilter.querySelectorAll('option');
                    options.forEach(option => {
                        option.style.display = '';
                        option.disabled = false;
                    });
                }
                
                if (campaignFilter) {
                    const options = campaignFilter.querySelectorAll('option');
                    options.forEach(option => {
                        option.style.display = '';
                        option.disabled = false;
                    });
                }
                
                // Redirect to overview without filters
                window.location.href = '{{ route('facebook.overview') }}';
            }
        }
        
        // Hàm làm mới dữ liệu filter
        async function refreshFilterData() {
            const refreshBtn = event.target.closest('button');
            if (refreshBtn) {
                refreshBtn.disabled = true;
                refreshBtn.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Đang tải...';
            }
            
            try {
                // Reload trang để lấy dữ liệu mới nhất
                window.location.reload();
            } catch (error) {
                console.error('Lỗi khi làm mới dữ liệu:', error);
                if (refreshBtn) {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = '<svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Làm mới dữ liệu';
                }
            }
        }

        window.addEventListener('livewire:navigated', ensureChartAndInit); // fix SPA re-init
        
        // Đảm bảo filter button hoạt động ngay cả khi JavaScript load chậm
        setTimeout(function() {
            const btnToggleFilter = document.getElementById('btnToggleFilter');
            const filterPanel = document.getElementById('filterPanel');
            
            if (btnToggleFilter && filterPanel) {
                // Xóa event listener cũ nếu có
                btnToggleFilter.removeEventListener('click', toggleFilterHandler);
                
                // Thêm event listener mới
                btnToggleFilter.addEventListener('click', toggleFilterHandler);
                
                function toggleFilterHandler(e) {
                    e.preventDefault();
                    console.log('Toggle filter clicked (fallback)');
                    filterPanel.classList.toggle('hidden');
                    updateFilterCount();
                }
            }
        }, 1000);
        </script>
    </div>
</x-layouts.app>


