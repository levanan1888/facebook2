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
                    <div class="flex items-center space-x-2">
                        <button type="button" id="btnClearFilter" class="text-red-500 hover:text-red-700 text-sm" title="Xóa bộ lọc">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Xóa bộ lọc
                        </button>
                        <button type="button" id="btnCloseFilter" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
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
                            <select name="business_id" id="business_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn Business Manager...</option>
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
                            <select name="account_id" id="account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Chọn Ad Account...</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Chiến dịch</label>
                            <select name="campaign_id" id="campaign_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Chọn Campaign...</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Ad</label>
                            <select name="ad_id" id="ad_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Chọn Ad...</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Trang Facebook</label>
                            <select name="page_id" id="page_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Chọn Page...</option>
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

            <!-- AI Summary Section - Ẩn ban đầu, chỉ hiển thị khi có kết quả -->
            <div id="aiSummaryHolder" class="mb-6 hidden">
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6">
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
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200"><div class="flex items-center"><div class="p-2 bg-purple-100 rounded-lg"><svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0021 6.894V5a2 2 0 00-2-2h-5M9 14l-4.553 2.276A2 2 0 013 17.106V19a2 2 0 002 2h5"/></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-600">Lượt phát video</p><p class="text-2xl font-bold text-gray-900">{{ number_format($agg['video_plays'] ?? 0) }}</p></div></div></div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200"><div class="flex items-center"><div class="p-2 bg-rose-100 rounded-lg"><svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-600" title="Messenger conversations started in the last 7 days (onsite_conversion.messaging_conversation_started_7d)">Conversations started (last 7 days)</p><p class="text-2xl font-bold text-gray-900">{{ number_format($agg['msg_started'] ?? 0) }}</p></div></div></div>
                
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200"><div class="flex items-center"><div class="p-2 bg-sky-100 rounded-lg"><svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div><div class="ml-4"><p class="text-sm font-medium text-gray-600">Nhấp liên kết</p><p class="text-2xl font-bold text-gray-900">{{ number_format($agg['link_click'] ?? 0) }}</p></div></div></div>
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
                @if(($data['filters']['page_id'] ?? null))
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-2 bg-cyan-100 rounded-lg">
                            <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m-2 8a9 9 0 110-18 9 9 0 010 18z"/></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tin nhắn Page (28 ngày)</p>
                            <div class="text-sm text-gray-700 space-x-3">
                                <span class="inline-flex items-center"><span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span>Organic: <span class="font-semibold ml-1">{{ number_format($agg['page_msg_28d_organic'] ?? 0) }}</span></span>
                                <span class="inline-flex items-center"><span class="w-2 h-2 bg-sky-500 rounded-full mr-2"></span>Paid: <span class="font-semibold ml-1">{{ number_format($agg['page_msg_28d_paid'] ?? 0) }}</span></span>
                                <span class="inline-flex items-center"><span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span>Total: <span class="font-semibold ml-1">{{ number_format($agg['page_msg_28d_total'] ?? 0) }}</span></span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Gần nhất: Organic {{ number_format($agg['page_msg_day_organic'] ?? 0) }}, Paid {{ number_format($agg['page_msg_day_paid'] ?? 0) }}, Total {{ number_format($agg['page_msg_day_total'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                @endif
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

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-6"></div>

            <div class="flex items-center justify-end mb-3">
                <button id="btnWidgetConfig" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Tùy chọn hiển thị biểu đồ</button>
            </div>
            <div class="flex flex-col gap-4">
                <div class="bg-white rounded-lg shadow p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Hoạt động theo thời gian</h3>
                        <div class="flex items-center flex-wrap gap-2">
                            <label for="activityChartType" class="text-xs text-gray-500">Biểu đồ</label>
                            <select id="activityChartType" class="text-sm border border-gray-300 rounded px-2 py-1">
                                <option value="bar">Cột</option>
                                <option value="line">Đường</option>
                                <option value="radar">Radar</option>
                                <option value="doughnut">Vòng</option>
                            </select>
                            <label class="inline-flex items-center space-x-1 text-xs text-gray-600"><input id="metricCampaigns" type="checkbox" class="rounded" checked><span>Campaigns</span></label>
                            <label class="inline-flex items-center space-x-1 text-xs text-gray-600"><input id="metricAds" type="checkbox" class="rounded" checked><span>Ads</span></label>
                            <label class="inline-flex items-center space-x-1 text-xs text-gray-600"><input id="metricPosts" type="checkbox" class="rounded" checked><span>Posts</span></label>
                            <label class="inline-flex items-center space-x-1 text-xs text-gray-600"><input id="metricSpend" type="checkbox" class="rounded" checked><span>Spend</span></label>
                        </div>
                    </div>
                    <div class="px-4 py-3">
                        <div class="h-72 lg:h-80">
                            <canvas id="activityChart" style="width:100%;height:100%"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compact KPI charts grid: 4 per row on large screens -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <div class="bg-white rounded-lg shadow p-4 overflow-hidden" data-widget="status">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Phân bố trạng thái Campaigns</h3>
                    <div class="h-56">
                        @if(isset($data['statusStats']['campaigns']) && count($data['statusStats']['campaigns']) > 0)
                            <canvas id="statusChart"></canvas>
                        @else
                            <div class="flex items-center justify-center h-full text-gray-500">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <p class="text-sm">Chưa có dữ liệu trạng thái</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 overflow-hidden" data-widget="video">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Tổng quan Video</h3>
                    <div class="h-56"><canvas id="videoOverviewChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 overflow-hidden" data-widget="messaging">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Tổng quan Tin nhắn</h3>
                    <div class="h-56"><canvas id="messagingOverviewChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 overflow-hidden" data-widget="device">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Thiết bị hiển thị</h3>
                    <div class="h-56"><canvas id="deviceBreakdownChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 overflow-hidden" data-widget="country">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Quốc gia (Top 10 theo hiển thị)</h3>
                    <div class="h-56"><canvas id="countryBreakdownChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 overflow-hidden" data-widget="region">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Tỉnh/Thành (Top 10 theo reach)</h3>
                    <div class="h-56"><canvas id="regionBreakdownChart"></canvas></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Top 5 Quảng cáo (theo hiệu suất)</h3></div>
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
                                        <p class="font-semibold text-gray-900">{{ number_format((($ad->perf_ctr ?? 0) * 100), 2) }}%</p>
                                        <p class="text-xs text-gray-500">CTR</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ number_format($ad->total_clicks ?? 0) }} clicks</p>
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
        <!-- Widget Config Modal -->
        <div id="widgetConfigModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-6 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Chọn biểu đồ muốn hiển thị</h3>
                    <button id="closeWidgetConfig" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="activity">
                            <span class="font-medium">Hoạt động theo thời gian</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="activity">
                            <option value="bar">Cột</option>
                            <option value="line">Đường</option>
                            <option value="radar">Radar</option>
                            <option value="doughnut">Vòng</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="status">
                            <span class="font-medium">Phân bố trạng thái Campaigns</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="status">
                            <option value="doughnut">Vòng</option>
                            <option value="pie">Tròn</option>
                            <option value="bar">Cột</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="video">
                            <span class="font-medium">Tổng quan Video</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="video">
                            <option value="bar">Cột</option>
                            <option value="line">Đường</option>
                            <option value="radar">Radar</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="messaging">
                            <span class="font-medium">Tổng quan Tin nhắn</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="messaging">
                            <option value="bar">Cột</option>
                            <option value="line">Đường</option>
                            <option value="radar">Radar</option>
                            <option value="doughnut">Vòng</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="device">
                            <span class="font-medium">Thiết bị hiển thị</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="device">
                            <option value="bar">Cột</option>
                            <option value="line">Đường</option>
                            <option value="radar">Radar</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="country">
                            <span class="font-medium">Quốc gia (Top 10)</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="country">
                            <option value="bar">Cột</option>
                            <option value="line">Đường</option>
                            <option value="radar">Radar</option>
                            <option value="doughnut">Vòng</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded" data-widget-toggle value="region">
                            <span class="font-medium">Tỉnh/Thành (Top 10)</span>
                        </label>
                        <select class="text-xs border rounded px-2 py-1" data-widget-type="region">
                            <option value="bar">Cột</option>
                            <option value="line">Đường</option>
                            <option value="radar">Radar</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end mt-5 gap-2">
                    <button id="btnWidgetReset" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Mặc định</button>
                    <button id="btnWidgetSave" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded hover:bg-emerald-700">Lưu</button>
                </div>
            </div>
        </div>
        
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
            // prevent double init
            if (window.__overviewChartsInit) return;
            window.__overviewChartsInit = true;
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
                
                function getActivityPrefs() {
                    const def = { type: 'bar', metrics: { campaigns: true, ads: true, posts: true, spend: true } };
                    try { return JSON.parse(localStorage.getItem('fb.activityChart')||'') || def; } catch(_) { return def; }
                }
                function setActivityPrefs(p) { localStorage.setItem('fb.activityChart', JSON.stringify(p)); }
                function buildDatasets(prefs) {
                    const ds = [];
                    if (prefs.metrics.campaigns) ds.push({ label: 'Chiến dịch', data: activityData.map(i=>i.campaigns), backgroundColor: 'rgba(59,130,246,0.8)', borderColor: 'rgb(59,130,246)', borderWidth: 1, borderRadius: 4, borderSkipped: false });
                    if (prefs.metrics.ads) ds.push({ label: 'Quảng cáo', data: activityData.map(i=>i.ads), backgroundColor: 'rgba(16,185,129,0.8)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 4, borderSkipped: false });
                    if (prefs.metrics.posts) ds.push({ label: 'Bài đăng', data: activityData.map(i=>i.posts), backgroundColor: 'rgba(245,158,11,0.8)', borderColor: 'rgb(245,158,11)', borderWidth: 1, borderRadius: 4, borderSkipped: false });
                    if (prefs.metrics.spend) ds.push({ label: 'Chi tiêu ($)', data: activityData.map(i=> i.spend || 0), backgroundColor: 'rgba(239,68,68,0.8)', borderColor: 'rgb(239,68,68)', borderWidth: 1, borderRadius: 4, borderSkipped: false, yAxisID: 'y1' });
                    return ds;
                }
                function syncControls(prefs){
                    const t1=document.getElementById('activityChartType');
                    const t2=document.getElementById('activityChartTypeSm');
                    if(t1) t1.value=prefs.type; if(t2) t2.value=prefs.type;
                    const set = (id,val)=>{ const el=document.getElementById(id); if(el) el.checked=val; };
                    set('metricCampaigns', prefs.metrics.campaigns); set('metricCampaignsSm', prefs.metrics.campaigns);
                    set('metricAds', prefs.metrics.ads); set('metricAdsSm', prefs.metrics.ads);
                    set('metricPosts', prefs.metrics.posts); set('metricPostsSm', prefs.metrics.posts);
                    set('metricSpend', prefs.metrics.spend); set('metricSpendSm', prefs.metrics.spend);
                }
                function collectPrefs(){
                    const val = (id, def)=>{ const el=document.getElementById(id); return el? !!el.checked : def; };
                    const typeSel = document.getElementById('activityChartType') || document.getElementById('activityChartTypeSm');
                    return {
                        type: typeSel ? typeSel.value : 'bar',
                        metrics: {
                            campaigns: val('metricCampaigns', true) || val('metricCampaignsSm', true),
                            ads: val('metricAds', true) || val('metricAdsSm', true),
                            posts: val('metricPosts', true) || val('metricPostsSm', true),
                            spend: val('metricSpend', true) || val('metricSpendSm', true)
                        }
                    };
                }
                function renderActivity(prefs){
                    window.__fbCharts.activity && window.__fbCharts.activity.destroy();
                    window.__fbCharts.activity = new Chart(activityCtx, { 
                        type: prefs.type, 
                        data: { 
                            labels: formattedLabels, 
                            datasets: buildDatasets(prefs)
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
                        scales: (['pie','doughnut','radar'].includes(prefs.type)) ? {} : { 
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

                const prefs = getActivityPrefs();
                syncControls(prefs);
                renderActivity(prefs);
                // Mini chart (7 days) for right column
                const miniEl = document.getElementById('activityMiniChart');
                if (miniEl) {
                    const mctx = miniEl.getContext('2d');
                    if (window.__fbCharts.activityMini) window.__fbCharts.activityMini.destroy();
                    window.__fbCharts.activityMini = new Chart(mctx, {
                        type: 'line',
                        data: { labels: formattedLabels, datasets: [{ data: activityData.map(i=>i.campaigns||0), borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,.2)', fill: true, tension: .35 }]},
                        options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{display:false}}, scales:{ x:{display:false}, y:{display:false} } }
                    });
                }
                const ids = ['activityChartType','activityChartTypeSm','metricCampaigns','metricCampaignsSm','metricAds','metricAdsSm','metricPosts','metricPostsSm','metricSpend','metricSpendSm'];
                ids.forEach(id=>{
                    const el=document.getElementById(id); if(!el) return;
                    el.addEventListener('change', ()=>{ const p=collectPrefs(); setActivityPrefs(p); renderActivity(p); });
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

            const videoEl = document.getElementById('videoOverviewChart');
            const msgEl = document.getElementById('messagingOverviewChart');
            const agg = @json($data['overviewAgg'] ?? []);
            // Read preferred widget types (force video to bar/line; map old radar to bar)
            function getWidgetTypesMain(){
                try{
                    const obj = JSON.parse(localStorage.getItem('fb.widgets.types')||'') || {};
                    if (obj.video === 'radar') obj.video = 'bar';
                    if (obj.video && !['bar','line'].includes(obj.video)) obj.video = 'bar';
                    return obj;
                }catch(_){ return {}; }
            }
            const widgetTypesMain = getWidgetTypesMain();
            if (videoEl) {
                const vctx = videoEl.getContext('2d');
                window.__fbCharts.video && window.__fbCharts.video.destroy();
                const plays = Number(agg.video_views||0);
                const p25 = Number(agg.v_p25||agg.video_p25_watched_actions||0);
                const p50 = Number(agg.v_p50||agg.video_p50_watched_actions||0);
                const p75 = Number(agg.v_p75||agg.video_p75_watched_actions||0);
                const p95 = Number(agg.v_p95||agg.video_p95_watched_actions||0);
                const p100 = Number(agg.v_p100||agg.video_p100_watched_actions||0);
                const tp = Number(agg.thruplays||0);
                const v30s = Number(agg.video_30s||agg.video_30_sec_watched||0);
                let vType = widgetTypesMain.video || 'bar';
                if (!['bar','line'].includes(vType)) vType = 'bar';
                const dataset = {
                    label: 'Video',
                    data: [plays,p25,p50,p75,p95,p100,tp,v30s].map(n => Number(n||0)),
                    backgroundColor: vType==='line' ? 'rgba(99,102,241,0.25)' : ['#6366F1','#93C5FD','#60A5FA','#A78BFA','#F472B6','#EC4899','#10B981','#F59E0B'],
                    borderColor: vType==='line' ? '#6366F1' : undefined,
                    pointBackgroundColor: vType==='line' ? '#6366F1' : undefined,
                };
                const cfg = {
                    type: vType,
                    data: { labels: ['Plays','P25','P50','P75','P95','P100','Thruplays','30s'], datasets: [dataset] },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                    }
                };
                if (vType === 'bar') cfg.options.scales = { y: { beginAtZero: true } };
                window.__fbCharts.video = new Chart(vctx, cfg);
            }
            if (msgEl) {
                const mctx = msgEl.getContext('2d');
                window.__fbCharts.msg && window.__fbCharts.msg.destroy();
                window.__fbCharts.msg = new Chart(mctx, {
                    type: widgetTypesMain.messaging || 'bar',
                    data: {
                        labels: ['Conversations started (7 days)','Message replies (7 days)','Welcome message views','Total messaging connections'],
                        datasets: [{
                            label: 'Tin nhắn',
                            data: [Number(agg.msg_started||0), Number(agg.msg_replied||0), Number(agg.msg_welcome||0), Number(agg.msg_total||0)],
                            backgroundColor: ['#06B6D4','#10B981','#60A5FA','#F59E0B']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx)=> `${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}` } } } }
                });
            }

            // Render breakdown charts (device & country)
            const breakdowns = @json($data['breakdowns'] ?? []);
            const deviceChartEl = document.getElementById('deviceBreakdownChart');
            const countryChartEl = document.getElementById('countryBreakdownChart');
            const regionChartEl = document.getElementById('regionBreakdownChart');
            function pickDeviceBucket(bd){
                if (bd && bd.impression_device) return bd.impression_device;
                if (bd && bd.device_platform) return bd.device_platform;
                if (bd && bd.action_device) return bd.action_device;
                return null;
            }
            if (deviceChartEl) {
                const bucket = pickDeviceBucket(breakdowns) || {};
                const labels = Object.keys(bucket);
                const values = labels.map(k => Number((bucket[k]?.impressions)||0));
                const dctx = deviceChartEl.getContext('2d');
                window.__fbCharts.device && window.__fbCharts.device.destroy();
                window.__fbCharts.device = new Chart(dctx, {
                    type: widgetTypesMain.device || 'bar',
                    data: { labels, datasets: [{ label: 'Impressions', data: values, backgroundColor: '#93C5FD' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
            if (countryChartEl) {
                const bucket = (breakdowns && breakdowns.country) ? breakdowns.country : {};
                const entries = Object.entries(bucket).map(([k,v]) => ({ label:k, val: Number((v?.impressions)||0) }));
                entries.sort((a,b)=>b.val-a.val);
                const top = entries.slice(0,10);
                const labels = top.map(x=>x.label);
                const values = top.map(x=>x.val);
                const cctx = countryChartEl.getContext('2d');
                window.__fbCharts.country && window.__fbCharts.country.destroy();
                window.__fbCharts.country = new Chart(cctx, {
                    type: widgetTypesMain.country || 'bar',
                    data: { labels, datasets: [{ label: 'Impressions', data: values, backgroundColor: '#F59E0B' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
            if (regionChartEl) {
                const bucket = (breakdowns && breakdowns.region) ? breakdowns.region : {};
                const entries = Object.entries(bucket).map(([k,v]) => ({ label:k, val: Number((v?.impressions)||0) }));
                entries.sort((a,b)=>b.val-a.val);
                const top = entries.slice(0,10);
                const labels = top.map(x=>x.label);
                const values = top.map(x=>x.val);
                const rctx = regionChartEl.getContext('2d');
                window.__fbCharts.region && window.__fbCharts.region.destroy();
                window.__fbCharts.region = new Chart(rctx, {
                    type: widgetTypesMain.region || 'bar',
                    data: { labels, datasets: [{ label: 'Impressions', data: values, backgroundColor: '#34D399' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
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
            // Kiểm tra xem đã có kết quả AI chưa
            if (!isManual && window.aiSummaryLoaded) {
                console.log('AI summary already loaded, skipping...');
                return;
            }
            
            // Hiển thị chat AI với trạng thái loading
            showAiChat();
            updateAiChatMessage('Đang phân tích dữ liệu và tạo báo cáo tổng quan...');
            
            const chatStatus = document.getElementById('aiChatStatus');
            if (chatStatus) {
                chatStatus.textContent = 'Đang phân tích...';
            }
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
            // Hiển thị chat AI thay vì phần lớn
            showAiChat();
            updateAiChatMessage(content);
            
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
            
            // Cập nhật chat AI với content đã format
            updateAiChatMessage(md);
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
            
            // Load charts trước
            ensureChartAndInit(); 
            
            // Init filter logic
            initFilterLogic();
            
            // Delay AI summary để không block UI
            if (!window.aiSummaryLoaded) {
                setTimeout(() => {
                    requestAiSummary(false);
                    window.aiSummaryLoaded = true;
                }, 2000); // Tăng delay để trang load xong trước
            }
        }
        
        // Lazy initialization để tránh block Livewire navigation
        function initOverviewPage() {
            // Chỉ khởi tạo nếu chưa có instance
            if (!window.__overviewInit) {
                initPage();
                window.__overviewInit = true;
            }
        }
        
        // Delay initialization để Livewire hoàn thành navigation
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(initOverviewPage, 200);
            });
        } else {
            setTimeout(initOverviewPage, 200);
        }
        
        // Khởi tạo lại khi Livewire navigate (SPA) với delay
        document.addEventListener('livewire:navigated', function() {
            // Reset flags để có thể load lại
            window.aiSummaryLoaded = false;
            window.__overviewInit = false;
            window.__filterInit = false;
            // Delay để tránh conflict
            setTimeout(initOverviewPage, 150);
        });

        // Widget visibility preferences
        (function initWidgetPrefs(){
            const KEY='fb.widgets.visible';
            const defaults=['activity','status','video','messaging','device','country','region'];
            function get(){ try{ return JSON.parse(localStorage.getItem(KEY)||'') || defaults; }catch(_){ return defaults; } }
            function set(arr){ localStorage.setItem(KEY, JSON.stringify(arr)); }
            function apply(arr){
                const all = document.querySelectorAll('[data-widget]');
                all.forEach(el=>{ const id=el.getAttribute('data-widget'); el.style.display = arr.includes(id)? '':'none'; });
            }
            // initial apply
            const vis = get(); apply(vis);
            // open/close modal
            const modal=document.getElementById('widgetConfigModal');
            const btn=document.getElementById('btnWidgetConfig');
            const close=document.getElementById('closeWidgetConfig');
            const btnSave=document.getElementById('btnWidgetSave');
            const btnReset=document.getElementById('btnWidgetReset');
            function syncChecks(arr){
                document.querySelectorAll('[data-widget-toggle]').forEach(chk=>{
                    chk.checked = arr.includes(chk.value);
                });
            }
            if (btn && modal && close && btnSave && btnReset){
                btn.addEventListener('click', ()=>{ 
                    syncChecks(get()); 
                    modal.classList.remove('hidden');
                    // render previews (lazy load Chart.js if needed)
                    const ensureChart = () => new Promise(res=>{ if(window.Chart) return res(); const s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/chart.js'; s.onload=res; document.head.appendChild(s); });
                    ensureChart().then(()=>{
                        try { renderPreviewCharts(); } catch(e) { console.warn('preview error', e); }
                    });
                });
                const hide=()=> modal.classList.add('hidden');
                close.addEventListener('click', hide); modal.addEventListener('click', e=>{ if(e.target===modal) hide(); });
                btnReset.addEventListener('click', ()=>{ syncChecks(defaults); });
                btnSave.addEventListener('click', ()=>{
                    const arr=[]; document.querySelectorAll('[data-widget-toggle]').forEach(chk=>{ if(chk.checked) arr.push(chk.value); });
                    set(arr); apply(arr); hide();
                });
            }
            function getWidgetTypes(){
                const KEY_T='fb.widgets.types';
                const def={activity:'bar',status:'doughnut',video:'bar',messaging:'bar',device:'bar',country:'bar',region:'bar'};
                try{ return JSON.parse(localStorage.getItem(KEY_T)||'') || def; }catch(_){ return def; }
            }
            function setWidgetTypes(obj){ localStorage.setItem('fb.widgets.types', JSON.stringify(obj)); }

            function renderPreviewCharts(){
                const last7 = @json($data['last7Days'] ?? []);
                const labels = last7.map(i=> (i.date ? new Date(i.date).toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit' }) : ''));
                const smallOpts = { plugins:{legend:{display:false}}, scales:{x:{display:false}, y:{display:false}}, responsive:true, maintainAspectRatio:false };
                const use = (id, cfg) => { const el=document.getElementById(id); if(!el) return; const ctx=el.getContext('2d'); if(el._c) { el._c.destroy(); } el._c=new Chart(ctx, cfg); };
                const types = getWidgetTypes();
                function renderOne(key){
                    if(key==='activity'){
                        use('preview-activity', { type: types.activity || 'line', data:{ labels, datasets:[{ data:last7.map(i=>i.ads||0), borderColor:'#10B981', backgroundColor:'rgba(16,185,129,0.25)', fill: types.activity==='line'? false : true }] }, options: smallOpts });
                        return;
                    }
                    if(key==='status'){
                        const st = @json($data['statusStats']['campaigns'] ?? []);
                        use('preview-status', { type: types.status || 'doughnut', data:{ labels:Object.keys(st), datasets:[{ data:Object.values(st), backgroundColor:['#10B981','#F59E0B','#EF4444','#6B7280'] }] }, options: smallOpts });
                        return;
                    }
                    if(key==='video'){
                        const ag = @json($data['overviewAgg'] ?? []);
                        use('preview-video', { type: types.video || 'bar', data:{ labels:['Plays','Thruplays'], datasets:[{ data:[Number(ag.video_views||0), Number(ag.thruplays||0)], backgroundColor:['#6366F1','#10B981'] }] }, options: smallOpts });
                        return;
                    }
                    if(key==='messaging'){
                        const ag = @json($data['overviewAgg'] ?? []);
                        use('preview-messaging', { type: types.messaging || 'bar', data:{ labels:['Start','Replies'], datasets:[{ data:[Number(ag.msg_started||0), Number(ag.msg_replied||0)], backgroundColor:['#06B6D4','#60A5FA'] }] }, options: smallOpts });
                        return;
                    }
                    if(key==='device'){
                        const dev = @json(($data['breakdowns']['impression_device'] ?? $data['breakdowns']['device_platform'] ?? $data['breakdowns']['action_device'] ?? []) );
                        const dLabels = Object.keys(dev || {}).slice(0,3);
                        const dVals = dLabels.map(k=> Number((dev[k]?.impressions)||0));
                        use('preview-device', { type: types.device || 'bar', data:{ labels:dLabels, datasets:[{ data:dVals, backgroundColor:'#93C5FD' }] }, options: smallOpts });
                        return;
                    }
                    if(key==='country'){
                        const ctry = @json($data['breakdowns']['country'] ?? []);
                        const cEntries = Object.entries(ctry||{}).map(([k,v])=>({k, v:Number((v?.impressions)||0)})).sort((a,b)=>b.v-a.v).slice(0,3);
                        use('preview-country', { type: types.country || 'bar', data:{ labels:cEntries.map(x=>x.k), datasets:[{ data:cEntries.map(x=>x.v), backgroundColor:'#F59E0B' }] }, options: smallOpts });
                        return;
                    }
                    if(key==='region'){
                        const reg = @json($data['breakdowns']['region'] ?? []);
                        const rEntries = Object.entries(reg||{}).map(([k,v])=>({k, v:Number((v?.impressions)||0)})).sort((a,b)=>b.v-a.v).slice(0,3);
                        use('preview-region', { type: types.region || 'bar', data:{ labels:rEntries.map(x=>x.k), datasets:[{ data:rEntries.map(x=>x.v), backgroundColor:'#34D399' }] }, options: smallOpts });
                    }
                }
                // initial render all
                ['activity','status','video','messaging','device','country','region'].forEach(renderOne);

                // Sync selects with current types
                document.querySelectorAll('[data-widget-type]').forEach(sel=>{
                    const key=sel.getAttribute('data-widget-type'); sel.value = types[key] || sel.value;
                    sel.addEventListener('change', ()=>{
                        const t = getWidgetTypes();
                        t[key] = sel.value; setWidgetTypes(t); renderOne(key);
                    });
                });
            }
        })();
        
        function initFilterLogic() {
            // Chỉ khởi tạo filter nếu chưa có
            if (window.__filterInit) return;
            window.__filterInit = true;
            
            const btnToggleFilter = document.getElementById('btnToggleFilter');
            const filterPanel = document.getElementById('filterPanel');
            const btnCloseFilter = document.getElementById('btnCloseFilter');
            const businessFilter = document.getElementById('business_id');
            const accountFilter = document.getElementById('account_id');
            const campaignFilter = document.getElementById('campaign_id');
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

            // Toggle filter panel - Ẩn/hiện filter panel
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

            // Clear filter button
            const btnClearFilter = document.getElementById('btnClearFilter');
            if (btnClearFilter) {
                btnClearFilter.addEventListener('click', function() {
                    if (window.hierarchicalFilter) {
                        window.hierarchicalFilter.reset();
                        // Reload page to apply cleared filters
                        window.location.reload();
                    }
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
                    e.preventDefault();
                    updateFilterCount();
                    
                    // Show loading overlay với animation mượt mà
                    const loadingOverlay = createSmoothLoadingOverlay();
                    document.body.appendChild(loadingOverlay);
                    
                    // Animate overlay in
                    setTimeout(() => {
                        loadingOverlay.classList.add('opacity-100');
                    }, 10);
                    
                    // Submit form với delay để user thấy loading
                    setTimeout(() => {
                        // Scroll to results smoothly
                        const resultsSection = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-4.gap-6');
                        if (resultsSection) {
                            resultsSection.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'start' 
                            });
                        }
                        
                        // Submit form
                        this.submit();
                    }, 500);
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
            const accountFilter = document.getElementById('account_id');
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
            const campaignFilter = document.getElementById('campaign_id');
            if (campaignFilter) {
                campaignFilter.value = '';
            }
        }

        function filterCampaignsByAccount(accountId) {
            const campaignFilter = document.getElementById('campaign_id');
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
        
        function createSmoothLoadingOverlay() {
            const overlay = document.createElement('div');
            overlay.id = 'filterLoadingOverlay';
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-500 ease-in-out opacity-0';
            overlay.innerHTML = `
                <div class="bg-white rounded-xl p-8 shadow-2xl max-w-md w-full mx-4 transform transition-all duration-500 ease-out scale-95">
                    <div class="text-center">
                        <div class="relative mb-6">
                            <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600 mx-auto"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Đang áp dụng bộ lọc</h3>
                        <p class="text-gray-600 mb-6">Đang tải dữ liệu theo bộ lọc của bạn...</p>
                        
                        <!-- Progress bar với animation -->
                        <div class="bg-gray-200 rounded-full h-3 mb-4 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full animate-pulse" style="width: 0%; animation: progressBar 2s ease-in-out infinite;"></div>
                        </div>
                        
                        <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
                            <div class="animate-bounce">•</div>
                            <div class="animate-bounce" style="animation-delay: 0.1s">•</div>
                            <div class="animate-bounce" style="animation-delay: 0.2s">•</div>
                        </div>
                    </div>
                </div>
                
                <style>
                    @keyframes progressBar {
                        0% { width: 0%; }
                        50% { width: 70%; }
                        100% { width: 100%; }
                    }
                </style>
            `;
            
            // Animate scale in
            setTimeout(() => {
                const content = overlay.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            }, 50);
            
            return overlay;
        }
        
        function removeSmoothLoadingOverlay() {
            const overlay = document.getElementById('filterLoadingOverlay');
            if (overlay) {
                // Animate out
                overlay.classList.remove('opacity-100');
                const content = overlay.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                }
                
                // Remove after animation
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.remove();
                    }
                }, 500);
            }
        }

        function clearFilters() {
            const form = document.getElementById('filterForm');
            if (form) {
                form.reset();
                updateFilterCount();
                
                // Reset dependent filters
                const businessFilter = document.getElementById('business_id');
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
        
        // Fallback filter button với debounce để tránh duplicate listeners
        let fallbackTimeout;
        function initFallbackFilter() {
            clearTimeout(fallbackTimeout);
            fallbackTimeout = setTimeout(function() {
                const btnToggleFilter = document.getElementById('btnToggleFilter');
                const filterPanel = document.getElementById('filterPanel');
                
                if (btnToggleFilter && filterPanel && !window.__filterInit) {
                    // Chỉ thêm nếu chưa có event listener
                    btnToggleFilter.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Toggle filter clicked (fallback)');
                        filterPanel.classList.toggle('hidden');
                        updateFilterCount();
                    });
                }
            }, 1000);
        }
        
        // Chỉ chạy fallback nếu filter chưa được init
        if (!window.__filterInit) {
            initFallbackFilter();
        }
        
        // Auto remove loading overlay khi trang load xong
        window.addEventListener('load', function() {
            setTimeout(() => {
                removeSmoothLoadingOverlay();
            }, 1000);
        });
        
        // Remove loading overlay khi navigate
        document.addEventListener('livewire:navigated', function() {
            removeSmoothLoadingOverlay();
        });

        // AI Chat Assistant Functions
        function toggleAiChat() {
            const chatAssistant = document.getElementById('aiChatAssistant');
            const chatContent = document.getElementById('aiChatContent');
            const chatStatus = document.getElementById('aiChatStatus');
            
            if (chatContent.classList.contains('hidden')) {
                chatContent.classList.remove('hidden');
                chatStatus.textContent = 'Đã sẵn sàng';
            } else {
                chatContent.classList.add('hidden');
                chatStatus.textContent = 'Đang phân tích...';
            }
        }

        function showAiChat() {
            const chatAssistant = document.getElementById('aiChatAssistant');
            console.log('showAiChat called, chatAssistant:', chatAssistant);
            if (chatAssistant) {
                chatAssistant.classList.remove('hidden');
                console.log('AI Chat Assistant shown');
            } else {
                console.error('aiChatAssistant element not found');
            }
        }

        function updateAiChatMessage(message) {
            const chatText = document.getElementById('aiChatText');
            const chatStatus = document.getElementById('aiChatStatus');
            
            if (chatText) {
                chatText.innerHTML = message;
            }
            if (chatStatus) {
                chatStatus.textContent = 'Hoàn thành';
            }
        }
        </script>
        
        <!-- Hierarchical Filter Script -->
        <script src="{{ asset('js/hierarchical-filter.js') }}"></script>
    </div>

    <!-- AI Chat Assistant - Fixed bottom right -->
    <div id="aiChatAssistant" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg border border-emerald-200 max-w-sm">
            <!-- Chat Header -->
            <div class="bg-emerald-600 text-white px-4 py-3 rounded-t-lg cursor-pointer" onclick="toggleAiChat()">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span class="font-medium text-sm">AI Assistant</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span id="aiChatStatus" class="text-xs text-emerald-200">Đang phân tích...</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Chat Content -->
            <div id="aiChatContent" class="p-4 max-h-96 overflow-y-auto hidden">
                <div id="aiChatMessage" class="text-sm text-gray-700">
                    <div class="flex items-start space-x-2">
                        <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="bg-gray-100 rounded-lg p-3">
                                <p id="aiChatText">Đang phân tích dữ liệu và tạo báo cáo tổng quan...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>


