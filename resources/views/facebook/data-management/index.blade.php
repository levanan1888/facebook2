<x-layouts.app :title="'Quản lý dữ liệu Facebook'">
@php
    $currentUser = auth()->user();
    $__canViewDm = $currentUser && (
        (method_exists($currentUser, 'hasRole') && ($currentUser->hasRole('admin') || $currentUser->hasRole('super-admin')))
        || (method_exists($currentUser, 'can') && $currentUser->can('facebook.data-management.view'))
    );
@endphp
@if($__canViewDm)
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Quản lý dữ liệu Facebook</h1>
        <p class="text-gray-600">Quản lý và phân tích dữ liệu từ các trang Facebook và bài viết</p>
    </div>

    <!-- Page Selection -->
    <form id="page-select-form" method="GET" action="{{ route('facebook.data-management.index') }}">
        <!-- Card 1: Page selection only -->
        <div class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <label for="page-select" class="text-sm font-medium text-gray-700 min-w-[120px]">
                    Chọn Trang Facebook:
                </label>
                <select id="page-select" name="page_id" class="flex-1 min-w-[280px] rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Chọn trang --</option>
                    @foreach($data['pages'] as $page)
                        <option value="{{ $page->id }}" 
                                {{ ($filters['page_id'] ?? '') == $page->id ? 'selected' : '' }}
                                data-fan-count="{{ $page->fan_count }}"
                                data-category="{{ $page->category }}"
                                data-name="{{ Str::lower($page->name) }}"
                                data-ads="{{ (int) $page->ads_count }}"
                                data-created="{{ isset($page->created_time) ? \Carbon\Carbon::parse($page->created_time)->timestamp : (isset($page->created_at) ? \Carbon\Carbon::parse($page->created_at)->timestamp : 0) }}">
                            {{ $page->name }} 
                            ({{ number_format($page->fan_count) }} fan{{ $page->ads_count > 0 ? ', ' . $page->ads_count . ' quảng cáo' : '' }})
                        </option>
                    @endforeach
                </select>

                <!-- Quick search and sort for Page list -->
                <input id="page-search" type="text" placeholder="Tìm theo tên/ID Page..." class="w-56 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <select id="page-sort" class="rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="name_asc">Tên A-Z</option>
                    <option value="name_desc">Tên Z-A</option>
                    <option value="created_desc">Ngày tạo mới nhất</option>
                    <option value="created_asc">Ngày tạo cũ nhất</option>
                    <option value="ads_desc">Quảng cáo nhiều nhất</option>
                    <option value="ads_asc">Quảng cáo ít nhất</option>
                </select>
            </div>
        </div>

    </form>

    @if($data['selected_page'])
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 mb-6">
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
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 shadow">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        Xem trang Facebook
                    </a>
                    <a href="{{ route('analytics.index') }}" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 shadow">
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

        <!-- Multi-dimension panels (accordion) -->
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Content type -->
            <div class="border rounded-md">
                <button type="button" class="w-full text-left px-4 py-2 font-medium bg-gray-50 border-b" data-acc="content-panel">Loại nội dung</button>
                <div id="content-panel" class="p-4 grid grid-cols-2 gap-2">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" class="bd-content" value="post"> <span>Bài viết</span></label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" class="bd-content" value="ad"> <span>Quảng cáo</span></label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" class="bd-content" value="video"> <span>Video</span></label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" class="bd-content" value="photo"> <span>Ảnh</span></label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" class="bd-content" value="live"> <span>Livestream</span></label>
                </div>
            </div>

            <!-- Audience -->
            <div class="border rounded-md">
                <button type="button" class="w-full text-left px-4 py-2 font-medium bg-gray-50 border-b" data-acc="audience-panel">Đối tượng</button>
                <div id="audience-panel" class="p-4 grid grid-cols-2 gap-2">
                    <select id="bd-gender" class="rounded-md border-gray-300">
                        <option value="">Giới tính</option>
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="unknown">Khác</option>
                    </select>
                    <select id="bd-age" class="rounded-md border-gray-300">
                        <option value="">Độ tuổi</option>
                        <option value="18-24">18-24</option>
                        <option value="25-34">25-34</option>
                        <option value="35-44">35-44</option>
                        <option value="45-54">45-54</option>
                        <option value="55-64">55-64</option>
                        <option value="65+">65+</option>
                    </select>
                    <input id="bd-region" class="col-span-2 rounded-md border-gray-300" placeholder="Vị trí địa lý (tỉnh/thành, quốc gia)..." />
                    <select id="bd-device" class="rounded-md border-gray-300 col-span-2">
                        <option value="">Thiết bị</option>
                        <option value="mobile">Mobile</option>
                        <option value="desktop">Desktop</option>
                    </select>
                </div>
            </div>

            <!-- Channel -->
            <div class="border rounded-md">
                <button type="button" class="w-full text-left px-4 py-2 font-medium bg-gray-50 border-b" data-acc="channel-panel">Kênh</button>
                <div id="channel-panel" class="p-4 grid grid-cols-3 gap-2">
                    <label class="inline-flex items-center gap-2"><input type="radio" name="bd-channel" class="bd-channel" value=""> <span>Tất cả</span></label>
                    <label class="inline-flex items-center gap-2"><input type="radio" name="bd-channel" class="bd-channel" value="organic"> <span>Organic</span></label>
                    <label class="inline-flex items-center gap-2"><input type="radio" name="bd-channel" class="bd-channel" value="paid"> <span>Paid</span></label>
                    <label class="inline-flex items-center gap-2"><input type="radio" name="bd-channel" class="bd-channel" value="viral"> <span>Viral</span></label>
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

            <!-- Slice analysis selector -->
            <div class="ml-auto flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Cắt lát theo:</label>
                <select id="slice-by" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="none">Không</option>
                    <option value="page">Page</option>
                    <option value="post">Post</option>
                    <option value="ad">Ad</option>
                    <option value="date">Ngày</option>
                </select>
            </div>
        </div>

        <!-- Filters (Hidden by default) -->
        <div id="filter-section" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bộ lọc</h3>
            <form id="filter-form" method="GET" action="{{ route('facebook.data-management.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <input type="hidden" name="page_id" value="{{ $filters['page_id'] ?? '' }}">
                <div class="md:col-span-2 lg:col-span-4">
                    <label for="date_preset" class="block text-sm font-medium text-gray-700 mb-1">Khoảng thời gian nhanh</label>
                    <select id="date_preset" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tùy chỉnh</option>
                        <option value="today">Hôm nay</option>
                        <option value="yesterday">Hôm qua</option>
                        <option value="this_week">Tuần này</option>
                        <option value="last_week">Tuần trước</option>
                        <option value="last_7_days">7 ngày qua</option>
                        <option value="last_28_days">28 ngày qua</option>
                        <option value="last_30_days">30 ngày qua</option>
                        <option value="this_month">Tháng này</option>
                        <option value="last_month">Tháng trước</option>
                        <option value="this_quarter">Quý này</option>
                        <option value="last_quarter">Quý trước</option>
                        <option value="lifetime">Toàn thời gian</option>
                    </select>
                </div>
                <div>
                    <label for="time_increment" class="block text-sm font-medium text-gray-700 mb-1">Nhóm thời gian</label>
                    <select id="time_increment" name="time_increment" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="day">Theo ngày</option>
                        <option value="week">Theo tuần</option>
                        <option value="month">Theo tháng</option>
                    </select>
                </div>
                
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
        <div id="page-charts-section" class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-6">
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
        <div id="posts-list-container" class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Danh sách bài viết</h3>
            
            @if($data['posts']->count() > 0)
                <div class="space-y-4">
                    @foreach($data['posts'] as $post)
                        <div class="border border-gray-200 rounded-xl shadow-sm p-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            {{ ucfirst($post->type) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
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
                                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
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
                                    <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                                        <div class="text-center">
                                            <div class="font-semibold {{ ($post->likes_count ?? 0) > 0 ? 'text-blue-600' : 'text-gray-400' }}">💙 {{ number_format($post->likes_count ?? 0) }}</div>
                                            <div class="text-gray-600">Lượt thích</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold {{ ($post->shares_count ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }}">🔁 {{ number_format($post->shares_count ?? 0) }}</div>
                                            <div class="text-gray-600">Chia sẻ</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold {{ ($post->comments_count ?? 0) > 0 ? 'text-purple-600' : 'text-gray-400' }}">💬 {{ number_format($post->comments_count ?? 0) }}</div>
                                            <div class="text-gray-600">Bình luận</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold {{ ($post->reactions_count ?? 0) > 0 ? 'text-orange-600' : 'text-gray-400' }}">❤️ {{ number_format($post->reactions_count ?? 0) }}</div>
                                            <div class="text-gray-600">Tương tác</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Ad Campaigns Summary -->
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="text-sm font-medium text-gray-700">Chiến dịch quảng cáo:</div>
                                            <button onclick="showAdCampaigns('{{ $post->id }}', '{{ $post->page_id }}')" 
                                                    class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">
                                                Xem chi tiết →
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <span class="text-gray-600">Số lần chạy:</span>
                                                <span class="font-semibold {{ ($post->ad_count ?? 0) > 0 ? 'text-purple-600' : 'text-gray-400' }} ml-1">{{ number_format($post->ad_count ?? 0) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Chi phí:</span>
                                                <span class="font-semibold {{ ($post->total_spend ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }} ml-1">💰 {{ number_format($post->total_spend ?? 0, 0) }} VND</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Hiển thị:</span>
                                                <span class="font-semibold {{ ($post->total_impressions ?? 0) > 0 ? 'text-blue-600' : 'text-gray-400' }} ml-1">👀 {{ number_format($post->total_impressions ?? 0) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Click:</span>
                                                <span class="font-semibold {{ ($post->total_clicks ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }} ml-1">🖱️ {{ number_format($post->total_clicks ?? 0) }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Video Metrics -->
                                        @if(($post->total_video_views ?? 0) > 0 || ($post->total_video_plays ?? 0) > 0)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <div class="text-sm font-medium text-gray-700 mb-2">Thống kê video:</div>
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-600">Lượt xem:</span>
                                                        <span class="font-semibold {{ ($post->total_video_views ?? 0) > 0 ? 'text-blue-600' : 'text-gray-400' }} ml-1">{{ number_format($post->total_video_views ?? 0) }}</span>
                                                    </div>
                                                    @if(($post->total_video_plays ?? 0) > 0)
                                                    <div>
                                                        <span class="text-gray-600">Lượt phát:</span>
                                                        <span class="font-semibold {{ ($post->total_video_plays ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }} ml-1">{{ number_format($post->total_video_plays) }}</span>
                                                    </div>
                                                    @endif
                                                    @if(($post->total_video_p75_watched_actions ?? 0) > 0)
                                                    <div>
                                                        <span class="text-gray-600">Xem 75%:</span>
                                                        <span class="font-semibold {{ ($post->total_video_p75_watched_actions ?? 0) > 0 ? 'text-orange-600' : 'text-gray-400' }} ml-1">{{ number_format($post->total_video_p75_watched_actions) }}</span>
                                                    </div>
                                                    @endif
                                                    @if(($post->total_video_p100_watched_actions ?? 0) > 0)
                                                    <div>
                                                        <span class="text-gray-600">Xem 100%:</span>
                                                        <span class="font-semibold {{ ($post->total_video_p100_watched_actions ?? 0) > 0 ? 'text-purple-600' : 'text-gray-400' }} ml-1">{{ number_format($post->total_video_p100_watched_actions) }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- CTR và Performance -->
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <div class="text-sm font-medium text-gray-700 mb-2">Hiệu suất:</div>
                                            <div class="grid grid-cols-2 gap-3 text-sm">
                                                <div>
                                                    <span class="text-gray-600">CTR:</span>
                                                    <span class="font-semibold {{ (($post->avg_ctr ?? 0) * 100) > 0 ? 'text-orange-600' : 'text-gray-400' }} ml-1">📈 {{ number_format(($post->avg_ctr ?? 0) * 100, 2) }}%</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">CPC:</span>
                                                    <span class="font-semibold {{ ($post->avg_cpc ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }} ml-1">{{ number_format($post->avg_cpc ?? 0, 0) }} VND</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">CPM:</span>
                                                    <span class="font-semibold {{ ($post->avg_cpm ?? 0) > 0 ? 'text-orange-600' : 'text-gray-400' }} ml-1">{{ number_format($post->avg_cpm ?? 0, 0) }} VND</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Conversions:</span>
                                                    <span class="font-semibold {{ ($post->total_conversions ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }} ml-1">{{ number_format($post->total_conversions ?? 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col space-y-2 ml-4">
                                    @if($post->permalink_url)
                                        <a href="{{ $post->permalink_url }}" target="_blank" 
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-xl shadow hover:bg-blue-700">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Xem bài viết
                                        </a>
                                    @endif
                                    
                                    @if($data['selected_page'])
                                        <a href="https://facebook.com/{{ $data['selected_page']->id }}" target="_blank"
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-xl shadow hover:bg-blue-700">
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
    if (window.__dmInit) return; // đảm bảo chỉ gắn handler 1 lần cho vòng đời trang hiện tại
    window.__dmInit = true;
    
    // Initialize cache if not exists
    if (!window.__dmCache) {
        window.__dmCache = new Map();
    }
    const pageSelect = document.getElementById('page-select');
    const pageSearch = document.getElementById('page-search');
    const pageSort = document.getElementById('page-sort');
    const datePreset = document.getElementById('date_preset');
    const quickPreset = document.getElementById('quick_date_preset');
    const quickFrom = document.getElementById('quick_from');
    const quickTo = document.getElementById('quick_to');
    const quickApply = document.getElementById('quick_apply');
    const viewTypeInput = document.getElementById('view_type');
    const viewTabButtons = document.querySelectorAll('.view-tab');
    const sliceBySelect = document.getElementById('slice-by');
    const filterForm = document.getElementById('filter-form');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const contentArea = document.getElementById('content-area');
    const filterToggle = document.getElementById('filter-toggle');
    const filterSection = document.getElementById('filter-section');
    const noPageSelected = document.getElementById('no-page-selected');
    const refreshDataBtn = document.getElementById('refresh-data');
    const debugInfoBtn = document.getElementById('debug-info');
    const postsListContainer = document.getElementById('posts-list-container');
    const aiSummaryEl = document.getElementById('ai-summary');
    
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
        
        // Check cache first
        const cacheKey = `page_${pageId}_${JSON.stringify(filters)}`;
        if (window.__dmCache && window.__dmCache.has(cacheKey)) {
            console.log('Loading from cache:', cacheKey);
            const cachedData = window.__dmCache.get(cacheKey);
            hideNoPageMessage();
            if (contentArea) {
                renderPageContent(cachedData);
                try { renderPageCharts(cachedData); } catch (e) { console.warn('renderPageCharts error', e); }
            }
            return Promise.resolve(cachedData);
        }
        
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
        const timeIncrementSel = document.getElementById('time_increment');
        const params = new URLSearchParams({ 
            page_id: pageId, 
            view_type: (viewTypeInput?.value || 'combined'), 
            slice_by: (sliceBySelect?.value || 'none'),
            time_increment: (timeIncrementSel?.value || 'day'),
            ...filters 
        });
        
        // Make AJAX request with timeout
        // console.debug('Loading page data', { pageId, filters });
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 15000); // 15s timeout
        
        return fetch(`{{ route('facebook.data-management.page-data') }}?${params}`, {
            signal: controller.signal,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
            .then(response => {
                clearTimeout(timeoutId);
                // console.debug('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') {
                    throw new Error('Request timeout - vui lòng thử lại');
                }
                throw error;
            })
            .then(data => {
                // console.debug('Received data');
                
                // Save to cache
                if (window.__dmCache) {
                    window.__dmCache.set(cacheKey, data);
                }
                
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

    // Reset UI when không chọn trang
    function resetPageView() {
        if (contentArea) contentArea.innerHTML = '';
        // Destroy and clear overview chart if exists
        try { if (window.overviewChart) { window.overviewChart.destroy(); window.overviewChart = null; } } catch(_) {}
        const dynCharts = document.getElementById('dynamic-charts');
        if (dynCharts) dynCharts.remove();
        // Reset AI summary text
        if (aiSummaryEl) aiSummaryEl.textContent = 'Đang tạo nhận định...';
    }
    
    // loadPageCharts bị loại bỏ: mọi render biểu đồ/AI thực hiện ngay trong loadPageData()
    
    // Nạp Chart.js khi cần
    function ensureChartLib() {
        if (window.Chart) return Promise.resolve();
        return new Promise(resolve => {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            s.onload = resolve;
            document.head.appendChild(s);
        });
    }

    // Render single overview chart (lazy Chart.js)
    async function renderPageCharts(data) {
        await ensureChartLib();
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
                plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 12, font: { family: 'Inter, Roboto, system-ui', size: 11 }, color: '#333' } } },
                scales: {
                    ySpend: { beginAtZero: true, position: 'left', title: { display: true, text: 'Chi phí (VND)' }, ticks: { color: '#374151' } },
                    yCount: { beginAtZero: true, position: 'left', grid: { drawOnChartArea: false }, title: { display: true, text: 'Số lượng' }, ticks: { color: '#374151' } },
                    yCtr: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'CTR (%)' }, ticks: { color: '#374151' } }
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
        
        // Fanpage Info Card (nếu có)
        if (data.fanpage_info) {
            const fp = data.fanpage_info;
            html += `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-start gap-6 mb-6">
                        <div class="flex-shrink-0">
                            ${fp.profile_picture_url ? 
                                `<img src="${fp.profile_picture_url}" alt="avatar" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"/>` :
                                `<div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500 text-2xl">📄</span>
                                </div>`
                            }
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h2 class="text-2xl font-bold text-gray-900">${fp.name || 'Unknown Page'}</h2>
                                <a href="https://facebook.com/${fp.page_id}" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                                    </svg>
                                    Xem trên Facebook
                                </a>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Danh mục:</span>
                                    <span class="text-gray-900">${fp.category || 'Unknown'}</span>
                                </div>
                                ${fp.phone ? `
                                    <div>
                                        <span class="font-medium text-gray-700">Điện thoại:</span>
                                        <span class="text-gray-900">${fp.phone}</span>
                                    </div>
                                ` : ''}
                                ${fp.email ? `
                                    <div>
                                        <span class="font-medium text-gray-700">Email:</span>
                                        <span class="text-gray-900">${fp.email}</span>
                                    </div>
                                ` : ''}
                                ${fp.location ? `
                                    <div>
                                        <span class="font-medium text-gray-700">Địa chỉ:</span>
                                        <span class="text-gray-900">${fp.location}</span>
                                    </div>
                                ` : ''}
                                <div>
                                    <span class="font-medium text-gray-700">Trạng thái:</span>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full ${fp.is_published ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${fp.is_published ? 'Đã xuất bản' : 'Chưa xuất bản'}
                                    </span>
                                    ${fp.is_verified ? `
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full ml-2">
                                            ✓ Đã xác minh
                                        </span>
                                    ` : ''}
                                </div>
                                ${fp.last_synced_at ? `
                                    <div>
                                        <span class="font-medium text-gray-700">Lần đồng bộ cuối:</span>
                                        <span class="text-gray-900">${new Date(fp.last_synced_at).toLocaleString('vi-VN')}</span>
                                    </div>
                                ` : ''}
                            </div>
                            
                            ${fp.about ? `
                                <div class="mt-4">
                                    <span class="font-medium text-gray-700">Giới thiệu:</span>
                                    <p class="text-gray-900 mt-1">${fp.about}</p>
                                </div>
                            ` : ''}
                            
                            ${fp.website ? `
                                <div class="mt-2">
                                    <span class="font-medium text-gray-700">Website:</span>
                                    <a href="${fp.website}" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2">${fp.website}</a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <!-- Cover photo nếu có -->
                    ${fp.cover_photo_url ? `
                        <div class="mb-6">
                            <img src="${fp.cover_photo_url}" alt="Cover photo" class="w-full h-32 object-cover rounded-lg"/>
                        </div>
                    ` : ''}
                    
                    <!-- Các chỉ số tổng quan của page -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Thống kê tổng quan</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">${numberFormat(fp.fan_count || 0)}</div>
                                <div class="text-sm text-gray-600">Fans</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">${numberFormat(fp.followers_count || 0)}</div>
                                <div class="text-sm text-gray-600">Followers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">${numberFormat(data.posts ? data.posts.length : 0)}</div>
                                <div class="text-sm text-gray-600">Posts</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.has_ads ? (post.total_impressions || 0) : (post.likes_count || 0)), 0) : 0)}</div>
                                <div class="text-sm text-gray-600">Reach</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.has_ads ? 0 : (post.reactions_count || 0)), 0) : 0)}</div>
                                <div class="text-sm text-gray-600">Reactions</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-indigo-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.has_ads ? (post.total_clicks || 0) : (post.engaged_users || 0)), 0) : 0)}</div>
                                <div class="text-sm text-gray-600">Engagements</div>
                            </div>
                        </div>
                        
                        <!-- Thông tin thời gian dữ liệu -->
                        <div class="bg-blue-50 rounded-lg p-4 mb-4">
                            <div class="text-center">
                                <div class="text-sm font-semibold text-blue-800 mb-2">Thời gian dữ liệu</div>
                                  <div class="text-xs text-blue-600">
                                      ${data.posts && data.posts.length > 0 ? 
                                         (() => {
                                             const dates = data.posts.map(p => new Date(p.created_time)).filter(d => !isNaN(d.getTime()));
                                             if (dates.length === 0) return 'Không có dữ liệu hợp lệ';
                                             
                                             const minDate = new Date(Math.min(...dates.map(d => d.getTime())));
                                             const maxDate = new Date(Math.max(...dates.map(d => d.getTime())));
                                             
                                             return `Từ ${formatDate(minDate.toISOString())} đến ${formatDate(maxDate.toISOString())}`;
                                         })()
                                          : 'Không có dữ liệu'
                                      }
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chỉ số bổ sung -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-800">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.has_ads ? (post.total_impressions || 0) : (post.impressions_unique || 0)), 0) : 0)}</div>
                                <div class="text-xs text-gray-600">Reach Unique</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-800">${numberFormat(data.posts ? data.posts.filter(post => !post.has_ads).reduce((sum, post) => sum + (post.impressions_organic || 0), 0) : 0)}</div>
                                <div class="text-xs text-gray-600">Reach Organic</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-800">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.has_ads ? (post.total_clicks || 0) : (post.clicks || 0)), 0) : 0)}</div>
                                <div class="text-xs text-gray-600">Clicks</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-800">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.has_ads ? (post.total_video_views || 0) : (post.video_views || 0)), 0) : 0)}</div>
                                <div class="text-xs text-gray-600">Video Views</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-800">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (post.total_messages || 0), 0) : 0)}</div>
                                <div class="text-xs text-gray-600">Tin nhắn</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phân tích Reactions -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Phân tích Reactions</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-blue-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (!post.has_ads ? (post.likes_count || 0) : 0), 0) : 0)}</div>
                                <div class="text-xs text-gray-600 flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.834a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                    </svg>
                                    Like
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-green-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (!post.has_ads ? (post.comments_count || 0) : 0), 0) : 0)}</div>
                                <div class="text-xs text-gray-600 flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Comment
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-purple-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (!post.has_ads ? (post.shares_count || 0) : 0), 0) : 0)}</div>
                                <div class="text-xs text-gray-600 flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"></path>
                                    </svg>
                                    Share
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-red-600">${numberFormat(data.posts ? data.posts.reduce((sum, post) => sum + (!post.has_ads ? (post.love_count || 0) : 0), 0) : 0)}</div>
                                <div class="text-xs text-gray-600 flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                    </svg>
                                    Love
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // View Mode Selector with Filters (moved below page info)
        html += `
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Chế độ xem:</span>
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button onclick="setViewMode('grid')" class="view-mode-btn px-3 py-1 text-sm font-medium rounded-md bg-blue-100 text-blue-700" data-mode="grid">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                                Lưới
                            </button>
                            <button onclick="setViewMode('list')" class="view-mode-btn px-3 py-1 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-200" data-mode="list">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Danh sách
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Loại bài viết:</span>
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button onclick="setPostFilter('all')" class="post-filter-btn px-3 py-1 text-sm font-medium rounded-md bg-blue-100 text-blue-700" data-filter="all">
                                Tổng hợp
                            </button>
                            <button onclick="setPostFilter('organic')" class="post-filter-btn px-3 py-1 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-200" data-filter="organic">
                                Posts
                            </button>
                            <button onclick="setPostFilter('ads')" class="post-filter-btn px-3 py-1 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-200" data-filter="ads">
                                Ads
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Sắp xếp:</span>
                        <select id="sort-select" onchange="applySorting()" class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="created_time_desc">Thời gian ↓</option>
                            <option value="created_time_asc">Thời gian ↑</option>
                            <option value="spend_desc">Chi phí ↓</option>
                            <option value="spend_asc">Chi phí ↑</option>
                            <option value="impressions_desc">Hiển thị ↓</option>
                            <option value="impressions_asc">Hiển thị ↑</option>
                            <option value="clicks_desc">Click ↓</option>
                            <option value="clicks_asc">Click ↑</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Tìm kiếm:</span>
                        <input type="text" id="search-input" placeholder="Tìm kiếm nội dung..." 
                               class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 w-48">
                    </div>
                    
                    
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Khoảng thời gian:</span>
                        <input type="date" id="date-from" class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-gray-500">→</span>
                        <input type="date" id="date-to" class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button onclick="applyFilters()" class="px-4 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Lọc
                    </button>
                    
                    <button onclick="resetFilters()" class="px-4 py-1 bg-gray-500 text-white text-sm rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Reset
                    </button>
                </div>
            </div>
        `;
        
        // Posts List
        if (data.posts && Array.isArray(data.posts) && data.posts.length > 0) {
            // Hiển thị thông báo thành công
            const adsCount = data.posts.filter(p => p.has_ads).length;
            const organicCount = data.posts.filter(p => !p.has_ads).length;
            
            html += `
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                Đã tải thành công ${data.posts.length} bài viết (${adsCount} có ads, ${organicCount} organic)
                            </p>
                        </div>
                    </div>
                </div>
            `;
            
            // Charts section at top of content (follows selected page)
            html += `
                <div id="dynamic-charts" class="bg-white rounded-xl shadow border border-gray-200 p-5 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Biểu đồ tổng hợp của Page</h3>
                          <div class="text-sm text-gray-600">
                              ${data.posts && data.posts.length > 0 ? 
                                 (() => {
                                     const dates = data.posts.map(p => new Date(p.created_time)).filter(d => !isNaN(d.getTime()));
                                     if (dates.length === 0) return 'Không có dữ liệu hợp lệ';
                                     
                                     const minDate = new Date(Math.min(...dates.map(d => d.getTime())));
                                     const maxDate = new Date(Math.max(...dates.map(d => d.getTime())));
                                     
                                     return `Từ ${formatDate(minDate.toISOString())} đến ${formatDate(maxDate.toISOString())}`;
                                 })()
                                  : 'Không có dữ liệu'
                              }
                        </div>
                    </div>
                    <div class="max-w-4xl mx-auto"><canvas id="overview-chart" class="w-full" style="height:160px" height="160"></canvas></div>
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

            // Store current posts for filtering
            currentPosts = data.posts;
            window.currentPosts = data.posts;
            
            // Store fanpage info globally
            window.currentFanpageInfo = data.fanpage_info;

            // Client-side pagination
            const pageSize = 10;
            const totalPosts = currentPosts.length;
            const totalPages = Math.max(1, Math.ceil(totalPosts / pageSize));
            let current = 1;

            function renderPostsSlice(page) {
                const start = (page - 1) * pageSize;
                const slice = currentPosts.slice(start, start + pageSize);
                let postsHtml = '';
                slice.forEach(post => {
                    const postType = post.type || 'post';
                    const postSource = post.post_source || 'unknown';
                    const hasAds = post.has_ads || false;
                    const createdTime = post.created_time ? new Date(post.created_time).toLocaleString('vi-VN') : 'N/A';
                    const message = post.message || 'Không có nội dung';
                    
                    // Xử lý attachments cho cả ads và organic posts
                    let attachmentsHtml = '';
                    
                    // Xử lý attachments từ JSON string hoặc object (organic posts)
                    if (post.attachments) {
                        console.log('Processing attachments for post:', post.id, 'Type:', typeof post.attachments, 'Data:', post.attachments);
                        try {
                            let attachments;
                            if (typeof post.attachments === 'string') {
                                attachments = JSON.parse(post.attachments);
                            } else {
                                attachments = post.attachments;
                            }
                            
                            if (attachments && attachments.data && Array.isArray(attachments.data)) {
                                attachments.data.forEach((attachment, index) => {
                                    if (index < 3) {
                                        if (attachment.media_type === 'photo' && attachment.media?.image?.src) {
                                            attachmentsHtml += `
                                                <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                    <div class="flex justify-center">
                                                        <img src="${attachment.media.image.src}" 
                                                             class="attachment-image max-w-full object-contain rounded-lg"
                                                             onload="this.style.display='block'"
                                                             onerror="this.style.display='none'"/>
                                                    </div>
                                                    ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                                </div>
                                            `;
                                        } else if (attachment.media_type === 'video' && attachment.media?.source) {
                                            attachmentsHtml += `
                                                <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                    <div class="flex justify-center">
                                                        <video controls 
                                                               class="attachment-video max-w-full object-contain rounded-lg"
                                                               src="${attachment.media.source}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    </div>
                                                    ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                                </div>
                                            `;
                                        } else if (attachment.media_type === 'album' && attachment.media?.image?.src) {
                                            // Xử lý album (nhiều ảnh)
                                            attachmentsHtml += `
                                                <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                    <div class="flex justify-center">
                                                        <img src="${attachment.media.image.src}" 
                                                             class="attachment-image max-w-full object-contain rounded-lg"
                                                             onload="this.style.display='block'"
                                                             onerror="this.style.display='none'"/>
                                                    </div>
                                                    ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                                </div>
                                            `;
                                        } else if (attachment.media_type === 'link' && attachment.media?.image?.src) {
                                            // Xử lý link với ảnh
                                            attachmentsHtml += `
                                                <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                    <div class="flex justify-center">
                                                        <img src="${attachment.media.image.src}" 
                                                             class="attachment-image max-w-full object-contain rounded-lg"
                                                             onload="this.style.display='block'"
                                                             onerror="this.style.display='none'"/>
                                                    </div>
                                                    ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                                </div>
                                            `;
                                        }
                                    }
                                });
                            }
                        } catch (e) {
                            console.warn('Error parsing attachments JSON:', e);
                        }
                    }
                    
                    // Xử lý attachment_image cho ads posts (từ FacebookPostAd)
                    if (!attachmentsHtml && post.attachment_image) {
                        try {
                            const imageUrl = post.attachment_image;
                            if (imageUrl) {
                                attachmentsHtml += `
                                        <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                            <div class="flex justify-center">
                                                <img src="${imageUrl}" 
                                                     class="attachment-image max-w-full object-contain rounded-lg"
                                                     onload="this.style.display='block'"
                                                     onerror="this.style.display='none'"/>
                                            </div>
                                        </div>
                                    `;
                            }
                        } catch (e) {
                            console.warn('Error parsing attachment_image:', e);
                        }
                    }
                    
                    // Xử lý attachment_source cho ads posts (từ FacebookPostAd)
                    if (!attachmentsHtml && post.attachment_source) {
                        try {
                            const videoUrl = post.attachment_source;
                            if (videoUrl) {
                                attachmentsHtml += `
                                    <div class="relative mb-3">
                                        <video controls class="w-full rounded-lg border max-h-64 object-cover" src="${videoUrl}"></video>
                                    </div>
                                `;
                            }
                        } catch (e) {
                            console.warn('Error parsing attachment_source:', e);
                        }
                    }
                    
                    // Xử lý attachments_image (array of URLs) cho organic posts
                    if (!attachmentsHtml && post.attachments_image) {
                        console.log('Processing attachments_image for post:', post.id, 'Data:', post.attachments_image);
                        try {
                            const images = Array.isArray(post.attachments_image) ? post.attachments_image : JSON.parse(post.attachments_image);
                            if (Array.isArray(images)) {
                                images.slice(0, 3).forEach(imageUrl => {
                                    if (imageUrl) {
                                        attachmentsHtml += `
                                            <div class="relative mb-3">
                                                <img src="${imageUrl}" class="w-full rounded-lg border max-h-64 object-cover"/>
                                            </div>
                                        `;
                                    }
                                });
                            }
                        } catch (e) {
                            console.warn('Error parsing attachments_image:', e);
                        }
                    }
                    
                    // Xử lý attachments_source (array of video URLs) cho organic posts
                    if (!attachmentsHtml && post.attachments_source) {
                        try {
                            const videos = Array.isArray(post.attachments_source) ? post.attachments_source : JSON.parse(post.attachments_source);
                            if (Array.isArray(videos)) {
                                videos.slice(0, 2).forEach(videoUrl => {
                                    if (videoUrl) {
                                        attachmentsHtml += `
                                            <div class="relative mb-3">
                                                <video controls class="w-full rounded-lg border max-h-64 object-cover" src="${videoUrl}"></video>
                                            </div>
                                        `;
                                    }
                                });
                            }
                        } catch (e) {
                            console.warn('Error parsing attachments_source:', e);
                        }
                    }
                    
                    // Fallback cho ảnh từ các trường khác
                    if (!attachmentsHtml && (post.full_picture || post.picture)) {
                        const imageUrl = post.full_picture || post.picture;
                        console.log('Using fallback image for post:', post.id, 'URL:', imageUrl);
                        attachmentsHtml = `
                            <div class="mb-3 bg-gray-100 rounded-lg p-2">
                                <div class="flex justify-center">
                                    <img src="${imageUrl}" 
                                         class="attachment-image max-w-full object-contain rounded-lg"
                                         onload="this.style.display='block'"
                                         onerror="this.style.display='none'"/>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Fallback cho video từ các trường khác
                    if (!attachmentsHtml && post.video_url) {
                        attachmentsHtml = `
                            <div class="mb-3">
                                <video controls class="w-full rounded-lg border max-h-64 object-cover" src="${post.video_url}"></video>
                            </div>
                        `;
                    }
                    
                    postsHtml += `
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-2 overflow-hidden">
                        <div class="p-2 flex items-start gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                                ${data.fanpage_info && data.fanpage_info.profile_picture_url ? 
                                    `<img src="${data.fanpage_info.profile_picture_url}" class="w-6 h-6 object-cover"/>` : ''
                                }
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1 text-xs text-gray-600 mb-1 flex-wrap">
                                    <span class="font-semibold text-gray-900 truncate">${data.fanpage_info ? data.fanpage_info.name : 'Unknown Page'}</span>
                                    <span>·</span>
                                    <span class="whitespace-nowrap">${createdTime}</span>
                                    <span class="px-1 py-0.5 text-xs bg-blue-100 text-blue-700 rounded whitespace-nowrap">${postType.toUpperCase()}</span>
                                    ${hasAds ? 
                                        '<span class="px-1 py-0.5 text-xs bg-green-100 text-green-700 rounded whitespace-nowrap">💰 ADS</span>' : 
                                        '<span class="px-1 py-0.5 text-xs bg-gray-100 text-gray-700 rounded whitespace-nowrap">📝 ORGANIC</span>'
                                    }
                                </div>
                                
                                <!-- Nội dung bài viết -->
                                <div class="text-gray-900 mb-1 text-xs break-words">
                                    ${message.replace(/\n/g, '<br>')}
                                </div>

                                <div class="attachment-container">
                                    ${attachmentsHtml}
                                </div>
                                
                                <!-- Chỉ số compact -->
                                <div class="flex items-center gap-3 text-xs text-gray-600 mt-1">
                                    ${hasAds ? `
                                        <span>💰 ${numberFormat(post.total_spend || 0)}</span>
                                        <span>👁️ ${numberFormat(post.total_impressions || 0)}</span>
                                        <span>👆 ${numberFormat(post.total_clicks || 0)}</span>
                                        <span>📊 ${((post.avg_ctr || 0) * 100).toFixed(2)}%</span>
                                    ` : `
                                        <span>👍 ${numberFormat(post.likes_count || 0)}</span>
                                        <span>💬 ${numberFormat(post.comments_count || 0)}</span>
                                        <span>↗️ ${numberFormat(post.shares_count || 0)}</span>
                                        <span>❤️ ${numberFormat(post.love_count || 0)}</span>
                                        <span>👁️ ${numberFormat(post.impressions || 0)}</span>
                                        <span>👆 ${numberFormat(post.clicks || 0)}</span>
                                    `}
                                    ${post.total_messages > 0 ? `<span>💬 ${post.total_messages}</span>` : ''}
                                </div>
                                
                                <div class="flex items-center justify-between text-xs mt-1">
                                    <div class="flex items-center gap-2">
                                        ${hasAds ? `
                                            <div class="flex gap-2">
                                                <button onclick="showAdDetails('${post.id}', '${post.page_id}')"
                                                        class="px-2 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                                    📊 Chi tiết
                                                </button>
                                                <a href="/facebook/data-management/post/${post.id}/page/${post.page_id}" 
                                                   class="px-2 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                                    Xem chi tiết →
                                                </a>
                                                ${post.permalink_url ? `
                                                    <a href="${post.permalink_url}" target="_blank"
                                                       class="px-2 py-1 text-xs border border-blue-300 text-blue-700 rounded hover:bg-blue-50">
                                                        📘 Facebook
                                                    </a>
                                                ` : ''}
                                            </div>
                                        ` : `
                                            <div class="flex gap-2">
                                                <button onclick="showOrganicPostDetails('${post.id}', '${post.page_id}')"
                                                        class="px-2 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                                    📊 Chi tiết
                                                </button>
                                                <a href="https://facebook.com/${post.id}" target="_blank"
                                                   class="px-2 py-1 text-xs border border-blue-300 text-blue-700 rounded hover:bg-blue-50">
                                                    📘 Facebook
                                                </a>
                                            </div>
                                        `}
                                    </div>
                                    <div class="text-gray-500">
                                        ID: ${post.id}
                                    </div>
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
    
    // Tabs switch
    if (viewTabButtons && viewTabButtons.length) {
        viewTabButtons.forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                if (viewTypeInput) viewTypeInput.value = v;
                const pageId = pageSelect ? pageSelect.value : null;
                if (pageId) loadPageData(pageId);
            });
        });
    }

    // View mode functions - Make them global
    window.setViewMode = function(mode) {
        // Update button states
        const buttons = document.querySelectorAll('.view-mode-btn');
        buttons.forEach(btn => {
            if (btn.getAttribute('data-mode') === mode) {
                btn.classList.add('bg-blue-100', 'text-blue-700');
                btn.classList.remove('text-gray-700', 'hover:bg-gray-200');
            } else {
                btn.classList.remove('bg-blue-100', 'text-blue-700');
                btn.classList.add('text-gray-700', 'hover:bg-gray-200');
            }
        });
        
        // Store view mode preference
        localStorage.setItem('viewMode', mode);
        
        // Apply view mode to posts container
        const postsContainer = document.getElementById('posts-paginated');
        if (postsContainer) {
            // Remove existing view classes
            postsContainer.classList.remove('grid-view', 'list-view');
            
            if (mode === 'grid') {
                postsContainer.classList.remove('space-y-4');
                postsContainer.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-4', 'grid-view');
            } else {
                postsContainer.classList.remove('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-4');
                postsContainer.classList.add('space-y-4', 'list-view');
            }
        }
    }

    // Load saved view mode - default to list view
    const savedViewMode = localStorage.getItem('viewMode') || 'list';
    setTimeout(() => setViewMode(savedViewMode), 100);

    // Add event listeners for search input
    setTimeout(() => {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(applyFilters, 300));
        }
    }, 100);

    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Global variables for filtering and sorting
    let currentPosts = [];
    let currentPostFilter = 'all';
    let currentSort = 'created_time_desc';
    let currentBreakdown = 'content';

    // Post filter functions - Make them global
    window.setPostFilter = function(filter) {
        currentPostFilter = filter;
        
        // Update button states
        const buttons = document.querySelectorAll('.post-filter-btn');
        buttons.forEach(btn => {
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('bg-blue-100', 'text-blue-700');
                btn.classList.remove('text-gray-700', 'hover:bg-gray-200');
            } else {
                btn.classList.remove('bg-blue-100', 'text-blue-700');
                btn.classList.add('text-gray-700', 'hover:bg-gray-200');
            }
        });
        
        applyFilters();
    }

    // Sorting function - Make it global
    window.applySorting = function() {
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            currentSort = sortSelect.value;
            applyFilters();
        }
    }

    // Main filter function - Make it global
    window.applyFilters = function() {
        if (!currentPosts || currentPosts.length === 0) return;
        
        let filteredPosts = [...currentPosts];
        
        // Filter by post type
        if (currentPostFilter === 'ads') {
            filteredPosts = filteredPosts.filter(post => post.has_ads);
        } else if (currentPostFilter === 'organic') {
            filteredPosts = filteredPosts.filter(post => !post.has_ads);
        }
        
        // Filter by search
        const searchInput = document.getElementById('search-input');
        if (searchInput && searchInput.value.trim()) {
            const searchTerm = searchInput.value.toLowerCase();
            filteredPosts = filteredPosts.filter(post => 
                (post.message && post.message.toLowerCase().includes(searchTerm)) ||
                (post.id && post.id.toString().includes(searchTerm))
            );
        }
        
        // Filter by content type
        const contentTypeFilter = document.getElementById('content-type-filter');
        if (contentTypeFilter && contentTypeFilter.value) {
            const contentType = contentTypeFilter.value;
            filteredPosts = filteredPosts.filter(post => {
                if (contentType === 'photo') {
                    return post.attachments_image || post.picture || 
                           (post.attachments && post.attachments.includes('photo'));
                } else if (contentType === 'video') {
                    return post.attachments_source || post.video_url || 
                           (post.attachments && post.attachments.includes('video'));
                } else if (contentType === 'link') {
                    return post.link || (post.attachments && post.attachments.includes('link'));
                } else if (contentType === 'status') {
                    return post.message && !post.attachments_image && !post.attachments_source && !post.picture;
                }
                return true;
            });
        }
        
        // Filter by date range
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        if (dateFrom && dateFrom.value) {
            const fromDate = new Date(dateFrom.value);
            filteredPosts = filteredPosts.filter(post => {
                if (!post.created_time) return false;
                return new Date(post.created_time) >= fromDate;
            });
        }
        if (dateTo && dateTo.value) {
            const toDate = new Date(dateTo.value);
            toDate.setHours(23, 59, 59, 999); // End of day
            filteredPosts = filteredPosts.filter(post => {
                if (!post.created_time) return false;
                return new Date(post.created_time) <= toDate;
            });
        }
        
        // Sort posts
        filteredPosts.sort((a, b) => {
            switch (currentSort) {
                case 'created_time_desc':
                    return new Date(b.created_time || 0) - new Date(a.created_time || 0);
                case 'created_time_asc':
                    return new Date(a.created_time || 0) - new Date(b.created_time || 0);
                case 'spend_desc':
                    return (b.total_spend || 0) - (a.total_spend || 0);
                case 'spend_asc':
                    return (a.total_spend || 0) - (b.total_spend || 0);
                case 'impressions_desc':
                    return (b.total_impressions || 0) - (a.total_impressions || 0);
                case 'impressions_asc':
                    return (a.total_impressions || 0) - (b.total_impressions || 0);
                case 'clicks_desc':
                    return (b.total_clicks || 0) - (a.total_clicks || 0);
                case 'clicks_asc':
                    return (a.total_clicks || 0) - (b.total_clicks || 0);
                default:
                    return 0;
            }
        });
        
        // Update posts display
        updatePostsDisplay(filteredPosts);
    }

    // Breakdown function - Make it global
    window.setBreakdown = function(breakdown) {
        currentBreakdown = breakdown;
        
        // Update button states
        const buttons = document.querySelectorAll('.breakdown-btn');
        buttons.forEach(btn => {
            if (btn.getAttribute('data-breakdown') === breakdown) {
                btn.classList.add('bg-blue-100', 'text-blue-700');
                btn.classList.remove('text-gray-700', 'hover:bg-gray-200');
            } else {
                btn.classList.remove('bg-blue-100', 'text-blue-700');
                btn.classList.add('text-gray-700', 'hover:bg-gray-200');
            }
        });
        
        applyFilters();
    }

    // Reset filters function - Make it global
    window.resetFilters = function() {
        currentPostFilter = 'all';
        currentSort = 'created_time_desc';
        currentBreakdown = 'content';
        
        // Reset UI elements
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';
        
        const dateFrom = document.getElementById('date-from');
        if (dateFrom) dateFrom.value = '';
        
        const dateTo = document.getElementById('date-to');
        if (dateTo) dateTo.value = '';
        
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) sortSelect.value = 'created_time_desc';
        
        // Reset button states
        setPostFilter('all');
        setBreakdown('content');
        
        // Apply filters
        applyFilters();
    }

    // Update posts display function
    function updatePostsDisplay(posts) {
        const postsContainer = document.getElementById('posts-paginated');
        if (!postsContainer) return;
        
        // Update pagination
        const pageSize = 10;
        const totalPages = Math.max(1, Math.ceil(posts.length / pageSize));
        let current = 1;
        
        function renderPostsSlice(page, fanpageInfo = null) {
            const start = (page - 1) * pageSize;
            const slice = posts.slice(start, start + pageSize);
            let postsHtml = '';
            
            slice.forEach(post => {
                const postType = post.type || 'post';
                const postSource = post.post_source || 'unknown';
                const hasAds = post.has_ads || false;
                const createdTime = post.created_time ? new Date(post.created_time).toLocaleString('vi-VN') : 'N/A';
                const message = post.message || 'Không có nội dung';
                
                // Xử lý attachments cho cả ads và organic posts
                let attachmentsHtml = '';
                
                // Xử lý attachments từ JSON string hoặc object (organic posts) - theo logic pages.blade.php
                if (post.attachments) {
                    try {
                        let attachments;
                        if (typeof post.attachments === 'string') {
                            attachments = JSON.parse(post.attachments);
                        } else {
                            attachments = post.attachments;
                        }
                        
                        if (attachments && attachments.data && Array.isArray(attachments.data)) {
                            attachments.data.forEach((attachment, index) => {
                                if (index < 3) {
                                    if (attachment.media_type === 'photo' && attachment.media?.image?.src) {
                                        attachmentsHtml += `
                                            <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                <div class="flex justify-center">
                                                    <img src="${attachment.media.image.src}" 
                                                         class="attachment-image max-w-full object-contain rounded-lg"
                                                         onload="this.style.display='block'"
                                                         onerror="this.style.display='none'"/>
                                                </div>
                                                ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                            </div>
                                        `;
                                    } else if (attachment.media_type === 'video' && attachment.media?.source) {
                                        attachmentsHtml += `
                                            <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                <div class="flex justify-center">
                                                    <video controls 
                                                           class="attachment-video max-w-full object-contain rounded-lg"
                                                           src="${attachment.media.source}">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                </div>
                                                ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                            </div>
                                        `;
                                    } else if (attachment.media_type === 'album' && attachment.media?.image?.src) {
                                        // Xử lý album (nhiều ảnh)
                                        attachmentsHtml += `
                                            <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                <div class="flex justify-center">
                                                    <img src="${attachment.media.image.src}" 
                                                         class="attachment-image max-w-full object-contain rounded-lg"
                                                         onload="this.style.display='block'"
                                                         onerror="this.style.display='none'"/>
                                                </div>
                                                ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                            </div>
                                        `;
                                    } else if (attachment.media_type === 'link' && attachment.media?.image?.src) {
                                        // Xử lý link với ảnh
                                        attachmentsHtml += `
                                            <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                                <div class="flex justify-center">
                                                    <img src="${attachment.media.image.src}" 
                                                         class="attachment-image max-w-full object-contain rounded-lg"
                                                         onload="this.style.display='block'"
                                                         onerror="this.style.display='none'"/>
                                                </div>
                                                ${attachment.title ? `<div class="mt-1 text-xs text-gray-600 text-center break-words">${attachment.title}</div>` : ''}
                                            </div>
                                        `;
                                    }
                                }
                            });
                        }
                    } catch (e) {
                        console.warn('Error parsing attachments JSON:', e);
                    }
                }
                
                // Xử lý attachment_image cho ads posts (từ FacebookPostAd)
                if (!attachmentsHtml && post.attachment_image) {
                    try {
                        const imageUrl = post.attachment_image;
                        if (imageUrl) {
                            attachmentsHtml += `
                                <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                    <div class="flex justify-center">
                                        <img src="${imageUrl}" 
                                             class="attachment-image max-w-full object-contain rounded-lg"
                                             onload="this.style.display='block'"
                                             onerror="this.style.display='none'"/>
                                    </div>
                                </div>
                            `;
                        }
                    } catch (e) {
                        console.warn('Error parsing attachment_image:', e);
                    }
                }
                
                // Xử lý attachment_source cho ads posts (từ FacebookPostAd)
                if (!attachmentsHtml && post.attachment_source) {
                    try {
                        const videoUrl = post.attachment_source;
                        if (videoUrl) {
                            attachmentsHtml += `
                                <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                    <div class="flex justify-center">
                                        <video controls 
                                               class="attachment-video max-w-full object-contain rounded-lg"
                                               src="${videoUrl}">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                </div>
                            `;
                        }
                    } catch (e) {
                        console.warn('Error parsing attachment_source:', e);
                    }
                }
                
                // Xử lý attachments_image (array of URLs) cho organic posts
                if (!attachmentsHtml && post.attachments_image) {
                    try {
                        const images = Array.isArray(post.attachments_image) ? post.attachments_image : JSON.parse(post.attachments_image);
                        if (Array.isArray(images)) {
                            images.slice(0, 3).forEach(imageUrl => {
                                if (imageUrl) {
                                    attachmentsHtml += `
                                        <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                            <div class="flex justify-center">
                                                <img src="${imageUrl}" 
                                                     class="attachment-image max-w-full object-contain rounded-lg"
                                                     onload="this.style.display='block'"
                                                     onerror="this.style.display='none'"/>
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                        }
                    } catch (e) {
                        console.warn('Error parsing attachments_image:', e);
                    }
                }
                
                // Xử lý attachments_source (array of video URLs) cho organic posts
                if (!attachmentsHtml && post.attachments_source) {
                    try {
                        const videos = Array.isArray(post.attachments_source) ? post.attachments_source : JSON.parse(post.attachments_source);
                        if (Array.isArray(videos)) {
                            videos.slice(0, 2).forEach(videoUrl => {
                                if (videoUrl) {
                                    attachmentsHtml += `
                                        <div class="relative mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                                            <div class="flex justify-center">
                                                <video controls 
                                                       class="attachment-video max-w-full object-contain rounded-lg"
                                                       src="${videoUrl}">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                        }
                    } catch (e) {
                        console.warn('Error parsing attachments_source:', e);
                    }
                }
                
                // Fallback cho ảnh từ các trường khác
                if (!attachmentsHtml && (post.full_picture || post.picture)) {
                    const imageUrl = post.full_picture || post.picture;
                    attachmentsHtml = `
                        <div class="mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                            <div class="flex justify-center">
                                <img src="${imageUrl}" 
                                     class="attachment-image max-w-full object-contain rounded-lg"
                                     onload="this.style.display='block'"
                                     onerror="this.style.display='none'"/>
                            </div>
                        </div>
                    `;
                }
                
                // Fallback cho video từ các trường khác
                if (!attachmentsHtml && post.video_url) {
                    attachmentsHtml = `
                        <div class="mb-2 bg-gray-100 rounded-lg p-2 max-w-full overflow-hidden">
                            <div class="flex justify-center">
                                <video controls 
                                       class="attachment-video max-w-full object-contain rounded-lg"
                                       src="${post.video_url}">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    `;
                }
                
                postsHtml += `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-2 overflow-hidden">
                    <div class="p-2 flex items-start gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                            ${fanpageInfo && fanpageInfo.profile_picture_url ? 
                                `<img src="${fanpageInfo.profile_picture_url}" class="w-6 h-6 object-cover"/>` : ''
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1 text-xs text-gray-600 mb-1 flex-wrap">
                                <span class="font-semibold text-gray-900 truncate">${fanpageInfo ? fanpageInfo.name : 'Unknown Page'}</span>
                                <span>·</span>
                                <span class="whitespace-nowrap">${createdTime}</span>
                                <span class="px-1 py-0.5 text-xs bg-blue-100 text-blue-700 rounded whitespace-nowrap">${postType.toUpperCase()}</span>
                                ${hasAds ? 
                                    '<span class="px-1 py-0.5 text-xs bg-green-100 text-green-700 rounded whitespace-nowrap">💰 ADS</span>' : 
                                    '<span class="px-1 py-0.5 text-xs bg-gray-100 text-gray-700 rounded whitespace-nowrap">📝 ORGANIC</span>'
                                }
                            </div>
                            
                            <!-- Nội dung bài viết -->
                            <div class="text-gray-900 mb-1 text-xs break-words">
                                ${message.replace(/\n/g, '<br>')}
                            </div>

                            <div class="attachment-container">
                                ${attachmentsHtml}
                            </div>
                            
                            <!-- Chỉ số compact -->
                            <div class="flex items-center gap-3 text-xs text-gray-600 mt-1">
                                ${hasAds ? `
                                    <span>💰 ${numberFormat(post.total_spend || 0)}</span>
                                    <span>👁️ ${numberFormat(post.total_impressions || 0)}</span>
                                    <span>👆 ${numberFormat(post.total_clicks || 0)}</span>
                                    <span>📊 ${numberFormat(post.avg_ctr || 0)}%</span>
                                ` : `
                                    <span>👍 ${numberFormat(post.likes_count || 0)}</span>
                                    <span>💬 ${numberFormat(post.comments_count || 0)}</span>
                                    <span>↗️ ${numberFormat(post.shares_count || 0)}</span>
                                    <span>❤️ ${numberFormat(post.love_count || 0)}</span>
                                    <span>👁️ ${numberFormat(post.impressions || 0)}</span>
                                    <span>👆 ${numberFormat(post.clicks || 0)}</span>
                                `}
                                ${post.total_messages > 0 ? `<span>💬 ${post.total_messages}</span>` : ''}
                            </div>
                            
                            <div class="flex items-center justify-between text-xs mt-1">
                                <div class="flex items-center gap-2">
                                        ${hasAds ? `
                                            <div class="flex gap-2">
                                                <button onclick="showAdDetails('${post.id}', '${post.page_id}')"
                                                        class="px-2 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                                    📊 Chi tiết
                                                </button>
                                                <a href="/facebook/data-management/post/${post.id}/page/${post.page_id}" 
                                                   class="px-2 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                                    Xem chi tiết →
                                                </a>
                                                ${post.permalink_url ? `
                                                    <a href="${post.permalink_url}" target="_blank"
                                                       class="px-2 py-1 text-xs border border-blue-300 text-blue-700 rounded hover:bg-blue-50">
                                                        📘 Facebook
                                                    </a>
                                                ` : ''}
                                            </div>
                                        ` : `
                                            <div class="flex gap-2">
                                                <button onclick="showOrganicPostDetails('${post.id}', '${post.page_id}')"
                                                        class="px-2 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                                    📊 Chi tiết
                                                </button>
                                                <a href="https://facebook.com/${post.id}" target="_blank"
                                                   class="px-2 py-1 text-xs border border-blue-300 text-blue-700 rounded hover:bg-blue-50">
                                                    📘 Facebook
                                                </a>
                                            </div>
                                        `}
                                </div>
                                <div class="text-gray-500">
                                    ID: ${post.id}
                                </div>
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
            if (container) container.innerHTML = renderPostsSlice(current, window.currentFanpageInfo);
            updatePager();
        }
        
        renderCurrent();

        const prevBtn = document.getElementById('btn-prev');
        const nextBtn = document.getElementById('btn-next');
        if (prevBtn) prevBtn.addEventListener('click', function(){ if (current > 1) { current--; renderCurrent(); }});
        if (nextBtn) nextBtn.addEventListener('click', function(){ if (current < totalPages) { current++; renderCurrent(); }});
    }

    // Date preset quick apply
    if (datePreset) {
        datePreset.addEventListener('change', function(){
            const val = this.value;
            const fromInput = document.getElementById('date_from');
            const toInput = document.getElementById('date_to');
            if (!fromInput || !toInput) return;
            const today = new Date();
            const fmt = d => d.toISOString().slice(0,10);
            let since = '', until = '';
            const getMonday = d => { const day = d.getDay(); const diff = (day === 0 ? -6 : 1) - day; const t = new Date(d); t.setDate(d.getDate()+diff); t.setHours(0,0,0,0); return t; };
            if (val === 'today') { since = fmt(today); until = fmt(today); }
            else if (val === 'yesterday') { const y = new Date(today.getTime()-86400000); since = fmt(y); until = fmt(y); }
            else if (val === 'this_week') { const m = getMonday(today); since = fmt(m); until = fmt(today); }
            else if (val === 'last_week') { const m = getMonday(new Date(today.getTime()-7*86400000)); const e = new Date(m.getTime()+6*86400000); since = fmt(m); until = fmt(e); }
            else if (val === 'last_7_days') { const s = new Date(today.getTime()-6*86400000); since = fmt(s); until = fmt(today); }
            else if (val === 'last_28_days') { const s = new Date(today.getTime()-27*86400000); since = fmt(s); until = fmt(today); }
            else if (val === 'last_30_days') { const s = new Date(today.getTime()-29*86400000); since = fmt(s); until = fmt(today); }
            else if (val === 'this_month') { const s = new Date(today.getFullYear(), today.getMonth(), 1); since = fmt(s); until = fmt(today); }
            else if (val === 'last_month') { const s = new Date(today.getFullYear(), today.getMonth()-1, 1); const e = new Date(today.getFullYear(), today.getMonth(), 0); since = fmt(s); until = fmt(e); }
            else if (val === 'this_quarter') { const q = Math.floor(today.getMonth()/3); const s = new Date(today.getFullYear(), q*3, 1); since = fmt(s); until = fmt(today); }
            else if (val === 'last_quarter') { const q = Math.floor(today.getMonth()/3)-1; const year = today.getFullYear() + (q<0?-1:0); const startMonth = ((q+4)%4)*3; const s = new Date(year, startMonth, 1); const e = new Date(year, startMonth+3, 0); since = fmt(s); until = fmt(e); }
            else if (val === 'lifetime') { since = ''; until = ''; }
            if (since && until) { fromInput.value = since; toInput.value = until; }
            if (!since && !until) { fromInput.value = ''; toInput.value = ''; }
        });
    }

    // Quick preset on header mirrors main preset and submits
    (function(){
        if (!quickApply) return;
        const fmt = d => d.toISOString().slice(0,10);
        const getMonday = d => { const day = d.getDay(); const diff = (day === 0 ? -6 : 1) - day; const t = new Date(d); t.setDate(d.getDate()+diff); t.setHours(0,0,0,0); return t; };
        function applyPreset(val){
            const today = new Date();
            let since='',until='';
            if (val==='today'){ since=fmt(today); until=fmt(today);} 
            else if (val==='yesterday'){ const y=new Date(today.getTime()-86400000); since=fmt(y); until=fmt(y);} 
            else if (val==='this_week'){ const m=getMonday(today); since=fmt(m); until=fmt(today);} 
            else if (val==='last_week'){ const m=getMonday(new Date(today.getTime()-7*86400000)); const e=new Date(m.getTime()+6*86400000); since=fmt(m); until=fmt(e);} 
            else if (val==='last_7_days'){ const s=new Date(today.getTime()-6*86400000); since=fmt(s); until=fmt(today);} 
            else if (val==='last_28_days'){ const s=new Date(today.getTime()-27*86400000); since=fmt(s); until=fmt(today);} 
            else if (val==='last_30_days'){ const s=new Date(today.getTime()-29*86400000); since=fmt(s); until=fmt(today);} 
            else if (val==='this_month'){ const s=new Date(today.getFullYear(),today.getMonth(),1); since=fmt(s); until=fmt(today);} 
            else if (val==='last_month'){ const s=new Date(today.getFullYear(),today.getMonth()-1,1); const e=new Date(today.getFullYear(),today.getMonth(),0); since=fmt(s); until=fmt(e);} 
            if (since && until){ quickFrom.value=since; quickTo.value=until; }
        }
        quickPreset?.addEventListener('change', ()=>applyPreset(quickPreset.value));
        quickApply.addEventListener('click', function(){
            const filters = {};
            if (quickFrom.value) filters['date_from']=quickFrom.value;
            if (quickTo.value) filters['date_to']=quickTo.value;
            // Collect breakdown filters
            const sortSel = document.getElementById('sort-metric');
            if (sortSel && sortSel.value) filters['sort_metric'] = sortSel.value;
            const ch = document.querySelector('input.bd-channel:checked');
            if (ch) filters['channel'] = ch.value;
            const gender = document.getElementById('bd-gender'); if (gender && gender.value) filters['gender'] = gender.value;
            const age = document.getElementById('bd-age'); if (age && age.value) filters['age'] = age.value;
            const region = document.getElementById('bd-region'); if (region && region.value) filters['region'] = region.value.trim();
            const device = document.getElementById('bd-device'); if (device && device.value) filters['device'] = device.value;
            const contentChecked = Array.from(document.querySelectorAll('input.bd-content:checked')).map(i=>i.value);
            if (contentChecked.length) filters['content_types'] = contentChecked.join(',');
            let pageId = pageSelect ? pageSelect.value : null;
            if (!pageId && pageSelect) {
                const firstOpt = Array.from(pageSelect.options).find(o=>o.value);
                if (firstOpt) { pageSelect.value = firstOpt.value; pageId = firstOpt.value; }
            }
            if (pageId){ 
                loadPageData(pageId, filters);
            } else {
                alert('Vui lòng chọn Trang Facebook trước khi lọc.');
            }
        });

        // Auto-apply when date range changed and page selected
        [quickFrom, quickTo].forEach(el=>{
            el?.addEventListener('change', ()=>{
                const pageId = pageSelect?.value;
                if (pageId && quickFrom.value && quickTo.value) {
                    const filters = { date_from: quickFrom.value, date_to: quickTo.value };
                    loadPageData(pageId, filters);
                }
            });
        });
    })();

    // Page search/sort client-side
    if (pageSearch || pageSort) {
        const rebuildOptions = () => {
            const select = pageSelect; if (!select) return;
            const options = Array.from(select.querySelectorAll('option')).filter(o=>o.value);
            let filtered = options;
            const kw = (pageSearch?.value || '').trim().toLowerCase();
            if (kw) {
                filtered = filtered.filter(o => o.textContent.toLowerCase().includes(kw) || o.value.includes(kw));
            }
            const sortVal = pageSort?.value || 'name_asc';
            filtered.sort((a,b)=>{
                const get = (o,k)=>o.dataset[k]? (isNaN(o.dataset[k])?o.dataset[k]:Number(o.dataset[k])):'';
                if (sortVal.startsWith('name')) { const an=get(a,'name'), bn=get(b,'name'); return sortVal.endsWith('asc')? (an>bn?1:-1):(an<bn?1:-1); }
                if (sortVal.startsWith('created')) { const an=Number(get(a,'created')), bn=Number(get(b,'created')); return sortVal.endsWith('asc')? (an-bn):(bn-an); }
                if (sortVal.startsWith('ads')) { const an=Number(get(a,'ads')), bn=Number(get(b,'ads')); return sortVal.endsWith('asc')? (an-bn):(bn-an); }
                return 0;
            });
            // Reattach without losing the first placeholder option
            const first = select.querySelector('option[value=""]');
            select.innerHTML = '';
            if (first) select.appendChild(first);
            filtered.forEach(o=>select.appendChild(o));
        };
        pageSearch?.addEventListener('input', rebuildOptions);
        pageSort?.addEventListener('change', rebuildOptions);
    }

    // Auto-load data when page changes
    if (pageSelect) {
        pageSelect.addEventListener('change', function() {
            if (this.value) {
                hideNoPageMessage();
                loadPageData(this.value);
            } else {
                showNoPageMessage();
                resetPageView();
            }
        });
        
        // Auto-load data if page is already selected
        if (pageSelect.value) {
            hideNoPageMessage();
            loadPageData(pageSelect.value);
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
        // Update date range
        const chartsDateRange = document.getElementById('charts-date-range');
        if (chartsDateRange && data.posts && data.posts.length > 0) {
            const dates = data.posts.map(p => new Date(p.created_time)).filter(d => !isNaN(d.getTime()));
            if (dates.length > 0) {
                const minDate = new Date(Math.min(...dates.map(d => d.getTime())));
                const maxDate = new Date(Math.max(...dates.map(d => d.getTime())));
                chartsDateRange.textContent = `Từ ${formatDate(minDate.toISOString())} đến ${formatDate(maxDate.toISOString())}`;
            }
        }

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

        // Engagement Chart
        const engagementCtx = document.getElementById('engagement-chart').getContext('2d');
        if (window.engagementChart) {
            window.engagementChart.destroy();
        }
        
        if (data.engagement_data) {
            window.engagementChart = new Chart(engagementCtx, {
                type: 'line',
                data: {
                    labels: data.engagement_data.labels || [],
                    datasets: [{
                        label: 'Likes',
                        data: data.engagement_data.likes || [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Comments',
                        data: data.engagement_data.comments || [],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Shares',
                        data: data.engagement_data.shares || [],
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
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

        // Reach Chart
        const reachCtx = document.getElementById('reach-chart').getContext('2d');
        if (window.reachChart) {
            window.reachChart.destroy();
        }
        
        if (data.reach_data) {
            window.reachChart = new Chart(reachCtx, {
                type: 'bar',
                data: {
                    labels: data.reach_data.labels || [],
                    datasets: [{
                        label: 'Reach',
                        data: data.reach_data.values || [],
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
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

        // Video Views Chart
        const videoCtx = document.getElementById('video-chart').getContext('2d');
        if (window.videoChart) {
            window.videoChart.destroy();
        }
        
        if (data.video_data) {
            window.videoChart = new Chart(videoCtx, {
                type: 'line',
                data: {
                    labels: data.video_data.labels || [],
                    datasets: [{
                        label: 'Video Views',
                        data: data.video_data.values || [],
                        borderColor: 'rgb(251, 146, 60)',
                        backgroundColor: 'rgba(251, 146, 60, 0.1)',
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

        // Clicks Chart
        const clicksCtx = document.getElementById('clicks-chart').getContext('2d');
        if (window.clicksChart) {
            window.clicksChart.destroy();
        }
        
        if (data.clicks_data) {
            window.clicksChart = new Chart(clicksCtx, {
                type: 'bar',
                data: {
                    labels: data.clicks_data.labels || [],
                    datasets: [{
                        label: 'Clicks',
                        data: data.clicks_data.values || [],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
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

        // Post Types Chart
        const postTypesCtx = document.getElementById('post-types-chart').getContext('2d');
        if (window.postTypesChart) {
            window.postTypesChart.destroy();
        }
        
        if (data.post_types_data) {
            window.postTypesChart = new Chart(postTypesCtx, {
                type: 'pie',
                data: {
                    labels: data.post_types_data.labels || [],
                    datasets: [{
                        data: data.post_types_data.values || [],
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

        // Messages Chart
        const messagesCtx = document.getElementById('messages-chart').getContext('2d');
        if (window.messagesChart) {
            window.messagesChart.destroy();
        }
        
        if (data.messages_data) {
            window.messagesChart = new Chart(messagesCtx, {
                type: 'line',
                data: {
                    labels: data.messages_data.labels || [],
                    datasets: [{
                        label: 'Messages',
                        data: data.messages_data.values || [],
                        borderColor: 'rgb(236, 72, 153)',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
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

// Lazy initialization để tránh block Livewire navigation
function initDataManagement() {
    // Chỉ khởi tạo nếu chưa có instance
    if (!window.__dmInit) {
        initializeDataManagement();
    }
}

// Delay initialization để Livewire hoàn thành navigation
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initDataManagement, 200);
});

// Livewire SPA: ensure re-init when navigating back to this view
document.addEventListener('livewire:navigated', function() {
    // Reset instance cũ nếu có
    if (window.__dmInit) {
        window.__dmInit = false;
    }
    // Delay để tránh conflict
    setTimeout(() => {
        if (document.getElementById('page-select')) {
            initDataManagement();
        }
    }, 150);
});

// Turbo (nếu có) với delay
document.addEventListener('turbo:load', function(){
    setTimeout(() => {
        if (document.getElementById('page-select')) {
            initDataManagement();
        }
    }, 100);
});

// pageshow (bấm Back/Forward) với delay
window.addEventListener('pageshow', function(){
    setTimeout(() => {
        if (document.getElementById('page-select')) {
            initDataManagement();
        }
    }, 100);
});

// Function to format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    
    // Kiểm tra nếu date không hợp lệ
    if (isNaN(date.getTime())) {
        console.warn('Invalid date:', dateString);
        return 'N/A';
    }
    
    return date.toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Function to format numbers
function numberFormat(num) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    return new Intl.NumberFormat('vi-VN').format(num);
}

// Function to show ad details modal
function showAdDetails(postId, pageId) {
    // Tìm post data từ currentPosts hoặc window.currentPosts
    const posts = window.currentPosts || currentPosts;
    if (!posts) {
        console.error('currentPosts not found');
        return;
    }
    const post = posts.find(p => p.id === postId);
    if (!post) {
        console.error('Post not found:', postId);
        return;
    }
    
    // Tạo modal content
    const modalContent = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeAdDetails()">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Chi tiết bài quảng cáo</h3>
                        <button onclick="closeAdDetails()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Post Content -->
                    <div class="mb-6">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                                ${post.from_picture ? `<img src="${post.from_picture}" class="w-8 h-8 object-cover"/>` : ''}
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-600 mb-1">
                                    <span class="font-semibold text-gray-900">${post.from_name || 'Unknown'}</span>
                                    <span>·</span>
                                    <span>${formatDate(post.created_time)}</span>
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded ml-2">ADS</span>
                                </div>
                                <div class="text-sm text-gray-900">${post.message || 'Không có nội dung'}</div>
                            </div>
                        </div>
                        
                        <!-- Attachments -->
                        ${post.attachment_image ? `
                            <div class="mb-3 bg-gray-100 rounded-lg p-2">
                                <div class="flex justify-center">
                                    <img src="${post.attachment_image}" 
                                         class="attachment-image max-w-full object-contain rounded-lg"
                                         onload="this.style.display='block'"
                                         onerror="this.style.display='none'"/>
                                </div>
                            </div>
                        ` : ''}
                        ${post.attachment_source ? `
                            <div class="mb-3 bg-gray-100 rounded-lg p-2">
                                <div class="flex justify-center">
                                    <video controls 
                                           class="attachment-video max-w-full object-contain rounded-lg"
                                           src="${post.attachment_source}">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">${numberFormat(post.total_spend || 0)}</div>
                            <div class="text-sm text-gray-600">Chi phí (VND)</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">${numberFormat(post.total_impressions || 0)}</div>
                            <div class="text-sm text-gray-600">Lượt hiển thị</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">${numberFormat(post.total_clicks || 0)}</div>
                            <div class="text-sm text-gray-600">Lượt nhấp</div>
                        </div>
                        <div class="text-center p-3 bg-orange-50 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600">${((post.avg_ctr || 0) * 100).toFixed(2)}%</div>
                            <div class="text-sm text-gray-600">CTR</div>
                        </div>
                    </div>
                    
                    <!-- Detailed Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Chỉ số hiệu suất</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>CPC trung bình:</span>
                                    <span class="font-medium">${numberFormat(post.avg_cpc || 0)} VND</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>CPM trung bình:</span>
                                    <span class="font-medium">${numberFormat(post.avg_cpm || 0)} VND</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Chuyển đổi:</span>
                                    <span class="font-medium">${numberFormat(post.total_conversions || 0)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Số quảng cáo:</span>
                                    <span class="font-medium">${post.ad_count || 0}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Video & Tương tác</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Lượt xem video:</span>
                                    <span class="font-medium">${numberFormat(post.total_video_views || 0)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Lượt phát video:</span>
                                    <span class="font-medium">${numberFormat(post.total_video_plays || 0)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Xem 75% video:</span>
                                    <span class="font-medium">${numberFormat(post.total_video_p75_watched_actions || 0)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Xem 100% video:</span>
                                    <span class="font-medium">${numberFormat(post.total_video_p100_watched_actions || 0)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Post Engagement -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Tương tác bài viết</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-green-600">${numberFormat(post.comments_count || 0)}</div>
                                <div class="text-gray-600">💬 Comment</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-purple-600">${numberFormat(post.shares_count || 0)}</div>
                                <div class="text-gray-600">↗️ Share</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-red-600">${numberFormat(post.total_impressions || post.impressions || 0)}</div>
                                <div class="text-gray-600">👁️ Tổng Reach</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages Statistics -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Tin nhắn & Tương tác</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-indigo-600">${numberFormat(post.total_messages || 0)}</div>
                                <div class="text-gray-600">📨 Tin nhắn</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-pink-600">${numberFormat(post.total_message_conversations || 0)}</div>
                                <div class="text-gray-600">💬 Hội thoại</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-teal-600">${numberFormat(post.ad_count || 0)}</div>
                                <div class="text-gray-600">📊 Số quảng cáo</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-orange-600">${numberFormat(post.total_runs || 0)}</div>
                                <div class="text-gray-600">🔄 Số lần chạy</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ad Campaign Breakdown -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Chi tiết quảng cáo</h4>
                        <div class="space-y-3">
                            <!-- Campaign Level -->
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between cursor-pointer" onclick="toggleBreakdown('campaign-${post.id}')">
                                    <div class="flex items-center">
                                        <span class="text-lg mr-2">📊</span>
                                        <span class="font-medium">Campaign Level</span>
                                    </div>
                                    <span class="text-gray-500" id="campaign-${post.id}-arrow">▼</span>
                                </div>
                                <div id="campaign-${post.id}-content" class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Số quảng cáo:</span>
                                        <span class="font-semibold">${numberFormat(post.ad_count || 0)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Số lần chạy:</span>
                                        <span class="font-semibold">${numberFormat(post.total_runs || 0)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Ngày bắt đầu:</span>
                                        <span class="font-semibold">${formatDate(post.start_date || post.created_time)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Ngày kết thúc:</span>
                                        <span class="font-semibold">${formatDate(post.end_date || post.updated_time)}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Daily Breakdown -->
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between cursor-pointer" onclick="toggleBreakdown('daily-${post.id}')">
                                    <div class="flex items-center">
                                        <span class="text-lg mr-2">📅</span>
                                        <span class="font-medium">Breakdown theo ngày</span>
                                    </div>
                                    <span class="text-gray-500" id="daily-${post.id}-arrow">▼</span>
                                </div>
                                <div id="daily-${post.id}-content" class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Trung bình/ngày:</span>
                                        <span class="font-semibold">${numberFormat(Math.round((post.total_impressions || 0) / Math.max(1, post.total_runs || 1)))}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Chi phí/ngày:</span>
                                        <span class="font-semibold">${numberFormat(Math.round((post.total_spend || 0) / Math.max(1, post.total_runs || 1)))} VND</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">CTR trung bình:</span>
                                        <span class="font-semibold">${((post.avg_ctr || 0) * 100).toFixed(2)}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">CPC trung bình:</span>
                                        <span class="font-semibold">${numberFormat(post.avg_cpc || 0)} VND</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Performance Breakdown -->
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between cursor-pointer" onclick="toggleBreakdown('performance-${post.id}')">
                                    <div class="flex items-center">
                                        <span class="text-lg mr-2">📈</span>
                                        <span class="font-medium">Breakdown hiệu suất</span>
                                    </div>
                                    <span class="text-gray-500" id="performance-${post.id}-arrow">▼</span>
                                </div>
                                <div id="performance-${post.id}-content" class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">CPM trung bình:</span>
                                        <span class="font-semibold">${numberFormat(post.avg_cpm || 0)} VND</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tổng chuyển đổi:</span>
                                        <span class="font-semibold">${numberFormat(post.total_conversions || 0)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Video views:</span>
                                        <span class="font-semibold">${numberFormat(post.total_video_plays || 0)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Video 75%:</span>
                                        <span class="font-semibold">${numberFormat(post.total_video_p75_watched_actions || 0)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <button onclick="closeAdDetails()" class="px-4 py-2 text-sm bg-gray-500 text-white rounded hover:bg-gray-600">
                            Đóng
                        </button>
                        <a href="/facebook/data-management/post/${postId}/page/${pageId}" 
                           class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                            Xem trang chi tiết →
                        </a>
                        ${post.permalink_url ? `
                            <a href="${post.permalink_url}" target="_blank"
                               class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
                                📘 Xem trên Facebook
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Thêm modal vào body
    document.body.insertAdjacentHTML('beforeend', modalContent);
}

function closeAdDetails() {
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (modal) {
        modal.remove();
    }
}

// Function to toggle breakdown sections
function toggleBreakdown(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const arrow = document.getElementById(sectionId + '-arrow');
    
    if (content && arrow) {
        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            arrow.textContent = '▲';
        } else {
            content.style.display = 'none';
            arrow.textContent = '▼';
        }
    }
}

// Function to show organic post details modal
function showOrganicPostDetails(postId, pageId) {
    const posts = window.currentPosts || currentPosts;
    const post = posts.find(p => p.id === postId);
    
    if (!post) {
        console.error('Post not found:', postId);
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Chi tiết bài viết</h3>
                    <button onclick="closeOrganicPostDetails()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Post Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold mr-3 overflow-hidden">
                            ${post.page_profile_picture_url ? 
                                `<img src="${post.page_profile_picture_url}" class="w-10 h-10 object-cover"/>` :
                                `<div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    ${post.page_name ? post.page_name.charAt(0).toUpperCase() : 'P'}
                                </div>`
                            }
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">${post.page_name || 'Unknown Page'}</div>
                            <div class="text-sm text-gray-500">${formatDate(post.created_time)}</div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-900 whitespace-pre-wrap">${post.message || 'Không có nội dung'}</div>
                </div>
                
                <!-- Attachments -->
                ${post.attachment_image || post.attachment_source || post.full_picture || post.picture ? `
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Hình ảnh & Video</h4>
                        <div class="space-y-3">
                            ${post.attachment_image ? `
                                <div class="bg-gray-100 rounded-lg p-2">
                                    <div class="flex justify-center">
                                        <img src="${post.attachment_image}" 
                                             class="attachment-image max-w-full object-contain rounded-lg"
                                             onload="this.style.display='block'"
                                             onerror="this.style.display='none'"/>
                                    </div>
                                </div>
                            ` : ''}
                            ${post.attachment_source ? `
                                <div class="bg-gray-100 rounded-lg p-2">
                                    <div class="flex justify-center">
                                        <video controls 
                                               class="attachment-video max-w-full object-contain rounded-lg"
                                               src="${post.attachment_source}">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                </div>
                            ` : ''}
                            ${!post.attachment_image && !post.attachment_source && (post.full_picture || post.picture) ? `
                                <div class="bg-gray-100 rounded-lg p-2">
                                    <div class="flex justify-center">
                                        <img src="${post.full_picture || post.picture}" 
                                             class="attachment-image max-w-full object-contain rounded-lg"
                                             onload="this.style.display='block'"
                                             onerror="this.style.display='none'"/>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                ` : ''}
                
                <!-- Organic Post Metrics -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Chỉ số hiệu suất</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-blue-600">${numberFormat(post.impressions || 0)}</div>
                            <div class="text-gray-600">👁️ Hiển thị</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-green-600">${numberFormat(post.clicks || 0)}</div>
                            <div class="text-gray-600">👆 Clicks</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-purple-600">${numberFormat(post.video_views || 0)}</div>
                            <div class="text-gray-600">🎥 Video Views</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-orange-600">${numberFormat(post.engaged_users || 0)}</div>
                            <div class="text-gray-600">👥 Engaged Users</div>
                        </div>
                    </div>
                </div>
                
                <!-- Post Engagement -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Tương tác bài viết</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-green-600">${numberFormat(post.comments_count || 0)}</div>
                            <div class="text-gray-600">💬 Comment</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-purple-600">${numberFormat(post.shares_count || 0)}</div>
                            <div class="text-gray-600">↗️ Share</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-red-600">${numberFormat(post.reactions_count || 0)}</div>
                            <div class="text-gray-600">❤️ Reactions</div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Reactions -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Phân tích Reactions</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-blue-600">${numberFormat(post.likes_count || 0)}</div>
                            <div class="text-gray-600">👍 Like</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-red-600">${numberFormat(post.love_count || 0)}</div>
                            <div class="text-gray-600">❤️ Love</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-yellow-600">${numberFormat(post.wow_count || 0)}</div>
                            <div class="text-gray-600">😮 Wow</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-green-600">${numberFormat(post.haha_count || 0)}</div>
                            <div class="text-gray-600">😂 Haha</div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button onclick="closeOrganicPostDetails()" class="px-4 py-2 text-sm bg-gray-500 text-white rounded hover:bg-gray-600">
                        Đóng
                    </button>
                    ${post.permalink_url ? `
                        <a href="${post.permalink_url}" target="_blank"
                           class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
                            📘 Xem trên Facebook
                        </a>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Function to close organic post details modal
function closeOrganicPostDetails() {
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (modal) {
        modal.remove();
    }
}

// Make functions global
window.showAdDetails = showAdDetails;
window.closeAdDetails = closeAdDetails;
window.showOrganicPostDetails = showOrganicPostDetails;
window.closeOrganicPostDetails = closeOrganicPostDetails;
window.toggleBreakdown = toggleBreakdown;
window.formatDate = formatDate;
window.numberFormat = numberFormat;
</script>

<style>
/* Responsive image/video styling - Fixed layout issues */
.attachment-image, .attachment-video {
    border-radius: 0.5rem;
    display: block;
    max-width: 100%;
    height: auto;
    object-fit: contain;
}

/* Container styling for images - Prevent overflow */
.image-container, .relative.mb-2.bg-gray-100.rounded-lg.p-2 {
    background-color: #f3f4f6;
    border-radius: 0.5rem;
    padding: 0.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100px;
    overflow: hidden;
    max-width: 100%;
}

/* Responsive breakpoints */
@media (max-width: 768px) {
    .attachment-image, .attachment-video {
        max-height: 200px;
        width: 100%;
        object-fit: contain;
    }
    
    .relative.mb-2.bg-gray-100.rounded-lg.p-2 {
        max-width: 100%;
        margin: 0.5rem 0;
    }
}

@media (min-width: 769px) {
    .attachment-image, .attachment-video {
        max-height: 250px;
        width: 100%;
        object-fit: contain;
    }
}

/* Grid view specific styling */
.grid-view .attachment-image, .grid-view .attachment-video {
    max-height: 200px;
    width: 100%;
    object-fit: contain;
}

/* List view specific styling */
.list-view .attachment-image, .list-view .attachment-video {
    max-height: 150px;
    max-width: 200px;
    object-fit: contain;
}

/* Fix for post containers to prevent layout breaking */
.bg-white.rounded-lg.shadow-sm.border.border-gray-200.mb-2 {
    overflow: hidden;
    word-wrap: break-word;
    word-break: break-word;
}

/* Ensure flex containers don't break */
.flex.items-start.gap-2 {
    min-width: 0;
    flex: 1;
}

.flex-1 {
    min-width: 0;
    overflow: hidden;
}

/* Fix for text content that might be too long */
.text-gray-900.mb-1.text-xs {
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
}

/* Attachment container styling */
.attachment-container {
    max-width: 100%;
    overflow: hidden;
}

/* Additional fixes for layout stability */
.min-w-0 {
    min-width: 0;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.break-words {
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
}

/* Ensure consistent post container height when switching views */
.bg-white.rounded-lg.shadow-sm.border.border-gray-200.mb-2 {
    min-height: 120px;
    transition: all 0.2s ease-in-out;
}

/* Maintain consistent attachment heights across all views */
.attachment-container {
    min-height: 60px;
    max-height: 300px;
    overflow: hidden;
}

/* Ensure post content doesn't cause layout shifts */
.flex-1.min-w-0 {
    min-height: 80px;
    display: flex;
    flex-direction: column;
}

/* Consistent spacing for post metrics */
.flex.items-center.gap-3.text-xs.text-gray-600.mt-1 {
    margin-top: 0.5rem;
    min-height: 1.5rem;
}
</style>

<!-- Charts Section -->
<div id="charts-section" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-medium text-gray-900">Biểu đồ tổng hợp của Page</h3>
        <div class="text-sm text-gray-500" id="charts-date-range">
            <!-- Date range will be populated by JavaScript -->
        </div>
    </div>
    
    <!-- First Row: Performance and Spend -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Performance Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">📈 Hiệu suất theo thời gian</h4>
            <canvas id="performance-chart" width="400" height="200"></canvas>
        </div>
        
        <!-- Spend Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">💰 Phân bổ chi phí</h4>
            <canvas id="spend-chart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Second Row: Engagement and Reach -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Engagement Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">❤️ Tương tác theo thời gian</h4>
            <canvas id="engagement-chart" width="400" height="200"></canvas>
        </div>
        
        <!-- Reach Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">👁️ Reach theo thời gian</h4>
            <canvas id="reach-chart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Third Row: Video and Clicks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Video Views Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">🎥 Video Views theo thời gian</h4>
            <canvas id="video-chart" width="400" height="200"></canvas>
        </div>
        
        <!-- Clicks Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">🖱️ Clicks theo thời gian</h4>
            <canvas id="clicks-chart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Fourth Row: Post Types and Messages -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Post Types Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">📊 Phân loại bài viết</h4>
            <canvas id="post-types-chart" width="400" height="200"></canvas>
        </div>
        
        <!-- Messages Chart -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">💬 Tin nhắn theo thời gian</h4>
            <canvas id="messages-chart" width="400" height="200"></canvas>
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

@endif

</x-layouts.app> 