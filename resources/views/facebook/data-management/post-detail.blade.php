<x-layouts.app :title="'Chi tiết bài viết - ' . $post->id">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn .25s ease-out; }
.ai-dots::after { content: ' .'; animation: dots 1.2s steps(3, end) infinite; }
@keyframes dots { 0% { content: ' .'; } 33% { content: ' ..'; } 66% { content: ' ...'; } 100% { content: ' .'; } }
</style>

<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Chi tiết bài viết</h1>
                <p class="text-gray-600">Phân tích chi tiết dữ liệu quảng cáo và breakdown</p>
            </div>
            <a href="{{ route('facebook.data-management.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Post Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ ucfirst($post->type ?? 'Post') }}
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ $post->created_time ? \Carbon\Carbon::parse($post->created_time)->format('d/m/Y H:i') : 'N/A' }}
                    </span>
                </div>
                
                <p class="text-gray-900 mb-3">
                    {{ Str::limit($post->message, 300) ?: 'Không có nội dung' }}
                </p>
                
                <!-- Post Links -->
                <div class="flex items-center space-x-4 mb-3 text-sm">
                    @if($post->id && $post->page_id)
                        <a href="https://facebook.com/{{ $post->page_id }}/posts/{{ $post->id }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                            Xem bài viết Facebook →
                        </a>
                    @endif
                    @if($post->permalink_url)
                        <a href="{{ $post->permalink_url }}" target="_blank" class="text-green-600 hover:text-green-800 font-medium">
                            Xem bài viết gốc →
                        </a>
                    @endif
                    @if($post->page_id)
                        <a href="https://facebook.com/{{ $post->page_id }}" target="_blank" class="text-purple-600 hover:text-purple-800 font-medium">
                            Xem trang →
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- AI Marketing Summary -->
        <div id="ai-summary-box" class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-md">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-indigo-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
                <div>
                    <div class="text-sm font-semibold text-indigo-800 mb-1">Nhận định AI (Chuyên gia Marketing)</div>
                    <div id="ai-summary-text" class="text-sm text-indigo-900 ai-dots" style="white-space: pre-line">Đang tạo nhận định</div>
                </div>
            </div>
        </div>
        
        <!-- Post Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-xl font-bold text-red-600">{{ number_format($post->total_spend ?? 0, 0) }}</div>
                <div class="text-gray-600">Tổng chi phí (VND)</div>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-xl font-bold text-blue-600">{{ number_format($post->total_impressions ?? 0) }}</div>
                <div class="text-gray-600">Tổng hiển thị</div>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-xl font-bold text-green-600">{{ number_format($post->total_clicks ?? 0) }}</div>
                <div class="text-gray-600">Tổng click</div>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-xl font-bold text-purple-600">{{ number_format($post->total_video_views ?? 0) }}</div>
                <div class="text-gray-600">Video Views</div>
            </div>
        </div>
    </div>

    <!-- Detailed Breakdown Data -->
    @if(!empty($detailedBreakdowns))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Phân tích Breakdown Chi tiết</h2>
            
            @foreach($detailedBreakdowns as $breakdownType => $breakdownData)
                <details class="mb-8 group" open>
                    <summary class="cursor-pointer list-none">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 inline-flex items-center">
                            {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}
                            <svg class="w-4 h-4 ml-2 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </h3>
                    </summary>
                    
                    <!-- Breakdown Table -->
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi phí (VND)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hiển thị</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Click</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CTR (%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPC (VND)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPM (VND)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reach</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video Views</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($breakdownData as $value => $metrics)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            @if($value === 'unknown')
                                                @switch($breakdownType)
                                                    @case('action_device')
                                                        Không xác định thiết bị
                                                        @break
                                                    @case('action_destination')
                                                        Không xác định đích đến
                                                        @break
                                                    @case('action_target_id')
                                                        Không xác định đối tượng
                                                        @break
                                                    @case('action_reaction')
                                                        Không xác định phản ứng
                                                        @break
                                                    @case('action_video_sound')
                                                        Không xác định âm thanh
                                                        @break
                                                    @case('action_video_type')
                                                        Không xác định loại video
                                                        @break
                                                    @case('action_carousel_card_id')
                                                        Không xác định thẻ carousel
                                                        @break
                                                    @case('action_carousel_card_name')
                                                        Không xác định tên thẻ
                                                        @break
                                                    @case('action_canvas_component_name')
                                                        Không xác định thành phần
                                                        @break
                                                    @case('age')
                                                        Không xác định độ tuổi
                                                        @break
                                                    @case('gender')
                                                        Không xác định giới tính
                                                        @break
                                                    @case('country')
                                                        Không xác định quốc gia
                                                        @break
                                                    @case('region')
                                                        Không xác định khu vực
                                                        @break
                                                    @case('publisher_platform')
                                                        Không xác định nền tảng
                                                        @break
                                                    @case('device_platform')
                                                        Không xác định thiết bị
                                                        @break
                                                    @case('impression_device')
                                                        Không xác định thiết bị hiển thị
                                                        @break
                                                    @default
                                                        Không xác định
                                                @endswitch
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">{{ number_format($metrics['spend'], 0) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['impressions']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['clicks']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">{{ number_format($metrics['ctr'], 2) }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['cpc'], 0) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['cpm'], 0) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['reach']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['frequency'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-semibold">{{ number_format($metrics['video_views']) }}</td>
                                    </tr>
                                   
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Video Metrics Table -->
                    @if(array_sum(array_column($breakdownData, 'video_views')) > 0)
                        <div class="overflow-x-auto">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Thống kê Video</h4>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video Views</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video Plays</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">25% Watched</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">50% Watched</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">75% Watched</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">95% Watched</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">100% Watched</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thruplays</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">30s Watched</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($breakdownData as $value => $metrics)
                                        @if($metrics['video_views'] > 0)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    @if($value === 'unknown')
                                                        @switch($breakdownType)
                                                            @case('action_device')
                                                                Không xác định thiết bị
                                                                @break
                                                            @case('action_destination')
                                                                Không xác định đích đến
                                                                @break
                                                            @case('action_target_id')
                                                                Không xác định đối tượng
                                                                @break
                                                            @case('action_reaction')
                                                                Không xác định phản ứng
                                                                @break
                                                            @case('action_video_sound')
                                                                Không xác định âm thanh
                                                                @break
                                                            @case('action_video_type')
                                                                Không xác định loại video
                                                                @break
                                                            @case('action_carousel_card_id')
                                                                Không xác định thẻ carousel
                                                                @break
                                                            @case('action_carousel_card_name')
                                                                Không xác định tên thẻ
                                                                @break
                                                            @case('action_canvas_component_name')
                                                                Không xác định thành phần
                                                                @break
                                                            @case('age')
                                                                Không xác định độ tuổi
                                                                @break
                                                            @case('gender')
                                                                Không xác định giới tính
                                                                @break
                                                            @case('country')
                                                                Không xác định quốc gia
                                                                @break
                                                            @case('region')
                                                                Không xác định khu vực
                                                                @break
                                                            @case('publisher_platform')
                                                                Không xác định nền tảng
                                                                @break
                                                            @case('device_platform')
                                                                Không xác định thiết bị
                                                                @break
                                                            @case('impression_device')
                                                                Không xác định thiết bị hiển thị
                                                                @break
                                                            @default
                                                                Không xác định
                                                        @endswitch
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-semibold">{{ number_format($metrics['video_views']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['video_plays']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['video_p25_watched_actions']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['video_p50_watched_actions']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">{{ number_format($metrics['video_p75_watched_actions']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['video_p95_watched_actions']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">{{ number_format($metrics['video_p100_watched_actions']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['thruplays']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['video_30_sec_watched']) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </details>
            @endforeach
                </div>
    @endif

    <!-- Breakdown Charts -->
    @if(!empty($breakdowns))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Biểu đồ Breakdown</h2>
            
            @foreach($breakdowns as $breakdownType => $breakdownData)
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Spend Chart -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Chi phí theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h4>
                            <canvas id="spend-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                        </div>
                        
                        <!-- Impressions Chart -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Hiển thị theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h4>
                            <canvas id="impressions-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                        </div>
                        
                        <!-- CTR Chart -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-700 mb-3">CTR theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h4>
                            <canvas id="ctr-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                        </div>
                        
                        <!-- Video Views Chart -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Video Views theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h4>
                            <canvas id="video-views-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
    @endif

    <!-- Insights Charts -->
    @if(!empty($insights['daily_data']))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Phân tích theo thời gian</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Performance Over Time -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Hiệu suất theo thời gian</h4>
                    <canvas id="performance-chart" width="400" height="200"></canvas>
                </div>
                
                <!-- Spend Over Time -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Chi phí theo thời gian</h4>
                    <canvas id="spend-time-chart" width="400" height="200"></canvas>
                </div>
                
                <!-- Video Metrics Over Time -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Video Metrics theo thời gian</h4>
                    <canvas id="video-metrics-chart" width="400" height="200"></canvas>
                </div>
                
                <!-- CTR Over Time -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">CTR theo thời gian</h4>
                    <canvas id="ctr-time-chart" width="400" height="200"></canvas>
            </div>
        </div>
            </div>
    @endif

    <!-- Actions Data -->
                @if(!empty($actions['summary']))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Phân tích Actions</h2>
            
                        <!-- Actions Summary -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tổng hợp Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach(array_slice($actions['summary'], 0, 8) as $actionType => $value)
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-xl font-bold text-blue-600">{{ number_format($value) }}</div>
                            <div class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $actionType)) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
            <!-- Detailed Actions Table -->
                    @if(!empty($actions['detailed_actions']))
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Chi tiết Actions</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại Action</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng giá trị</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lần xuất hiện</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($actions['detailed_actions'] as $actionType => $details)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ ucfirst(str_replace('_', ' ', $actionType)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">
                                            {{ number_format($details['total_value']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($details['occurrences']) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                        @php
                                            $descriptions = [
                                                'video_view' => 'Lượt xem video',
                                                'post_engagement' => 'Tương tác với bài viết',
                                                'page_engagement' => 'Tương tác với trang',
                                                'link_click' => 'Click vào link',
                                                'like' => 'Lượt thích',
                                                'comment' => 'Bình luận',
                                                'share' => 'Chia sẻ',
                                                'onsite_conversion.messaging_conversation_started_7d' => 'Bắt đầu cuộc trò chuyện tin nhắn (7 ngày)',
                                                'onsite_conversion.total_messaging_connection' => 'Tổng kết nối tin nhắn',
                                                'onsite_conversion.lead' => 'Lead từ website',
                                                'onsite_web_purchase' => 'Mua hàng từ website',
                                                'onsite_conversion.purchase' => 'Mua hàng',
                                                'onsite_conversion.messaging_conversation_replied_7d' => 'Trả lời tin nhắn (7 ngày)',
                                                'onsite_conversion.messaging_user_call_placed' => 'Gọi điện từ tin nhắn',
                                                'onsite_conversion.post_save' => 'Lưu bài viết',
                                                'onsite_conversion.messaging_welcome_message_view' => 'Xem tin nhắn chào mừng',
                                                'onsite_conversion.messaging_user_depth_2_message_send' => 'Gửi tin nhắn độ sâu 2',
                                                'onsite_conversion.messaging_user_depth_3_message_send' => 'Gửi tin nhắn độ sâu 3',
                                                'onsite_conversion.messaging_user_depth_5_message_send' => 'Gửi tin nhắn độ sâu 5',
                                                'onsite_conversion.messaging_60s_call_connect' => 'Kết nối cuộc gọi 60s',
                                                'onsite_conversion.messaging_20s_call_connect' => 'Kết nối cuộc gọi 20s',
                                                'onsite_conversion.messaging_first_reply' => 'Trả lời tin nhắn đầu tiên',
                                                'onsite_conversion.lead_grouped' => 'Lead được nhóm',
                                                'onsite_app_purchase' => 'Mua hàng từ app',
                                                'omni_purchase' => 'Mua hàng đa kênh',
                                                'post_interaction_gross' => 'Tương tác thô với bài viết',
                                                'post_reaction' => 'Phản ứng với bài viết',
                                                'post' => 'Bài viết',
                                                'lead' => 'Lead',
                                                'offsite_complete_registration_add_meta_leads' => 'Đăng ký hoàn thành từ Meta',
                                                'offsite_search_add_meta_leads' => 'Tìm kiếm từ Meta',
                                                'offsite_content_view_add_meta_leads' => 'Xem nội dung từ Meta'
                                            ];
                                        @endphp
                                                    {{ $descriptions[$actionType] ?? 'Tương tác khác' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
            
            <!-- Actions Chart -->
            @if(!empty($actions['daily_actions']))
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Actions theo thời gian</h4>
                    <canvas id="actions-chart" width="400" height="200"></canvas>
                    </div>
                @endif
            </div>
    @endif


</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AI summary for this post
    try {
        const pageId = "{{ $post->page_id }}";
        const postId = "{{ $post->id }}";
        // Gửi đầy đủ dữ liệu (bao gồm video metrics, actions, insights thô)
        const metrics = {
            summary: @json($insights['summary'] ?? []),
            video: {
                views: @json($insights['summary']['video_views'] ?? ($insights['video']['views'] ?? null)),
                view_time: @json($insights['summary']['video_view_time'] ?? ($insights['video']['view_time'] ?? null)),
                avg_time: @json($insights['summary']['video_avg_time_watched'] ?? ($insights['video']['avg_time'] ?? null)),
                plays: @json($insights['summary']['video_plays'] ?? ($insights['video']['plays'] ?? null)),
                p25: @json($insights['summary']['video_p25_watched_actions'] ?? ($insights['video']['p25'] ?? null)),
                p50: @json($insights['summary']['video_p50_watched_actions'] ?? ($insights['video']['p50'] ?? null)),
                p75: @json($insights['summary']['video_p75_watched_actions'] ?? ($insights['video']['p75'] ?? null)),
                p95: @json($insights['summary']['video_p95_watched_actions'] ?? ($insights['video']['p95'] ?? null)),
                p100: @json($insights['summary']['video_p100_watched_actions'] ?? ($insights['video']['p100'] ?? null)),
                thruplays: @json($insights['summary']['thruplays'] ?? ($insights['video']['thruplays'] ?? null)),
                video_30s: @json($insights['summary']['video_30_sec_watched'] ?? ($insights['video']['video_30s'] ?? null)),
            },
            breakdowns: @json($breakdowns ?? []),
            detailedBreakdowns: @json($detailedBreakdowns ?? []),
            insights: @json($insights ?? []),
            actions: @json($actions ?? [])
        };
        // Hỗ trợ debug: đặt window._aiDebug = true hoặc thêm ?debug=1 vào URL
        const isDebug = (window._aiDebug === true) || new URLSearchParams(location.search).has('debug');
        const aiUrl = `{{ route('facebook.data-management.ai-summary') }}` + (isDebug ? '?debug=1' : '');
        fetch(aiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ page_id: pageId, post_id: postId, metrics })
        }).then(r => r.json()).then(res => {
            const el = document.getElementById('ai-summary-text');
            if (!el) return;
            const txt = res.summary || 'Không có nhận định.';
            let html = formatAiSummary(txt);
            if (isDebug) {
                const debugDump = {
                    ok: res.ok ?? true,
                    env_key_present: res.env_key_present ?? undefined,
                    since: res.since ?? undefined,
                    until: res.until ?? undefined,
                    metrics_sent: res.metrics_sent ?? undefined,
                };
                html += '<br><details class="mt-2"><summary class="cursor-pointer text-xs text-gray-600">Xem debug (payload gửi AI)</summary>' +
                        '<pre class="text-xs whitespace-pre-wrap bg-gray-50 p-2 border mt-2 rounded">' +
                        (typeof debugDump === 'object' ? JSON.stringify(debugDump, null, 2) : String(debugDump)) +
                        '</pre></details>';
            }
            el.classList.remove('ai-dots');
            el.innerHTML = html;
            el.classList.add('animate-fade-in');
        }).catch(() => {
            const el = document.getElementById('ai-summary-text');
            if (el) {
                const isDbg = (window._aiDebug === true) || new URLSearchParams(location.search).has('debug');
                el.textContent = isDbg ? 'Không tạo được nhận định AI (check network/ENV). Bật debug để xem chi tiết.' : 'Không tạo được nhận định AI.';
            }
        });
    } catch (e) {}
    
    // Định dạng AI summary: hỗ trợ **bold** và bullet xuống dòng
    function formatAiSummary(text) {
        if (!text) return '';
        let html = text
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1<\/strong>')
            .replace(/^(?:[-\*])\s+/gm, '• ')
            .replace(/\n/g, '<br>');
        return html;
    }
    // Breakdown Charts
    @if(!empty($breakdowns))
        @foreach($breakdowns as $breakdownType => $breakdownData)
            // Spend Chart
            const spendCtx{{ $loop->index }} = document.getElementById('spend-chart-{{ $breakdownType }}').getContext('2d');
            new Chart(spendCtx{{ $loop->index }}, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($breakdownData, 'breakdown_value')) !!},
                    datasets: [{
                        label: 'Chi phí (VND)',
                        data: {!! json_encode(array_column($breakdownData, 'spend')) !!},
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Impressions Chart
            const impressionsCtx{{ $loop->index }} = document.getElementById('impressions-chart-{{ $breakdownType }}').getContext('2d');
            new Chart(impressionsCtx{{ $loop->index }}, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($breakdownData, 'breakdown_value')) !!},
                    datasets: [{
                        label: 'Hiển thị',
                        data: {!! json_encode(array_column($breakdownData, 'impressions')) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // CTR Chart
            const ctrCtx{{ $loop->index }} = document.getElementById('ctr-chart-{{ $breakdownType }}').getContext('2d');
            new Chart(ctrCtx{{ $loop->index }}, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($breakdownData, 'breakdown_value')) !!},
                    datasets: [{
                        label: 'CTR (%)',
                        data: {!! json_encode(array_map(function($item) { return ($item['ctr'] ?? 0) * 100; }, $breakdownData)) !!},
                        backgroundColor: 'rgba(168, 85, 247, 0.8)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Video Views Chart
            const videoViewsCtx{{ $loop->index }} = document.getElementById('video-views-chart-{{ $breakdownType }}').getContext('2d');
            new Chart(videoViewsCtx{{ $loop->index }}, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($breakdownData, 'breakdown_value')) !!},
                    datasets: [{
                        label: 'Video Views',
                        data: {!! json_encode(array_column($breakdownData, 'video_views')) !!},
                        backgroundColor: 'rgba(251, 146, 60, 0.8)',
                        borderColor: 'rgba(251, 146, 60, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endforeach
    @endif

    // Insights Charts
    @if(!empty($insights['daily_data']))
        const dailyData = {!! json_encode($insights['daily_data']) !!};
        const labels = dailyData.map(item => item.date);
        const impressions = dailyData.map(item => item.impressions);
        const clicks = dailyData.map(item => item.clicks);
        const spend = dailyData.map(item => item.spend);
        const videoViews = dailyData.map(item => item.video_views);
        const videoP75 = dailyData.map(item => item.video_p75_watched_actions);
        const videoP100 = dailyData.map(item => item.video_p100_watched_actions);
        const ctr = dailyData.map(item => (item.ctr || 0) * 100);

        // Performance Chart
        const performanceCtx = document.getElementById('performance-chart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Impressions',
                    data: impressions,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Clicks',
                    data: clicks,
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

        // Spend Time Chart
        const spendTimeElement = document.getElementById('spend-time-chart');
        if (spendTimeElement) {
            const spendTimeCtx = spendTimeElement.getContext('2d');
            new Chart(spendTimeCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Chi phí (VND)',
                        data: spend,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
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

        // Video Metrics Chart
        const videoMetricsElement = document.getElementById('video-metrics-chart');
        if (videoMetricsElement) {
            const videoMetricsCtx = videoMetricsElement.getContext('2d');
            new Chart(videoMetricsCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Video Views',
                        data: videoViews,
                        borderColor: 'rgb(251, 146, 60)',
                        backgroundColor: 'rgba(251, 146, 60, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'P75 Watched',
                        data: videoP75,
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'P100 Watched',
                        data: videoP100,
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

        // CTR Time Chart
        const ctrTimeElement = document.getElementById('ctr-time-chart');
        if (ctrTimeElement) {
            const ctrTimeCtx = ctrTimeElement.getContext('2d');
            new Chart(ctrTimeCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'CTR (%)',
                        data: ctr,
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

        // Actions Chart
        @if(!empty($actions['daily_actions']))
            const actionsData = {!! json_encode($actions['daily_actions']) !!};
            const actionLabels = Object.keys(actionsData).sort();
            const actionTypes = [];
            const actionValues = {};

            // Lấy tất cả action types
            actionLabels.forEach(date => {
                Object.keys(actionsData[date]).forEach(actionType => {
                    if (!actionTypes.includes(actionType)) {
                        actionTypes.push(actionType);
                    }
                });
            });

            // Tạo datasets cho từng action type
            actionTypes.forEach(actionType => {
                actionValues[actionType] = actionLabels.map(date => actionsData[date][actionType] || 0);
            });

            const actionsElement = document.getElementById('actions-chart');
            if (actionsElement) {
                const actionsCtx = actionsElement.getContext('2d');
                const colors = [
                    'rgb(59, 130, 246)', 'rgb(16, 185, 129)', 'rgb(245, 158, 11)', 
                    'rgb(239, 68, 68)', 'rgb(139, 92, 246)', 'rgb(236, 72, 153)'
                ];

                new Chart(actionsCtx, {
                    type: 'line',
                    data: {
                        labels: actionLabels,
                        datasets: actionTypes.map((actionType, index) => ({
                            label: actionType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                            data: actionValues[actionType],
                            borderColor: colors[index % colors.length],
                            backgroundColor: colors[index % colors.length].replace('rgb', 'rgba').replace(')', ', 0.1)'),
                            tension: 0.1
                        }))
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
    @endif
    @endif
});
</script>
</x-layouts.app>
