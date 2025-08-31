<x-layouts.app :title="'Quản lý dữ liệu Facebook'">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Quản lý dữ liệu Facebook</h1>
        <p class="text-gray-600">Quản lý và phân tích dữ liệu từ các trang Facebook và bài viết</p>
    </div>

    <!-- Page Selection -->
    <form id="page-select-form" method="GET" action="{{ route('facebook.data-management.index') }}">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center space-x-4">
                <label for="page-select" class="text-sm font-medium text-gray-700 min-w-[120px]">
                    Chọn Trang Facebook:
                </label>
                <select id="page-select" name="page_id" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Chọn trang --</option>
                    @foreach($data['pages'] as $page)
                        <option value="{{ $page->id }}" 
                                {{ ($filters['page_id'] ?? '') == $page->id ? 'selected' : '' }}
                                data-fan-count="{{ $page->fan_count }}"
                                data-category="{{ $page->category }}">
                            {{ $page->name }} 
                            ({{ number_format($page->fan_count) }} fan{{ $page->ads_count > 0 ? ', ' . $page->ads_count . ' quảng cáo' : '' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    @if($data['selected_page'])
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $data['selected_page']->name }}</h2>
                    <div class="flex items-center space-x-2 mt-2">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ $data['selected_page']->category }}</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">{{ number_format($data['selected_page']->fan_count) }} fan</span>
                        @if($data['selected_page']->ads_count > 0)
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">{{ number_format($data['selected_page']->ads_count) }} quảng cáo</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="https://facebook.com/{{ $data['selected_page']->id }}" target="_blank" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-md hover:bg-green-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        Xem trang Facebook
                    </a>
                    <a href="{{ route('analytics.index') }}" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Analytics
                    </a>
                </div>
            </div>
        </div>

        <!-- Page Summary Stats -->
        <div id="page-summary" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tổng hợp dữ liệu Page</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600" id="total-posts">-</div>
                    <div class="text-sm text-gray-600">Tổng bài viết</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600" id="total-ads">-</div>
                    <div class="text-sm text-gray-600">Tổng quảng cáo</div>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600" id="total-spend">-</div>
                    <div class="text-sm text-gray-600">Tổng chi phí (VND)</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600" id="total-impressions">-</div>
                    <div class="text-sm text-gray-600">Tổng hiển thị</div>
                </div>
            </div>
        </div>

        <!-- Filter Toggle Button -->
        <div class="mb-6 flex items-center space-x-3">
            <button id="filter-toggle" type="button" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                Hiển thị bộ lọc
            </button>
            
            <button id="refresh-data" type="button" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Làm mới dữ liệu
            </button>
            
            <button id="debug-info" type="button" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Debug Info
            </button>
        </div>

        <!-- Filters (Hidden by default) -->
        <div id="filter-section" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 hidden">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bộ lọc</h3>
            <form id="filter-form" method="GET" action="{{ route('facebook.data-management.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <input type="hidden" name="page_id" value="{{ $filters['page_id'] ?? '' }}">
                
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                    <input type="date" id="date_from" name="date_from" 
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                    <input type="date" id="date_to" name="date_to" 
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="post_type" class="block text-sm font-medium text-gray-700 mb-1">Loại bài viết</label>
                    <select id="post_type" name="post_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tất cả</option>
                        <option value="status" {{ ($filters['post_type'] ?? '') == 'status' ? 'selected' : '' }}>Trạng thái</option>
                        <option value="photo" {{ ($filters['post_type'] ?? '') == 'photo' ? 'selected' : '' }}>Hình ảnh</option>
                        <option value="video" {{ ($filters['post_type'] ?? '') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="link" {{ ($filters['post_type'] ?? '') == 'link' ? 'selected' : '' }}>Liên kết</option>
                        <option value="event" {{ ($filters['post_type'] ?? '') == 'event' ? 'selected' : '' }}>Sự kiện</option>
                        <option value="offer" {{ ($filters['post_type'] ?? '') == 'offer' ? 'selected' : '' }}>Ưu đãi</option>
                    </select>
                </div>
                
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <input type="text" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Tìm trong nội dung..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2 lg:col-span-4 flex justify-end space-x-3">
                    <button type="button" id="clear-filters" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Xóa bộ lọc
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Áp dụng
                    </button>
                </div>
            </form>
        </div>

        <!-- AI Summary (no charts) -->
        <div id="page-charts-section" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="mt-2 bg-indigo-50 border border-indigo-200 rounded-md p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-indigo-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-indigo-800 mb-1">Nhận định AI (CMO)</div>
                        <div id="ai-summary" class="text-sm text-indigo-900">Đang tạo nhận định...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts List -->
        <div id="posts-list-container" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Danh sách bài viết</h3>
            
            @if($data['posts']->count() > 0)
                <div class="space-y-4">
                    @foreach($data['posts'] as $post)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            {{ ucfirst($post->type) }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $post->created_time->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-900 mb-3 line-clamp-3">
                                        {{ Str::limit($post->message, 200) ?: 'Không có nội dung' }}
                                    </p>
                                    
                                    <!-- Post Links -->
                                    <div class="flex items-center space-x-4 mb-3 text-sm">
                                        @if($post->permalink_url)
                                            <a href="{{ $post->permalink_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                Xem bài viết →
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-sm">Không có link bài viết</span>
                                        @endif
                                        @if($post->page_id)
                                            <a href="https://facebook.com/{{ $post->page_id }}" target="_blank" class="text-green-600 hover:text-green-800 font-medium">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                                </svg>
                                                Xem trang →
                                            </a>
                                        @endif
                                        <a href="{{ route('facebook.data-management.post-detail', ['postId' => $post->id, 'pageId' => $post->page_id]) }}" 
                                           class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            Xem chi tiết →
                                        </a>

                                    </div>
                                    
                                    <!-- Post Stats Charts -->
                                    <div class="mb-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Biểu đồ hiệu suất:</h5>
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <canvas id="post-performance-{{ $post->id }}" width="300" height="150"></canvas>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <canvas id="post-video-{{ $post->id }}" width="300" height="150"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Post Stats Summary -->
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
                                        <div class="text-center">
                                            <div class="font-semibold text-blue-600">{{ number_format($post->likes_count ?? 0) }}</div>
                                            <div class="text-gray-600">Lượt thích</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold text-green-600">{{ number_format($post->shares_count ?? 0) }}</div>
                                            <div class="text-gray-600">Chia sẻ</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold text-purple-600">{{ number_format($post->comments_count ?? 0) }}</div>
                                            <div class="text-gray-600">Bình luận</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold text-orange-600">{{ number_format($post->reactions_count ?? 0) }}</div>
                                            <div class="text-gray-600">Tương tác</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Ad Campaigns Summary -->
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="text-sm font-medium text-gray-700">Chiến dịch quảng cáo:</div>
                                            <button onclick="showAdCampaigns('{{ $post->id }}', '{{ $post->page_id }}')" 
                                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                Xem chi tiết →
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                            <div>
                                                <span class="text-gray-600">Số lần chạy:</span>
                                                <span class="font-semibold text-purple-600 ml-1">{{ number_format($post->ad_count ?? 0) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Chi phí:</span>
                                                <span class="font-semibold text-red-600 ml-1">{{ number_format($post->total_spend ?? 0, 0) }} VND</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Hiển thị:</span>
                                                <span class="font-semibold text-blue-600 ml-1">{{ number_format($post->total_impressions ?? 0) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Click:</span>
                                                <span class="font-semibold text-green-600 ml-1">{{ number_format($post->total_clicks ?? 0) }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Video Metrics -->
                                        @if(($post->total_video_views ?? 0) > 0 || ($post->total_video_plays ?? 0) > 0)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <div class="text-sm font-medium text-gray-700 mb-2">Thống kê video:</div>
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-600">Lượt xem:</span>
                                                        <span class="font-semibold text-blue-600 ml-1">{{ number_format($post->total_video_views ?? 0) }}</span>
                                                    </div>
                                                    @if(($post->total_video_plays ?? 0) > 0)
                                                    <div>
                                                        <span class="text-gray-600">Lượt phát:</span>
                                                        <span class="font-semibold text-green-600 ml-1">{{ number_format($post->total_video_plays) }}</span>
                                                    </div>
                                                    @endif
                                                    @if(($post->total_video_p75_watched_actions ?? 0) > 0)
                                                    <div>
                                                        <span class="text-gray-600">Xem 75%:</span>
                                                        <span class="font-semibold text-orange-600 ml-1">{{ number_format($post->total_video_p75_watched_actions) }}</span>
                                                    </div>
                                                    @endif
                                                    @if(($post->total_video_p100_watched_actions ?? 0) > 0)
                                                    <div>
                                                        <span class="text-gray-600">Xem 100%:</span>
                                                        <span class="font-semibold text-purple-600 ml-1">{{ number_format($post->total_video_p100_watched_actions) }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- CTR và Performance -->
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <div class="text-sm font-medium text-gray-700 mb-2">Hiệu suất:</div>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                <div>
                                                    <span class="text-gray-600">CTR:</span>
                                                    <span class="font-semibold text-blue-600 ml-1">{{ number_format(($post->avg_ctr ?? 0) * 100, 2) }}%</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">CPC:</span>
                                                    <span class="font-semibold text-red-600 ml-1">{{ number_format($post->avg_cpc ?? 0, 0) }} VND</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">CPM:</span>
                                                    <span class="font-semibold text-orange-600 ml-1">{{ number_format($post->avg_cpm ?? 0, 0) }} VND</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Conversions:</span>
                                                    <span class="font-semibold text-green-600 ml-1">{{ number_format($post->total_conversions ?? 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col space-y-2 ml-4">
                                    @if($post->permalink_url)
                                        <a href="{{ $post->permalink_url }}" target="_blank" 
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Xem bài viết
                                        </a>
                                    @endif
                                    
                                    @if($data['selected_page'])
                                        <a href="https://facebook.com/{{ $data['selected_page']->id }}" target="_blank"
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Xem trang
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không có bài viết nào</h3>
                    <p class="mt-1 text-sm text-gray-500">Không tìm thấy bài viết nào phù hợp với bộ lọc hiện tại.</p>
                </div>
            @endif
        </div>

        <!-- Spending Statistics -->
        @if(!empty($data['spending_stats']['posts']))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Thống kê chi phí theo bài viết</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bài viết</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đăng</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi phí (VND)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hiển thị</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Click</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPC (VND)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPM (VND)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data['spending_stats']['posts'] as $stat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ Str::limit($stat->message, 50) ?: 'Không có nội dung' }}
                                        </div>
                                        @if($stat->permalink_url)
                                            <a href="{{ $stat->permalink_url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                                                Xem bài viết →
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($stat->created_time)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                        {{ number_format($stat->total_spend, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->total_impressions) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->total_clicks) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->avg_cpc, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->avg_cpm, 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Tổng cộng</td>
                                <td class="px-6 py-4"></td>
                                <td class="px-6 py-4 text-sm font-bold text-red-600">
                                    {{ number_format($data['spending_stats']['summary']['total_spend'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($data['spending_stats']['summary']['total_impressions']) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($data['spending_stats']['summary']['total_clicks']) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($data['spending_stats']['summary']['avg_cpc'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($data['spending_stats']['summary']['avg_cpm'], 0) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    @else
        <!-- No Page Selected -->
        <div id="no-page-selected" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa chọn trang Facebook</h3>
            <p class="mt-1 text-sm text-gray-500">Vui lòng chọn một trang Facebook từ dropdown bên trên để xem dữ liệu.</p>
        </div>
    @endif
</div>

<!-- Content Area for AJAX -->
<div id="content-area">
    <!-- Content will be loaded here via AJAX -->
</div>
    
    <!-- No JavaScript Notice -->
    <noscript>
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">JavaScript bị tắt</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        Để sử dụng đầy đủ tính năng, vui lòng bật JavaScript trong trình duyệt.
                    </p>
                </div>
            </div>
        </div>
    </noscript>



<script>
// Function to initialize the page
function initializeDataManagement() {
    const pageSelect = document.getElementById('page-select');
    const filterForm = document.getElementById('filter-form');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const contentArea = document.getElementById('content-area');
    const filterToggle = document.getElementById('filter-toggle');
    const filterSection = document.getElementById('filter-section');
    const noPageSelected = document.getElementById('no-page-selected');
    const refreshDataBtn = document.getElementById('refresh-data');
    const debugInfoBtn = document.getElementById('debug-info');
    const postsListContainer = document.getElementById('posts-list-container');
    
    // Filter toggle functionality
    if (filterToggle && filterSection) {
        filterToggle.addEventListener('click', function() {
            const isHidden = filterSection.classList.contains('hidden');
            if (isHidden) {
                filterSection.classList.remove('hidden');
                filterToggle.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Ẩn bộ lọc
                `;
            } else {
                filterSection.classList.add('hidden');
                filterToggle.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                    </svg>
                    Hiển thị bộ lọc
                `;
            }
        });
    }
    
    // Hide no-page-selected message when page is selected
    function hideNoPageMessage() {
        if (noPageSelected) {
            noPageSelected.style.display = 'none';
        }
    }
    
    // Show no-page-selected message when no page is selected
    function showNoPageMessage() {
        if (noPageSelected) {
            noPageSelected.style.display = 'block';
        }
    }
    
    // Load page data via AJAX
    function loadPageData(pageId, filters = {}) {
        if (!pageId) return Promise.resolve(); 
        
        // Hide no-page message
        hideNoPageMessage();
        
        // Show loading
        if (contentArea) {
            contentArea.innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-sm text-gray-600">Đang tải dữ liệu cho page ${pageId}...</p>
                    <p class="mt-1 text-xs text-gray-500">Vui lòng chờ trong giây lát</p>
                </div>
            `;
        }
        
        // Build query string
        const params = new URLSearchParams({ page_id: pageId, ...filters });
        
        // Make AJAX request
        console.log('Loading page data for:', pageId, 'with filters:', filters);
        return fetch(`{{ route('facebook.data-management.page-data') }}?${params}`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                if (contentArea) {
                    // Không update URL vì dùng AJAX
                    // Chỉ lưu state để có thể refresh page
                    window.history.replaceState({
                        pageId: pageId,
                        filters: filters
                    }, '', window.location.pathname);
                    
                    // Render content
                    renderPageContent(data);
                    // Render charts immediately
                    try { renderPageCharts(data); } catch (e) { console.warn('renderPageCharts error', e); }
                    try { renderDecisionCharts(data); } catch (e) { console.warn('renderDecisionCharts error', e); }
                    try { requestAiSummary(pageId, data); } catch (e) { console.warn('requestAiSummary error', e); }
                }
                return data;
            })
            .catch(error => {
                console.error('Error loading page data:', error);
                if (contentArea) {
                    contentArea.innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-red-600 mb-4">
                                <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-red-900 mb-2">Lỗi khi tải dữ liệu</h3>
                            <p class="text-sm text-red-600 mb-4">${error.message}</p>
                            <button onclick="location.reload()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Thử lại
                            </button>
                        </div>
                    `;
                }
                throw error;
            });
    }
    
    // Load page charts data
    function loadPageCharts(pageId) {
        if (!pageId) return;
        
        // Load page summary data for charts
        fetch(`{{ route('facebook.data-management.page-data') }}?page_id=${pageId}`)
            .then(response => response.json())
            .then(data => {
                renderPageCharts(data);
                renderDecisionCharts(data);
                requestAiSummary(pageId, data);
            })
            .catch(error => {
                console.error('Error loading page charts:', error);
            });
    }
    
    // Render single overview chart
    function renderPageCharts(data) {
        const el = document.getElementById('overview-chart');
        if (!el) return;
        if (window.overviewChart) window.overviewChart.destroy();

        const s = data.page_summary || {};
        const totalSpend = Math.round(s.total_spend || 0);
        const totalImpressions = Math.round(s.total_impressions || 0);
        // Fallback aggregate from posts if summary missing
        const totalClicks = Math.round(
            (typeof s.total_clicks !== 'undefined' && s.total_clicks !== null) ? s.total_clicks : (
                (data.spending_stats && data.spending_stats.summary && typeof data.spending_stats.summary.total_clicks !== 'undefined')
                    ? data.spending_stats.summary.total_clicks
                    : (data.posts || []).reduce((acc, p) => acc + (p.total_clicks || 0), 0)
            )
        );
        const ctrPercent = Number((
            (typeof s.avg_ctr !== 'undefined' && s.avg_ctr !== null) ? (s.avg_ctr * 100) : (
                totalImpressions > 0 ? (totalClicks / totalImpressions) * 100 : 0
            )
        ).toFixed(2));

        window.overviewChart = new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Chi phí (VND)', 'Hiển thị', 'Click', 'CTR (%)'],
                datasets: [
                    { label: 'Chi phí (VND)', data: [totalSpend, null, null, null], backgroundColor: '#EF4444', yAxisID: 'ySpend' },
                    { label: 'Hiển thị', data: [null, totalImpressions, null, null], backgroundColor: '#3B82F6', yAxisID: 'yCount' },
                    { label: 'Click', data: [null, null, totalClicks, null], backgroundColor: '#10B981', yAxisID: 'yCount' },
                    { label: 'CTR (%)', data: [null, null, null, ctrPercent], backgroundColor: '#F59E0B', yAxisID: 'yCtr' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    ySpend: { beginAtZero: true, position: 'left', title: { display: true, text: 'Chi phí (VND)' } },
                    yCount: { beginAtZero: true, position: 'left', grid: { drawOnChartArea: false }, title: { display: true, text: 'Số lượng' } },
                    yCtr: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'CTR (%)' } }
                }
            }
        });
        }
        
    // Remove decision charts (only overview kept)
    function renderDecisionCharts() { /* no-op */ }

    // AI Summary request
    function requestAiSummary(pageId, data) {
        const el = document.getElementById('ai-summary');
        if (!el) return;
        const metrics = { breakdowns: data.breakdowns || {}, summary: data.page_summary || {}, topPosts: (data.posts||[]).slice(0,5) };
        const params = new URLSearchParams({ page_id: pageId, date_from: '', date_to: '' });
        fetch(`{{ route('facebook.data-management.ai-summary') }}?${params}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ page_id: pageId, metrics }) })
            .then(r => r.json()).then(res => { el.textContent = res.summary || 'Không có nhận định.'; })
            .catch(() => { el.textContent = 'Không tạo được nhận định AI.'; });
    }

        // Render page content
    function renderPageContent(data) {
        if (!contentArea) return;
        
        // Update summary numbers (no charts)
        if (data.page_summary) {
            const s = data.page_summary;
            const elSpend = document.getElementById('summary-spend');
            const elImpr = document.getElementById('summary-impressions');
            const elClicks = document.getElementById('summary-clicks');
            const elCtr = document.getElementById('summary-ctr');

            if (elSpend) elSpend.textContent = numberFormat(Math.round(s.total_spend || 0));
            if (elImpr) elImpr.textContent = numberFormat(Math.round(s.total_impressions || 0));

            const totalClicks = (typeof s.total_clicks !== 'undefined' && s.total_clicks !== null)
                ? Math.round(s.total_clicks)
                : Math.round((data.spending_stats && data.spending_stats.summary && data.spending_stats.summary.total_clicks) ? data.spending_stats.summary.total_clicks : 0);
            if (elClicks) elClicks.textContent = numberFormat(totalClicks);

            const ctrPercent = Number(((typeof s.avg_ctr !== 'undefined' && s.avg_ctr !== null) ? (s.avg_ctr * 100) : (s.total_impressions > 0 ? (totalClicks / s.total_impressions) * 100 : 0)).toFixed(2));
            if (elCtr) elCtr.textContent = `${ctrPercent}%`;
        }
        
        let html = '';
        
        // Posts List
        if (data.posts && data.posts.length > 0) {
            // Hiển thị thông báo thành công
            html += `
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                Đã tải thành công ${data.posts.length} bài viết
                            </p>
                        </div>
                    </div>
                </div>
            `;
            
            // Charts section at top of content (follows selected page)
            html += `
                <div id="dynamic-charts" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Biểu đồ tổng hợp của Page</h3>
                    <div><canvas id="overview-chart" class="w-full" style="height:180px" height="180"></canvas></div>
                </div>`;

            // Placeholders for posts and pagination
            html += `<div id="posts-paginated"></div>
                     <div id="posts-pagination" class="flex items-center justify-center space-x-3 mt-4">
                        <button id="btn-prev" class="px-3 py-1 border rounded disabled:opacity-50">Trước</button>
                        <span id="page-info" class="text-sm text-gray-600"></span>
                        <button id="btn-next" class="px-3 py-1 border rounded disabled:opacity-50">Sau</button>
                     </div>`;

            // Commit content
            contentArea.innerHTML = html;

            // Client-side pagination
            const pageSize = 10;
            const totalPosts = data.posts.length;
            const totalPages = Math.max(1, Math.ceil(totalPosts / pageSize));
            let current = 1;

            function renderPostsSlice(page) {
                const start = (page - 1) * pageSize;
                const slice = data.posts.slice(start, start + pageSize);
                let postsHtml = '';
                slice.forEach(post => {
                    postsHtml += `
                    <div class=\"border border-gray-200 rounded-lg p-4 hover:bg-gray-50 mb-4\">
                        <div class=\"flex items-start justify-between\"> 
                            <div class=\"flex-1\">
                                <div class=\"flex items-center space-x-2 mb-2\"> 
                                    <span class=\"px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full\">${post.type || 'post'}</span>
                                    <span class=\"text-sm text-gray-500\">${post.created_time ? new Date(post.created_time).toLocaleDateString('vi-VN') : 'N/A'}</span>
                                    </div>
                                <p class=\"text-gray-900 mb-3 line-clamp-3\">${post.message || 'Không có nội dung'}</p>
                                <div class=\"flex items-center space-x-4 mb-3 text-sm\">
                                    ${post.permalink_url ? `<a href=\"${post.permalink_url}\" target=\"_blank\" class=\"text-blue-600 hover:text-blue-800 font-medium\">Xem bài viết →</a>` : ''}
                                    ${post.page_id ? `<a href=\"https://facebook.com/${post.page_id}\" target=\"_blank\" class=\"text-green-600 hover:text-green-800 font-medium\">Xem trang →</a>` : ''}
                                    <a href=\"/facebook/data-management/post/${post.id}/page/${post.page_id}\" class=\"text-sm text-purple-600 hover:text-purple-800 font-medium\">Xem chi tiết →</a>
                                    </div>
                                <div class=\"grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3\">
                                    <div class=\"text-center\"><div class=\"text-lg font-extrabold text-blue-700\">${numberFormat(post.total_spend || 0)}</div><div class=\"text-sm font-medium text-gray-700\">Chi phí (VND)</div></div>
                                    <div class=\"text-center\"><div class=\"text-lg font-extrabold text-green-700\">${numberFormat(post.total_impressions || 0)}</div><div class=\"text-sm font-medium text-gray-700\">Hiển thị</div></div>
                                    <div class=\"text-center\"><div class=\"text-lg font-extrabold text-purple-700\">${numberFormat(post.total_clicks || 0)}</div><div class=\"text-sm font-medium text-gray-700\">Click</div></div>
                                    <div class=\"text-center\"><div class=\"text-lg font-extrabold text-orange-700\">${((post.avg_ctr || 0) * 100).toFixed(2)}%</div><div class=\"text-sm font-medium text-gray-700\">CTR</div></div>
                                    </div>
                                <div class=\"p-3 bg-gray-50 rounded-lg\">
                                    <div class=\"flex items-center justify-between mb-2\"><div class=\"text-sm font-medium text-gray-700\">Chiến dịch quảng cáo:</div><button onclick=\"showAdCampaigns('${post.id}', '${post.page_id}')\" class=\"text-sm text-blue-600 hover:text-blue-800 font-medium\">Xem chi tiết →</button></div>
                                    <div class=\"grid grid-cols-2 md:grid-cols-4 gap-3 text-sm\">
                                        <div><span class=\"text-gray-600\">Số lần chạy:</span><span class=\"font-semibold text-purple-600 ml-1\">${numberFormat(post.ad_count || 0)}</span></div>
                                        <div><span class=\"text-gray-600\">Chi phí:</span><span class=\"font-semibold text-red-600 ml-1\">${numberFormat(post.total_spend || 0)} VND</span></div>
                                        <div><span class=\"text-gray-600\">Hiển thị:</span><span class=\"font-semibold text-blue-600 ml-1\">${numberFormat(post.total_impressions || 0)}</span></div>
                                        <div><span class=\"text-gray-600\">Click:</span><span class=\"font-semibold text-green-600 ml-1\">${numberFormat(post.total_clicks || 0)}</span></div>
                                    </div>
                                    ${(post.total_video_views || 0) > 0 ? `<div class=\"mt-3 pt-3 border-t border-gray-200\"><div class=\"text-sm font-medium text-gray-700 mb-2\">Thống kê video:</div><div class=\"grid grid-cols-2 md:grid-cols-4 gap-3 text-sm\"><div><span class=\"text-gray-600\">Lượt xem:</span><span class=\"font-semibold text-blue-600 ml-1\">${numberFormat(post.total_video_views || 0)}</span></div>${(post.total_video_plays || 0) > 0 ? `<div><span class=\"text-gray-600\">Lượt phát:</span><span class=\"font-semibold text-green-600 ml-1\">${numberFormat(post.total_video_plays || 0)}</span></div>` : ''}${(post.total_video_p75_watched_actions || 0) > 0 ? `<div><span class=\"text-gray-600\">Xem 75%:</span><span class=\"font-semibold text-orange-600 ml-1\">${numberFormat(post.total_video_p75_watched_actions || 0)}</span></div>` : ''}${(post.total_video_p100_watched_actions || 0) > 0 ? `<div><span class=\"text-gray-600\">Xem 100%:</span><span class=\"font-semibold text-purple-600 ml-1\">${numberFormat(post.total_video_p100_watched_actions || 0)}</span></div>` : ''}</div></div>` : ''}
                                    <div class=\"mt-3 pt-3 border-t border-gray-200\"><div class=\"text-sm font-medium text-gray-700 mb-2\">Hiệu suất:</div><div class=\"grid grid-cols-2 md:grid-cols-4 gap-3 text-sm\"><div><span class=\"text-gray-600\">CTR:</span><span class=\"font-semibold text-blue-600 ml-1\">${((post.avg_ctr || 0) * 100).toFixed(2)}%</span></div><div><span class=\"text-gray-600\">CPC:</span><span class=\"font-semibold text-red-600 ml-1\">${numberFormat(post.avg_cpc || 0)} VND</span></div><div><span class=\"text-gray-600\">CPM:</span><span class=\"font-semibold text-orange-600 ml-1\">${numberFormat(post.avg_cpm || 0)} VND</span></div><div><span class=\"text-gray-600\">Conversions:</span><span class=\"font-semibold text-green-600 ml-1\">${numberFormat(post.total_conversions || 0)}</span></div></div></div>
                                </div>
                                    </div>
                                        </div>
                    </div>`;
                });
                return postsHtml;
            }

            function updatePager() {
                const info = document.getElementById('page-info');
                const prev = document.getElementById('btn-prev');
                const next = document.getElementById('btn-next');
                if (info) info.textContent = `Trang ${current}/${totalPages}`;
                if (prev) prev.disabled = current <= 1;
                if (next) next.disabled = current >= totalPages;
            }
            function renderCurrent() {
                const container = document.getElementById('posts-paginated');
                if (container) container.innerHTML = renderPostsSlice(current);
                updatePager();
            }
            renderCurrent();

            const prevBtn = document.getElementById('btn-prev');
            const nextBtn = document.getElementById('btn-next');
            if (prevBtn) prevBtn.addEventListener('click', function(){ if (current > 1) { current--; renderCurrent(); }});
            if (nextBtn) nextBtn.addEventListener('click', function(){ if (current < totalPages) { current++; renderCurrent(); }});

            // Render charts immediately for new canvases
            try { renderPageCharts(data); } catch (e) { console.warn('renderPageCharts error', e); }
            // Decision charts removed
            
        } else {
            html = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không có bài viết nào</h3>
                    <p class="mt-1 text-sm text-gray-500">Không tìm thấy bài viết nào phù hợp với bộ lọc hiện tại.</p>
                </div>
            `;
            contentArea.innerHTML = html;
        }
    }
    
    // Auto-load data when page changes
    if (pageSelect) {
        pageSelect.addEventListener('change', function() {
            if (this.value) {
                hideNoPageMessage();
                loadPageData(this.value);
                loadPageCharts(this.value);
            } else {
                showNoPageMessage();
            }
        });
        
        // Auto-load data if page is already selected
        if (pageSelect.value) {
            hideNoPageMessage();
            loadPageData(pageSelect.value);
            loadPageCharts(pageSelect.value);
        }
    }
    
    // Filter form submission
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const filters = {};
            
            for (let [key, value] of formData.entries()) {
                if (value) {
                    filters[key] = value;
                }
            }
            
            const pageId = pageSelect ? pageSelect.value : filters.page_id;
            if (pageId) {
                loadPageData(pageId, filters);
            }
        });
    }
    
    // Clear filters
    if (clearFiltersBtn && filterForm) {
        clearFiltersBtn.addEventListener('click', function() {
            const inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name !== 'page_id') {
                    input.value = '';
                }
            });
            
            const pageId = pageSelect ? pageSelect.value : null;
            if (pageId) {
                loadPageData(pageId);
            }
        });
    }
    
    // Date validation
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            if (dateTo.value && this.value > dateTo.value) {
                dateTo.value = this.value;
            }
        });
        
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && this.value < dateFrom.value) {
                dateFrom.value = this.value;
            }
        });
    }
    
    // Refresh data button
    if (refreshDataBtn) {
        refreshDataBtn.addEventListener('click', function() {
            const pageId = pageSelect ? pageSelect.value : null;
            if (pageId) {
                this.disabled = true;
                this.innerHTML = `
                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Đang tải...
                `;
                
                loadPageData(pageId).finally(() => {
                    this.disabled = false;
                    this.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Làm mới dữ liệu
                    `;
                });
            }
        });
    }
    
    // Debug info button
    if (debugInfoBtn) {
        debugInfoBtn.addEventListener('click', function() {
            const pageId = pageSelect ? pageSelect.value : null;
            const debugInfo = {
                'Selected Page ID': pageId,
                'Current URL': window.location.href,
                'Content Area Exists': !!contentArea,
                'Page Select Exists': !!pageSelect,
                'Filter Form Exists': !!filterForm,
                'User Agent': navigator.userAgent,
                'Timestamp': new Date().toISOString()
            };
            
            console.log('Debug Info:', debugInfo);
            alert('Debug info đã được log vào console. Mở Developer Tools để xem.');
        });
    }
    
    // Global functions for modal
    window.showAdCampaigns = function(postId, pageId) {
        const modal = document.getElementById('ad-campaigns-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalLoading = document.getElementById('modal-loading');
        const modalData = document.getElementById('modal-data');
        
        modal.classList.remove('hidden');
        modalTitle.textContent = `Chi tiết chiến dịch quảng cáo - Post ${postId}`;
        modalLoading.classList.remove('hidden');
        modalData.classList.add('hidden');
        
        // Load ad campaigns data
        fetch(`/facebook/data-management/ad-campaigns?post_id=${postId}&page_id=${pageId}`)
            .then(response => response.json())
            .then(data => {
                modalLoading.classList.add('hidden');
                modalData.classList.remove('hidden');
                renderAdCampaigns(data);
                renderCharts(data);
                document.getElementById('charts-section').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading ad campaigns:', error);
                modalLoading.classList.add('hidden');
                modalData.classList.remove('hidden');
                document.getElementById('ad-campaigns-list').innerHTML = 
                    '<div class="text-center text-red-600">Lỗi khi tải dữ liệu chiến dịch quảng cáo.</div>';
            });
    };
    
    window.closeAdCampaignsModal = function() {
        document.getElementById('ad-campaigns-modal').classList.add('hidden');
    };
    
    function renderAdCampaigns(data) {
        const container = document.getElementById('ad-campaigns-list');
        
        if (!data.campaigns || data.campaigns.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500">Không có dữ liệu chiến dịch quảng cáo.</div>';
            return;
        }
        
        let html = '';
        data.campaigns.forEach(campaign => {
            html += `
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-900">${campaign.name || 'Không có tên'}</h4>
                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            ${campaign.status || 'Unknown'}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
                        <div>
                            <span class="text-gray-600">Chi phí:</span>
                            <span class="font-semibold text-red-600 ml-1">${numberFormat(campaign.spend || 0)} VND</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Hiển thị:</span>
                            <span class="font-semibold text-blue-600 ml-1">${numberFormat(campaign.impressions || 0)}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Click:</span>
                            <span class="font-semibold text-green-600 ml-1">${numberFormat(campaign.clicks || 0)}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">CTR:</span>
                            <span class="font-semibold text-purple-600 ml-1">${((campaign.ctr || 0) * 100).toFixed(2)}%</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="showAdBreakdowns('${campaign.id}')" 
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Xem breakdown →
                        </button>
                        <button onclick="showAdInsights('${campaign.id}')" 
                                class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Xem insights →
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function renderCharts(data) {
        // Performance Chart
        const performanceCtx = document.getElementById('performance-chart').getContext('2d');
        if (window.performanceChart) {
            window.performanceChart.destroy();
        }
        
        if (data.performance_data) {
            window.performanceChart = new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: data.performance_data.labels || [],
                    datasets: [{
                        label: 'Impressions',
                        data: data.performance_data.impressions || [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Clicks',
                        data: data.performance_data.clicks || [],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }
        
        // Spend Chart
        const spendCtx = document.getElementById('spend-chart').getContext('2d');
        if (window.spendChart) {
            window.spendChart.destroy();
        }
        
        if (data.spend_data) {
            window.spendChart = new Chart(spendCtx, {
                type: 'doughnut',
                data: {
                    labels: data.spend_data.labels || [],
                    datasets: [{
                        data: data.spend_data.values || [],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(251, 146, 60, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
    }
    
    function numberFormat(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }
    
    // Thêm CSS cho line-clamp
    const style = document.createElement('style');
    style.textContent = `
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    `;
    document.head.appendChild(style);
    
    // Global functions for breakdowns and insights
    window.showAdBreakdowns = function(campaignId) {
        // Load breakdown data
        fetch(`/facebook/data-management/ad-breakdowns?ad_id=${campaignId}`)
            .then(response => response.json())
            .then(data => {
                let html = '<div class="space-y-4">';
                
                if (Object.keys(data).length === 0) {
                    html += '<div class="text-center text-gray-500">Không có dữ liệu breakdown.</div>';
                } else {
                    Object.keys(data).forEach(breakdownType => {
                        html += `<h4 class="text-md font-medium text-gray-900">${breakdownType.toUpperCase()}</h4>`;
                        html += '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
                        html += '<thead class="bg-gray-50"><tr>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá trị</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi phí</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hiển thị</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Click</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CTR</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video Views</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P75</th>';
                        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P100</th>';
                        html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                        
                        data[breakdownType].forEach(item => {
                            html += '<tr>';
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.breakdown_value}</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.spend)} VND</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.impressions)}</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.clicks)}</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${((item.ctr || 0) * 100).toFixed(2)}%</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.video_views)}</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.video_p75_watched_actions)}</td>`;
                            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.video_p100_watched_actions)}</td>`;
                            html += '</tr>';
                        });
                        
                        html += '</tbody></table></div>';
                    });
                }
                
                html += '</div>';
                
                // Show in modal
                const modal = document.getElementById('ad-campaigns-modal');
                const modalTitle = document.getElementById('modal-title');
                const modalData = document.getElementById('modal-data');
                
                modal.classList.remove('hidden');
                modalTitle.textContent = `Breakdown Data - Ad ${campaignId}`;
                modalData.classList.remove('hidden');
                document.getElementById('ad-campaigns-list').innerHTML = html;
                document.getElementById('charts-section').classList.add('hidden');
            })
            .catch(error => {
                console.error('Error loading breakdown data:', error);
                alert('Lỗi khi tải dữ liệu breakdown');
            });
    };
    
    window.showAdInsights = function(campaignId) {
        // Load insights data
        fetch(`/facebook/data-management/ad-insights?ad_id=${campaignId}`)
            .then(response => response.json())
            .then(data => {
                let html = '<div class="space-y-6">';
                
                // Summary section
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += '<h4 class="text-md font-medium text-gray-900 mb-3">Tổng quan</h4>';
                html += '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">';
                html += `<div><span class="text-gray-600">Tổng chi phí:</span><span class="font-semibold text-red-600 ml-1">${numberFormat(data.summary.total_spend)} VND</span></div>`;
                html += `<div><span class="text-gray-600">Tổng hiển thị:</span><span class="font-semibold text-blue-600 ml-1">${numberFormat(data.summary.total_impressions)}</span></div>`;
                html += `<div><span class="text-gray-600">Tổng click:</span><span class="font-semibold text-green-600 ml-1">${numberFormat(data.summary.total_clicks)}</span></div>`;
                html += `<div><span class="text-gray-600">CTR trung bình:</span><span class="font-semibold text-purple-600 ml-1">${data.summary.avg_ctr.toFixed(2)}%</span></div>`;
                html += `<div><span class="text-gray-600">Video Views:</span><span class="font-semibold text-orange-600 ml-1">${numberFormat(data.summary.total_video_views)}</span></div>`;
                html += `<div><span class="text-gray-600">Video P75:</span><span class="font-semibold text-indigo-600 ml-1">${numberFormat(data.summary.total_video_p75_watched_actions)}</span></div>`;
                html += `<div><span class="text-gray-600">Video P100:</span><span class="font-semibold text-pink-600 ml-1">${numberFormat(data.summary.total_video_p100_watched_actions)}</span></div>`;
                html += `<div><span class="text-gray-600">CPC trung bình:</span><span class="font-semibold text-yellow-600 ml-1">${numberFormat(data.summary.avg_cpc)} VND</span></div>`;
                html += '</div></div>';
                
                // Daily data table
                if (data.daily_data.length > 0) {
                    html += '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
                    html += '<thead class="bg-gray-50"><tr>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi phí</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hiển thị</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Click</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CTR</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video Views</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P75</th>';
                    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P100</th>';
                    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                    
                    data.daily_data.forEach(item => {
                        html += '<tr>';
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.date}</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.spend)} VND</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.impressions)}</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.clicks)}</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${((item.ctr || 0) * 100).toFixed(2)}%</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.video_views)}</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.video_p75_watched_actions)}</td>`;
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${numberFormat(item.video_p100_watched_actions)}</td>`;
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class="text-center text-gray-500">Không có dữ liệu daily insights.</div>';
                }
                
                html += '</div>';
                
                // Show in modal
                const modal = document.getElementById('ad-campaigns-modal');
                const modalTitle = document.getElementById('modal-title');
                const modalData = document.getElementById('modal-data');
                
                modal.classList.remove('hidden');
                modalTitle.textContent = `Insights Data - Ad ${campaignId}`;
                modalData.classList.remove('hidden');
                document.getElementById('ad-campaigns-list').innerHTML = html;
                document.getElementById('charts-section').classList.add('hidden');
            })
            .catch(error => {
                console.error('Error loading insights data:', error);
                alert('Lỗi khi tải dữ liệu insights');
            });
    };
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeDataManagement);

// Initialize on Livewire navigation
document.addEventListener('livewire:navigated', initializeDataManagement);

// Initialize on Turbo navigation (if using Turbo)
document.addEventListener('turbo:load', initializeDataManagement);

// Initialize on page show (for SPA navigation)
document.addEventListener('pageshow', initializeDataManagement);
</script>

<!-- Charts Section -->
<div id="charts-section" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Biểu đồ tổng hợp của Page</h3>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">Hiệu suất theo thời gian</h4>
            <canvas id="performance-chart" width="400" height="200"></canvas>
        </div>
        
        <!-- Spend Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">Phân bổ chi phí</h4>
            <canvas id="spend-chart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Ad Campaigns Modal -->
<div id="ad-campaigns-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900">Chi tiết chiến dịch quảng cáo</h3>
                <button onclick="closeAdCampaignsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Loading -->
            <div id="modal-loading" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Đang tải dữ liệu...
                </div>
            </div>
            
            <!-- Modal Data -->
            <div id="modal-data" class="hidden">
                <div id="ad-campaigns-list" class="space-y-4 mb-6"></div>
            </div>
        </div>
    </div>
</div>

<!-- Footer navigation removed per request -->
<!--

-->

</x-layouts.app> 