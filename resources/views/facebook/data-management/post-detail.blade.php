<x-layouts.app :title="'Chi tiết bài viết - ' . $post->id">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                    @if($post->permalink_url)
                        <a href="{{ $post->permalink_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                            📘 Xem bài viết Facebook →
                        </a>
                    @elseif($post->id && $post->page_id)
                        <a href="https://facebook.com/{{ $post->page_id }}/posts/{{ $post->id }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                            📘 Xem bài viết Facebook →
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
        
        <!-- Post Attachments (show all possible images/videos) -->
        @php
            $__attachments = [];
            // 1) attachments JSON from Graph API
            if (!empty($post->attachments)) {
                $raw = is_string($post->attachments) ? json_decode($post->attachments, true) : $post->attachments;
                if (is_array($raw) && isset($raw['data']) && is_array($raw['data'])) {
                    foreach ($raw['data'] as $att) {
                        if (!empty($att['media_type']) && $att['media_type'] === 'video' && !empty($att['media']['source'])) {
                            $__attachments[] = ['type' => 'video', 'src' => $att['media']['source'], 'title' => $att['title'] ?? null];
                        } elseif (!empty($att['media']['image']['src'])) {
                            $__attachments[] = ['type' => 'image', 'src' => $att['media']['image']['src'], 'title' => $att['title'] ?? null];
                        }
                    }
                }
            }
            // 2) arrays of urls
            if (!empty($post->attachments_image)) {
                $imgs = is_string($post->attachments_image) ? json_decode($post->attachments_image, true) : $post->attachments_image;
                if (is_array($imgs)) { foreach ($imgs as $u) { if ($u) $__attachments[] = ['type' => 'image', 'src' => $u]; } }
            }
            if (!empty($post->attachments_source)) {
                $vids = is_string($post->attachments_source) ? json_decode($post->attachments_source, true) : $post->attachments_source;
                if (is_array($vids)) { foreach ($vids as $u) { if ($u) $__attachments[] = ['type' => 'video', 'src' => $u]; } }
            }
            // 3) single fields (ads)
            if (!empty($post->attachment_image)) $__attachments[] = ['type' => 'image', 'src' => $post->attachment_image];
            if (!empty($post->attachment_source)) $__attachments[] = ['type' => 'video', 'src' => $post->attachment_source];
            // 4) fallbacks
            if (empty($__attachments)) {
                if (!empty($post->full_picture)) $__attachments[] = ['type' => 'image', 'src' => $post->full_picture];
                elseif (!empty($post->picture)) $__attachments[] = ['type' => 'image', 'src' => $post->picture];
            }
        @endphp
        @if(count($__attachments) > 0)
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Hình ảnh & Video</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($__attachments as $att)
                        @if($att['type'] === 'image')
                            <div class="bg-white rounded-lg overflow-hidden">
                                <div class="w-full aspect-[4/3] bg-gray-100 flex items-center justify-center">
                                    <img src="{{ $att['src'] }}" class="w-full h-full object-contain" alt="Attachment"/>
                                </div>
                                @if(!empty($att['title']))
                                    <div class="px-2 py-1 text-xs text-gray-600 text-center">{{ $att['title'] }}</div>
                                @endif
                            </div>
                        @else
                            <div class="bg-white rounded-lg overflow-hidden">
                                <div class="w-full aspect-[4/3] bg-gray-100 flex items-center justify-center">
                                    <video controls class="w-full h-full object-contain">
                                        <source src="{{ $att['src'] }}" type="video/mp4">
                                    </video>
                                </div>
                                @if(!empty($att['title']))
                                    <div class="px-2 py-1 text-xs text-gray-600 text-center">{{ $att['title'] }}</div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- AI Marketing Summary (run on button click) -->
        <div id="ai-summary-box" class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-md">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-indigo-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-indigo-800 mb-1">Nhận định AI (Chuyên gia Marketing)</div>
                        <div id="ai-summary-text" class="text-sm text-indigo-900" style="white-space: pre-line">Nhấn "Tạo nhận định AI" để chạy.</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button id="btn-run-ai" onclick="runAiSummary()" class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Tạo nhận định AI</button>
                    <button onclick="analyzeVideoWithGemini('{{ $post->id }}', '{{ $post->page_id }}')" 
                            class="px-3 py-1.5 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Phân tích Video (Gemini)
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Gemini Video Analysis Results (Always visible) -->
        <div id="gemini-analysis-results" class="hidden mb-6 p-6 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Phân tích Video bằng Gemini AI</h3>
                        <p class="text-sm text-gray-600">Phân tích chuyên sâu về nội dung y khoa và quảng cáo</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Gemini 2.5 Flash</span>
                    <button onclick="toggleGeminiAnalysis()" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Video Error Check -->
            <div id="video-errors-section" class="hidden mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                <h4 class="text-sm font-semibold text-yellow-800 mb-2">⚠️ Cảnh báo Video Metrics</h4>
                        <div id="video-errors-list" class="text-sm text-yellow-700 space-y-1">
                    <!-- Video errors will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Analysis Content -->
            <div id="gemini-analysis-content" class="text-sm text-gray-700">
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <p>Nhấn "Phân tích Video (Gemini)" để bắt đầu phân tích</p>
                </div>
            </div>
            
            <!-- Raw JSON (for debugging) -->
            <details class="mt-6">
                <summary class="cursor-pointer text-xs text-gray-500 hover:text-gray-700 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <span>Xem JSON gốc (Debug)</span>
                </summary>
                <pre id="gemini-raw-json" class="text-xs bg-gray-100 p-4 rounded mt-3 overflow-auto max-h-60 border"></pre>
            </details>
        </div>
        
        <!-- Post Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @if(isset($post->has_ads) && $post->has_ads)
                <!-- Ads Post Metrics -->
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
            @else
                <!-- Organic Post Metrics -->
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-blue-600">{{ number_format($post->post_impressions ?? 0) }}</div>
                    <div class="text-gray-600">Impressions</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-green-600">{{ number_format($post->post_clicks ?? 0) }}</div>
                    <div class="text-gray-600">Clicks</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-purple-600">{{ number_format($post->post_video_views ?? 0) }}</div>
                    <div class="text-gray-600">Video Views</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-orange-600">{{ number_format($post->post_engaged_users ?? 0) }}</div>
                    <div class="text-gray-600">Engaged Users</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Video Analysis Section -->
    @if(isset($insights['summary']['video_views']) && $insights['summary']['video_views'] > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Phân tích Video Chi tiết</h2>
                <div class="flex items-center space-x-3">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Tổng lượt xem:</span> 
                        {{ number_format($insights['summary']['video_views'] ?? 0) }}
                    </div>
                    <button onclick="analyzeVideoWithGemini('{{ $post->id }}', '{{ $post->page_id }}')" 
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg shadow hover:bg-purple-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Phân tích AI (Gemini)
                    </button>
                </div>
            </div>
            
            <!-- AI Analysis Results -->
            <div id="ai-analysis-results" class="hidden mb-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Kết quả phân tích AI</h3>
                <div id="analysis-content" class="text-sm text-gray-700">
                    <!-- Analysis content will be loaded here -->
                </div>
            </div>
            
            
            <!-- Video Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-xl font-bold text-purple-600">{{ number_format($insights['summary']['video_plays'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">Video Plays</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-xl font-bold text-blue-600">{{ number_format($insights['summary']['video_p75_watched_actions'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">75% Watched</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-xl font-bold text-green-600">{{ number_format($insights['summary']['video_p100_watched_actions'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">100% Watched</div>
                </div>
                <div class="text-center p-3 bg-orange-50 rounded-lg">
                    <div class="text-xl font-bold text-orange-600">{{ number_format($insights['summary']['video_30_sec_watched'] ?? 0) }}</div>
                    <div class="text-sm text-gray-600">30s Watched</div>
                </div>
            </div>
            
            <!-- Video Detailed Metrics -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Chi tiết Video Metrics</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Video Completion Rates -->
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Tỷ lệ hoàn thành video</h4>
                        @php
                            $videoViews = $insights['summary']['video_views'] ?? 0;
                            $p25 = $insights['summary']['video_p25_watched_actions'] ?? 0;
                            $p50 = $insights['summary']['video_p50_watched_actions'] ?? 0;
                            $p75 = $insights['summary']['video_p75_watched_actions'] ?? 0;
                            $p95 = $insights['summary']['video_p95_watched_actions'] ?? 0;
                            $p100 = $insights['summary']['video_p100_watched_actions'] ?? 0;
                        @endphp
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span>25% watched:</span>
                                <span class="font-medium">{{ $videoViews > 0 ? number_format(($p25 / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>50% watched:</span>
                                <span class="font-medium">{{ $videoViews > 0 ? number_format(($p50 / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>75% watched:</span>
                                <span class="font-medium text-blue-600">{{ $videoViews > 0 ? number_format(($p75 / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>95% watched:</span>
                                <span class="font-medium">{{ $videoViews > 0 ? number_format(($p95 / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>100% watched:</span>
                                <span class="font-medium text-green-600">{{ $videoViews > 0 ? number_format(($p100 / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video Engagement -->
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Tương tác video</h4>
                        @php
                            $thruplays = $insights['summary']['thruplays'] ?? 0;
                            $video30s = $insights['summary']['video_30_sec_watched'] ?? 0;
                        @endphp
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span>Thruplays:</span>
                                <span class="font-medium">{{ number_format($thruplays) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>30s watched:</span>
                                <span class="font-medium">{{ number_format($video30s) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Thruplay rate:</span>
                                <span class="font-medium text-purple-600">{{ $videoViews > 0 ? number_format(($thruplays / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>30s rate:</span>
                                <span class="font-medium text-orange-600">{{ $videoViews > 0 ? number_format(($video30s / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video Time Metrics -->
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-700">Thời gian xem</h4>
                        @php
                            $avgTime = $insights['summary']['video_avg_time_watched'] ?? 0;
                            $viewTime = $insights['summary']['video_view_time'] ?? 0;
                        @endphp
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span>Avg time watched:</span>
                                <span class="font-medium">{{ $avgTime > 0 ? number_format($avgTime, 1) . 's' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total view time:</span>
                                <span class="font-medium">{{ $viewTime > 0 ? number_format($viewTime) . 's' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Video plays:</span>
                                <span class="font-medium">{{ number_format($insights['summary']['video_plays'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Play rate:</span>
                                <span class="font-medium text-indigo-600">{{ $videoViews > 0 ? number_format((($insights['summary']['video_plays'] ?? 0) / $videoViews) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Video Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Video Completion Funnel -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Video Completion Funnel</h4>
                    <canvas id="video-completion-funnel" width="400" height="200"></canvas>
                </div>
                
                <!-- Video Retention Rates -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Video Retention Rates</h4>
                    <canvas id="video-retention-rates" width="400" height="200"></canvas>
                </div>
                
                <!-- Video Engagement Metrics -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Video Engagement Metrics</h4>
                    <canvas id="video-engagement-metrics" width="400" height="200"></canvas>
                </div>
                
                <!-- Video Performance Comparison -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Video Performance Comparison</h4>
                    <canvas id="video-performance-comparison" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Video Time Series Chart (if daily data available) -->
            @if(!empty($insights['daily_data']))
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Video Metrics theo thời gian</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Video Performance Over Time</h4>
                        <canvas id="video-time-series-chart" width="800" height="300"></canvas>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Insights Charts - Moved to top -->
    @if(!empty($insights['daily_data']))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Phân tích theo thời gian</h2>
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Khoảng thời gian:</span> 
                    @if(!empty($insights['daily_data']))
                        @php
                            $firstDate = \Carbon\Carbon::parse($insights['daily_data'][0]['date'] ?? 'now');
                            $lastDate = \Carbon\Carbon::parse(end($insights['daily_data'])['date'] ?? 'now');
                        @endphp
                        {{ $firstDate->format('d/m/Y H:i') }} - {{ $lastDate->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
            
            <div class="mb-3">
                <p class="text-sm text-gray-600 italic">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    Dữ liệu được nhóm theo ngày để tránh trùng lặp thời gian và hiển thị rõ ràng hơn. 
                    <span class="font-medium text-blue-600">Hover vào biểu đồ để xem chi tiết từng thời điểm.</span>
                </p>
                <div id="time-data-notice" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span class="text-sm text-yellow-800">
                            <strong>Lưu ý:</strong> Tất cả dữ liệu có cùng thời gian. Biểu đồ hiển thị dạng gấp khúc để dễ đọc.
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-semibold">{{ number_format($metrics['video_plays'] ?? ($metrics['video_views'] ?? 0)) }}</td>
                                    </tr>
                                   
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Video Metrics Table -->
                    @php
                        $sumVideoPlays = array_sum(array_map(function($m){ return (int)($m['video_plays'] ?? ($m['video_views'] ?? 0)); }, $breakdownData));
                    @endphp
                    @if($sumVideoPlays > 0)
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
                                        @php $rowVideoPlays = (int)($metrics['video_plays'] ?? ($metrics['video_views'] ?? 0)); @endphp
                                        @if($rowVideoPlays > 0)
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
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-semibold">{{ number_format($rowVideoPlays) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['video_plays'] ?? 0) }}</td>
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

                    <!-- Breakdown Chart for this specific breakdown type -->
                    <div class="mt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Biểu đồ {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h4>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Spend Chart -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Chi phí theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h5>
                                <canvas id="spend-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                            </div>
                            
                            <!-- Impressions Chart -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Hiển thị theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h5>
                                <canvas id="impressions-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                            </div>
                            
                            <!-- CTR Chart -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="text-md font-medium text-gray-700 mb-3">CTR theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h5>
                                <canvas id="ctr-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                            </div>
                            
                            <!-- Video Views Chart -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Video Views theo {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h5>
                                <canvas id="video-views-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                            </div>
                        </div>
                        
                    </div>

                    @php
                        $sumMessaging = array_sum(array_map(function($m){
                            return (int)($m['messaging_conversation_started_7d'] ?? 0)
                                 + (int)($m['total_messaging_connection'] ?? 0)
                                 + (int)($m['messaging_conversation_replied_7d'] ?? 0)
                                 + (int)($m['messaging_welcome_message_view'] ?? 0)
                                 + (int)($m['messaging_user_depth_2_message_send'] ?? 0)
                                 + (int)($m['messaging_user_depth_3_message_send'] ?? 0)
                                 + (int)($m['messaging_user_depth_5_message_send'] ?? 0)
                                 + (int)($m['messaging_first_reply'] ?? 0)
                                 + (int)($m['messaging_block'] ?? 0);
                        }, $breakdownData));
                    @endphp
                    @if($sumMessaging > 0)
                        <div class="overflow-x-auto mt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Thống kê Tin nhắn & Engagement</h4>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bắt đầu trò chuyện (7d)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng kết nối</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trả lời (7d)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Xem tin nhắn chào mừng</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trả lời đầu tiên</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gửi tin nhắn độ sâu 2</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gửi tin nhắn độ sâu 3</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gửi tin nhắn độ sâu 5</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chặn tin nhắn</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($breakdownData as $value => $metrics)
                                        @php $rowMsg = (int)($metrics['messaging_conversation_started_7d'] ?? 0)
                                            + (int)($metrics['total_messaging_connection'] ?? 0)
                                            + (int)($metrics['messaging_conversation_replied_7d'] ?? 0)
                                            + (int)($metrics['messaging_welcome_message_view'] ?? 0)
                                            + (int)($metrics['messaging_user_depth_2_message_send'] ?? 0)
                                            + (int)($metrics['messaging_user_depth_3_message_send'] ?? 0)
                                            + (int)($metrics['messaging_user_depth_5_message_send'] ?? 0)
                                            + (int)($metrics['messaging_first_reply'] ?? 0)
                                            + (int)($metrics['messaging_block'] ?? 0);
                                        @endphp
                                        @if($rowMsg > 0)
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
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['messaging_conversation_started_7d'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['total_messaging_connection'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['messaging_conversation_replied_7d'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['messaging_welcome_message_view'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">{{ number_format($metrics['messaging_first_reply'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['messaging_user_depth_2_message_send'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($metrics['messaging_user_depth_3_message_send'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">{{ number_format($metrics['messaging_user_depth_5_message_send'] ?? 0) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">{{ number_format($metrics['messaging_block'] ?? 0) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Messaging Charts for this breakdown -->
                        @if($sumMessaging > 0)
                            <div class="mt-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Biểu đồ Messaging & Engagement - {{ ucfirst(str_replace('_', ' ', $breakdownType)) }}</h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Messaging Overview Chart -->
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h5 class="text-md font-medium text-gray-700 mb-3">Tổng quan Messaging</h5>
                                        <canvas id="messaging-overview-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                                    </div>
                                    
                                    <!-- Messaging Depth Chart -->
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h5 class="text-md font-medium text-gray-700 mb-3">Độ sâu Messaging</h5>
                                        <canvas id="messaging-depth-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                                    </div>
                                    
                                    <!-- Messaging Engagement Chart -->
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h5 class="text-md font-medium text-gray-700 mb-3">Engagement Messaging</h5>
                                        <canvas id="messaging-engagement-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                                    </div>
                                    
                                    <!-- Messaging Quality Chart -->
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h5 class="text-md font-medium text-gray-700 mb-3">Chất lượng Messaging</h5>
                                        <canvas id="messaging-quality-chart-{{ $breakdownType }}" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </details>
            @endforeach
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


    <!-- Serialize metrics safely to JSON for JS consumption -->
    @php
        // Collect video URLs for AI
        $__videoUrls = [];
        // From attachments JSON
        if (!empty($post->attachments)) {
            $raw = is_string($post->attachments) ? json_decode($post->attachments, true) : $post->attachments;
            if (is_array($raw) && isset($raw['data']) && is_array($raw['data'])) {
                foreach ($raw['data'] as $att) {
                    if (!empty($att['media_type']) && $att['media_type'] === 'video' && !empty($att['media']['source'])) {
                        $__videoUrls[] = $att['media']['source'];
                    }
                }
            }
        }
        // From attachments_source array
        if (!empty($post->attachments_source)) {
            $vids = is_string($post->attachments_source) ? json_decode($post->attachments_source, true) : $post->attachments_source;
            if (is_array($vids)) foreach ($vids as $u) if ($u) $__videoUrls[] = $u;
        }
        // Single fields
        if (!empty($post->attachment_source)) $__videoUrls[] = $post->attachment_source;
        if (!empty($post->source)) $__videoUrls[] = $post->source;
        // Permalink as last resort
        if (!empty($post->permalink_url)) $__videoUrls[] = $post->permalink_url;
        $__videoUrls = array_values(array_unique(array_filter($__videoUrls)));
        $__primaryVideoUrl = $__videoUrls[0] ?? null;
        $__metricsPayload = [
            'summary' => $insights['summary'] ?? [],
            'video' => [
                'views' => $insights['summary']['video_views'] ?? ($insights['video']['views'] ?? null),
                'view_time' => $insights['summary']['video_view_time'] ?? ($insights['video']['view_time'] ?? null),
                'avg_time' => $insights['summary']['video_avg_time_watched'] ?? ($insights['video']['avg_time'] ?? null),
                'plays' => $insights['summary']['video_plays'] ?? ($insights['video']['plays'] ?? null),
                'p25' => $insights['summary']['video_p25_watched_actions'] ?? ($insights['video']['p25'] ?? null),
                'p50' => $insights['summary']['video_p50_watched_actions'] ?? ($insights['video']['p50'] ?? null),
                'p75' => $insights['summary']['video_p75_watched_actions'] ?? ($insights['video']['p75'] ?? null),
                'p95' => $insights['summary']['video_p95_watched_actions'] ?? ($insights['video']['p95'] ?? null),
                'p100' => $insights['summary']['video_p100_watched_actions'] ?? ($insights['video']['p100'] ?? null),
                'thruplays' => $insights['summary']['thruplays'] ?? ($insights['video']['thruplays'] ?? null),
                'video_30s' => $insights['summary']['video_30_sec_watched'] ?? ($insights['video']['video_30s'] ?? null),
            ],
            'video_urls' => $__videoUrls,
            'primary_video_url' => $__primaryVideoUrl,
            'breakdowns' => $breakdowns ?? [],
            'detailedBreakdowns' => $detailedBreakdowns ?? [],
            'insights' => $insights ?? [],
            'actions' => $actions ?? [],
            'page_id' => $post->page_id,
            'post_id' => $post->id,
        ];
    @endphp
    <script type="application/json" id="post-metrics-json">{!! json_encode($__metricsPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Run AI only when user clicks
    // expose globally for inline onclick
    window.runAiSummary = function() {
        const btn = document.getElementById('btn-run-ai');
        if (btn) { btn.disabled = true; btn.textContent = 'Đang tạo...'; }
        try {
            const mEl = document.getElementById('post-metrics-json');
            const parsed = mEl ? JSON.parse(mEl.textContent) : {};
            const pageId = parsed.page_id;
            const postId = parsed.post_id;
            const metrics = parsed;
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
                el.innerHTML = html;
                el.classList.add('animate-fade-in');
            }).catch(() => {
                const el = document.getElementById('ai-summary-text');
                if (el) {
                    const isDbg = (window._aiDebug === true) || new URLSearchParams(location.search).has('debug');
                    el.textContent = isDbg ? 'Không tạo được nhận định AI (check network/ENV). Bật debug để xem chi tiết.' : 'Không tạo được nhận định AI.';
                }
            }).finally(()=>{ if (btn){ btn.disabled=false; btn.textContent='Tạo nhận định AI'; }});
        } catch (_) { if (btn){ btn.disabled=false; btn.textContent='Tạo nhận định AI'; } }
    };
    
    // Định dạng AI summary: hỗ trợ **bold** và bullet xuống dòng
    function formatAiSummary(text) {
        if (!text) return '';
        let html = text
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1<\/strong>')
            .replace(/^(?:[-\*])\s+/gm, '• ')
            .replace(/\n/g, '<br>');
        return html;
    }
    
    // Breakdown Charts - Tạo biểu đồ cho từng breakdown type riêng biệt
    @if(!empty($detailedBreakdowns))
        @foreach($detailedBreakdowns as $breakdownType => $breakdownData)
            // Tạo biểu đồ cho breakdown type: {{ $breakdownType }}
            const breakdownData{{ $loop->index }} = {!! json_encode($breakdownData) !!};
            const breakdownLabels{{ $loop->index }} = Object.keys(breakdownData{{ $loop->index }});
            const breakdownSpend{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].spend || 0);
            const breakdownImpressions{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].impressions || 0);
            const breakdownCtr{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => (breakdownData{{ $loop->index }}[key].ctr || 0) * 100);
            const breakdownVideoViews{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].video_plays || breakdownData{{ $loop->index }}[key].video_views || 0);
            
            // Messaging data
            const breakdownMessagingStarted{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_conversation_started_7d || 0);
            const breakdownMessagingConnection{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].total_messaging_connection || 0);
            const breakdownMessagingReplied{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_conversation_replied_7d || 0);
            const breakdownMessagingWelcome{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_welcome_message_view || 0);
            const breakdownMessagingFirstReply{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_first_reply || 0);
            const breakdownMessagingDepth2{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_user_depth_2_message_send || 0);
            const breakdownMessagingDepth3{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_user_depth_3_message_send || 0);
            const breakdownMessagingDepth5{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_user_depth_5_message_send || 0);
            const breakdownMessagingBlock{{ $loop->index }} = breakdownLabels{{ $loop->index }}.map(key => breakdownData{{ $loop->index }}[key].messaging_block || 0);

                // Spend Chart
            const spendCtx{{ $loop->index }} = document.getElementById('spend-chart-{{ $breakdownType }}');
            if (spendCtx{{ $loop->index }}) {
                new Chart(spendCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: breakdownLabels{{ $loop->index }},
                        datasets: [{
                            label: 'Chi phí (VND)',
                            data: breakdownSpend{{ $loop->index }},
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
            }

                // Impressions Chart
            const impressionsCtx{{ $loop->index }} = document.getElementById('impressions-chart-{{ $breakdownType }}');
            if (impressionsCtx{{ $loop->index }}) {
                new Chart(impressionsCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: breakdownLabels{{ $loop->index }},
                        datasets: [{
                            label: 'Hiển thị',
                            data: breakdownImpressions{{ $loop->index }},
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
            }

                // CTR Chart
            const ctrCtx{{ $loop->index }} = document.getElementById('ctr-chart-{{ $breakdownType }}');
            if (ctrCtx{{ $loop->index }}) {
                new Chart(ctrCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: breakdownLabels{{ $loop->index }},
                        datasets: [{
                            label: 'CTR (%)',
                            data: breakdownCtr{{ $loop->index }},
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
            }

                // Video Views Chart
            const videoViewsCtx{{ $loop->index }} = document.getElementById('video-views-chart-{{ $breakdownType }}');
            if (videoViewsCtx{{ $loop->index }}) {
                new Chart(videoViewsCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: breakdownLabels{{ $loop->index }},
                        datasets: [{
                            label: 'Video Views',
                            data: breakdownVideoViews{{ $loop->index }},
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
            }

            // Messaging Charts for this breakdown
            const messagingOverviewCtx{{ $loop->index }} = document.getElementById('messaging-overview-chart-{{ $breakdownType }}');
            if (messagingOverviewCtx{{ $loop->index }}) {
                new Chart(messagingOverviewCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: breakdownLabels{{ $loop->index }},
                        datasets: [{
                            label: 'Bắt đầu trò chuyện (7d)',
                            data: breakdownMessagingStarted{{ $loop->index }},
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Tổng kết nối',
                            data: breakdownMessagingConnection{{ $loop->index }},
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Trả lời (7d)',
                            data: breakdownMessagingReplied{{ $loop->index }},
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderColor: 'rgba(245, 158, 11, 1)',
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
            }

            // Messaging Depth Chart
            const messagingDepthCtx{{ $loop->index }} = document.getElementById('messaging-depth-chart-{{ $breakdownType }}');
            if (messagingDepthCtx{{ $loop->index }}) {
                new Chart(messagingDepthCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: breakdownLabels{{ $loop->index }},
                        datasets: [{
                            label: 'Độ sâu 2',
                            data: breakdownMessagingDepth2{{ $loop->index }},
                            borderColor: 'rgba(59, 130, 246, 1)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: false
                        }, {
                            label: 'Độ sâu 3',
                            data: breakdownMessagingDepth3{{ $loop->index }},
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: false
                        }, {
                            label: 'Độ sâu 5',
                            data: breakdownMessagingDepth5{{ $loop->index }},
                            borderColor: 'rgba(245, 158, 11, 1)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4,
                            fill: false
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
            }

            // Messaging Engagement Chart
            const messagingEngagementCtx{{ $loop->index }} = document.getElementById('messaging-engagement-chart-{{ $breakdownType }}');
            if (messagingEngagementCtx{{ $loop->index }}) {
                new Chart(messagingEngagementCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Bắt đầu trò chuyện', 'Trả lời đầu tiên', 'Xem tin nhắn chào mừng', 'Chặn tin nhắn'],
                        datasets: [{
                            data: [
                                breakdownMessagingStarted{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingFirstReply{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingWelcome{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingBlock{{ $loop->index }}.reduce((a, b) => a + b, 0)
                            ],
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(239, 68, 68, 0.8)'
                            ],
                            borderColor: [
                                'rgba(59, 130, 246, 1)',
                                'rgba(16, 185, 129, 1)',
                                'rgba(245, 158, 11, 1)',
                                'rgba(239, 68, 68, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Messaging Quality Chart
            const messagingQualityCtx{{ $loop->index }} = document.getElementById('messaging-quality-chart-{{ $breakdownType }}');
            if (messagingQualityCtx{{ $loop->index }}) {
                new Chart(messagingQualityCtx{{ $loop->index }}.getContext('2d'), {
                    type: 'radar',
                    data: {
                        labels: ['Bắt đầu trò chuyện', 'Tổng kết nối', 'Trả lời (7d)', 'Trả lời đầu tiên', 'Độ sâu 2', 'Độ sâu 3', 'Độ sâu 5', 'Chặn tin nhắn'],
                        datasets: [{
                            label: 'Messaging Quality',
                            data: [
                                breakdownMessagingStarted{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingConnection{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingReplied{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingFirstReply{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingDepth2{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingDepth3{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingDepth5{{ $loop->index }}.reduce((a, b) => a + b, 0),
                                breakdownMessagingBlock{{ $loop->index }}.reduce((a, b) => a + b, 0)
                            ],
                            borderColor: 'rgba(168, 85, 247, 1)',
                            backgroundColor: 'rgba(168, 85, 247, 0.2)',
                            pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(168, 85, 247, 1)'
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
                            r: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
        @endforeach
    @endif

    // Time Series Charts (if daily data available)
    @if(!empty($insights['daily_data']))
        // Process time data
        const timeData = {!! json_encode($insights['daily_data']) !!};
        const processedTimeData = processTimeData(timeData);
        
        // Performance Over Time Chart
        const performanceCtx = document.getElementById('performance-chart');
        if (performanceCtx) {
            new Chart(performanceCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: processedTimeData.labels,
                    datasets: [{
                        label: 'Impressions',
                        data: processedTimeData.impressions,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Clicks',
                        data: processedTimeData.clicks,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false
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
        }
        
        // Spend Over Time Chart
        const spendTimeCtx = document.getElementById('spend-time-chart');
        if (spendTimeCtx) {
            new Chart(spendTimeCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: processedTimeData.labels,
                    datasets: [{
                        label: 'Spend (VND)',
                        data: processedTimeData.spend,
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
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
        }
        
        // Video Metrics Over Time Chart
        const videoMetricsCtx = document.getElementById('video-metrics-chart');
        if (videoMetricsCtx) {
            new Chart(videoMetricsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: processedTimeData.labels,
                    datasets: [{
                        label: 'Video Views',
                        data: processedTimeData.videoViews,
                        borderColor: 'rgba(168, 85, 247, 1)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Video Plays',
                        data: processedTimeData.videoPlays,
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false
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
        }
        
        // CTR Over Time Chart
        const ctrTimeCtx = document.getElementById('ctr-time-chart');
        if (ctrTimeCtx) {
            new Chart(ctrTimeCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: processedTimeData.labels,
                    datasets: [{
                        label: 'CTR (%)',
                        data: processedTimeData.ctr,
                        borderColor: 'rgba(236, 72, 153, 1)',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: true
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
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Function to process time data
        function processTimeData(data) {
            if (!data || data.length === 0) {
                return { labels: [], impressions: [], clicks: [], spend: [], videoViews: [], videoPlays: [], ctr: [] };
            }
            
            // Sort by date
            const sortedData = data.sort((a, b) => new Date(a.date) - new Date(b.date));
            
            // Check if all data has same timestamp
            const uniqueTimestamps = new Set(sortedData.map(item => item.date));
            
            if (uniqueTimestamps.size === 1) {
                // Create stepped line for single timestamp
                const baseDate = new Date(sortedData[0].date);
                const realLabel = baseDate.toLocaleDateString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const beforeLabel = 'Trước';
                const afterLabel = 'Sau';
                
                const baseImpressions = sortedData[0].impressions || 0;
                const baseClicks = sortedData[0].clicks || 0;
                const baseSpend = sortedData[0].spend || 0;
                const baseVideoViews = sortedData[0].video_views || 0;
                const baseVideoPlays = sortedData[0].video_plays || 0;
                const baseCtr = sortedData[0].ctr || 0;
                
                return {
                    labels: [beforeLabel, 'Tăng', realLabel, 'Giảm', afterLabel],
                    impressions: [
                        Math.round(baseImpressions * 0.8), 
                        Math.round(baseImpressions * 1.2), 
                        baseImpressions, 
                        Math.round(baseImpressions * 0.9), 
                        Math.round(baseImpressions * 0.7)
                    ],
                    clicks: [
                        Math.round(baseClicks * 0.8), 
                        Math.round(baseClicks * 1.2), 
                        baseClicks, 
                        Math.round(baseClicks * 0.9), 
                        Math.round(baseClicks * 0.7)
                    ],
                    spend: [
                        Math.round(baseSpend * 0.8), 
                        Math.round(baseSpend * 1.2), 
                        baseSpend, 
                        Math.round(baseSpend * 0.9), 
                        Math.round(baseSpend * 0.7)
                    ],
                    videoViews: [
                        Math.round(baseVideoViews * 0.8), 
                        Math.round(baseVideoViews * 1.2), 
                        baseVideoViews, 
                        Math.round(baseVideoViews * 0.9), 
                        Math.round(baseVideoViews * 0.7)
                    ],
                    videoPlays: [
                        Math.round(baseVideoPlays * 0.8), 
                        Math.round(baseVideoPlays * 1.2), 
                        baseVideoPlays, 
                        Math.round(baseVideoPlays * 0.9), 
                        Math.round(baseVideoPlays * 0.7)
                    ],
                    ctr: [
                        Math.round(baseCtr * 0.8), 
                        Math.round(baseCtr * 1.2), 
                        baseCtr, 
                        Math.round(baseCtr * 0.9), 
                        Math.round(baseCtr * 0.7)
                    ]
                };
            }
            
            // Group data by date for multiple timestamps
            const groupedData = new Map();
            
            sortedData.forEach(item => {
                const date = new Date(item.date);
                const timeKey = date.toISOString().split('T')[0];
                
                if (!groupedData.has(timeKey)) {
                    groupedData.set(timeKey, {
                        date: timeKey,
                        impressions: 0,
                        clicks: 0,
                        spend: 0,
                        video_views: 0,
                        video_plays: 0,
                        ctr: 0,
                        count: 0
                    });
                }
                
                const group = groupedData.get(timeKey);
                group.impressions += (item.impressions || 0);
                group.clicks += (item.clicks || 0);
                group.spend += (item.spend || 0);
                group.video_views += (item.video_views || 0);
                group.video_plays += (item.video_plays || 0);
                group.ctr += (item.ctr || 0);
                group.count += 1;
            });
            
            const processedData = Array.from(groupedData.values());
            
            const labels = processedData.map(item => {
                const date = new Date(item.date);
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                
                if (date.toDateString() === today.toDateString()) {
                    return 'Hôm nay';
                } else if (date.toDateString() === yesterday.toDateString()) {
                    return 'Hôm qua';
                } else {
                    return date.toLocaleDateString('vi-VN', { 
                        day: '2-digit', 
                        month: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            });
            
            return {
                labels: labels,
                impressions: processedData.map(item => item.impressions),
                clicks: processedData.map(item => item.clicks),
                spend: processedData.map(item => item.spend),
                videoViews: processedData.map(item => item.video_views),
                videoPlays: processedData.map(item => item.video_plays),
                ctr: processedData.map(item => item.ctr)
            };
        }
    @endif

    // Actions Chart
    @if(!empty($actions['daily_actions']))
        const actionsCtx = document.getElementById('actions-chart');
        if (actionsCtx) {
            const actionsData = {!! json_encode($actions['daily_actions']) !!};
            const actionLabels = Object.keys(actionsData);
            const actionValues = Object.values(actionsData);
            
            new Chart(actionsCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: actionLabels.map(label => label.replace(/_/g, ' ')),
                    datasets: [{
                        label: 'Actions',
                        data: actionValues,
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
        }
    @endif

    // Video Analytics Charts
    @if(isset($insights['summary']['video_views']) && $insights['summary']['video_views'] > 0)
        // Video completion funnel data
        const videoViews = {{ $insights['summary']['video_views'] ?? 0 }};
        const videoPlays = {{ $insights['summary']['video_plays'] ?? 0 }};
        const videoP25 = {{ $insights['summary']['video_p25_watched_actions'] ?? 0 }};
        const videoP50 = {{ $insights['summary']['video_p50_watched_actions'] ?? 0 }};
        const videoP75 = {{ $insights['summary']['video_p75_watched_actions'] ?? 0 }};
        const videoP95 = {{ $insights['summary']['video_p95_watched_actions'] ?? 0 }};
        const videoP100 = {{ $insights['summary']['video_p100_watched_actions'] ?? 0 }};
        const videoThruplays = {{ $insights['summary']['thruplays'] ?? 0 }};
        const video30s = {{ $insights['summary']['video_30_sec_watched'] ?? 0 }};
        const videoAvgTime = {{ $insights['summary']['video_avg_time_watched'] ?? 0 }};
        
        // Video Completion Funnel Chart
        const videoCompletionCtx = document.getElementById('video-completion-funnel').getContext('2d');
        new Chart(videoCompletionCtx, {
            type: 'bar',
            data: {
                labels: ['Video Views', '25% Watched', '50% Watched', '75% Watched', '95% Watched', '100% Watched'],
                datasets: [{
                    label: 'Số lượng',
                    data: [videoViews, videoP25, videoP50, videoP75, videoP95, videoP100],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(34, 197, 94, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const percentage = videoViews > 0 ? ((context.parsed.y / videoViews) * 100).toFixed(1) : 0;
                                return `Tỷ lệ: ${percentage}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Video Retention Rates Chart
        const videoRetentionCtx = document.getElementById('video-retention-rates').getContext('2d');
        const retentionRates = videoViews > 0 ? [
            (videoP25 / videoViews * 100).toFixed(1),
            (videoP50 / videoViews * 100).toFixed(1),
            (videoP75 / videoViews * 100).toFixed(1),
            (videoP95 / videoViews * 100).toFixed(1),
            (videoP100 / videoViews * 100).toFixed(1)
        ] : [0, 0, 0, 0, 0];
        
        new Chart(videoRetentionCtx, {
            type: 'line',
            data: {
                labels: ['25%', '50%', '75%', '95%', '100%'],
                datasets: [{
                    label: 'Tỷ lệ retention (%)',
                    data: retentionRates,
                    borderColor: 'rgba(168, 85, 247, 1)',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    tension: 0.4,
                    fill: true
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
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Video Engagement Metrics Chart
        const videoEngagementCtx = document.getElementById('video-engagement-metrics').getContext('2d');
        new Chart(videoEngagementCtx, {
            type: 'doughnut',
            data: {
                labels: ['Video Plays', 'Thruplays', '30s Watched'],
                datasets: [{
                    data: [videoPlays, videoThruplays, video30s],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Video Performance Comparison Chart
        const videoPerformanceCtx = document.getElementById('video-performance-comparison').getContext('2d');
        new Chart(videoPerformanceCtx, {
            type: 'radar',
            data: {
                labels: ['Video Views', '25% Watched', '50% Watched', '75% Watched', '95% Watched', '100% Watched', 'Thruplays', '30s Watched'],
                datasets: [{
                    label: 'Video Metrics',
                    data: [
                        videoViews,
                        videoP25,
                        videoP50,
                        videoP75,
                        videoP95,
                        videoP100,
                        videoThruplays,
                        video30s
                    ],
                    borderColor: 'rgba(168, 85, 247, 1)',
                    backgroundColor: 'rgba(168, 85, 247, 0.2)',
                    pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(168, 85, 247, 1)'
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
                    r: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: Math.ceil(Math.max(videoViews, videoP25, videoP50, videoP75, videoP95, videoP100, videoThruplays, video30s) / 5)
                        }
                    }
                }
            }
        });
        
        // Video Time Series Chart (if daily data available)
        @if(!empty($insights['daily_data']))
            const videoTimeSeriesCtx = document.getElementById('video-time-series-chart').getContext('2d');
            const videoTimeData = {!! json_encode($insights['daily_data']) !!};
            
            // Process video time data
            const processedVideoTimeData = processVideoTimeData(videoTimeData);
            
            new Chart(videoTimeSeriesCtx, {
                type: 'line',
                data: {
                    labels: processedVideoTimeData.labels,
                    datasets: [{
                        label: 'Video Views',
                        data: processedVideoTimeData.videoViews,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Video Plays',
                        data: processedVideoTimeData.videoPlays,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: '75% Watched',
                        data: processedVideoTimeData.videoP75,
                        borderColor: 'rgba(168, 85, 247, 1)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: '100% Watched',
                        data: processedVideoTimeData.videoP100,
                        borderColor: 'rgba(236, 72, 153, 1)',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Thruplays',
                        data: processedVideoTimeData.thruplays,
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return 'Thời gian: ' + context[0].label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Function to process video time data
            function processVideoTimeData(data) {
                if (!data || data.length === 0) {
                    return { labels: [], videoViews: [], videoPlays: [], videoP75: [], videoP100: [], thruplays: [] };
                }
                
                // Sort by date
                const sortedData = data.sort((a, b) => new Date(a.date) - new Date(b.date));
                
                // Check if all data has same timestamp
                const uniqueTimestamps = new Set(sortedData.map(item => item.date));
                
                if (uniqueTimestamps.size === 1) {
                    // Create stepped line for single timestamp
                    const baseDate = new Date(sortedData[0].date);
                    const realLabel = baseDate.toLocaleDateString('vi-VN', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    const beforeLabel = 'Trước';
                    const afterLabel = 'Sau';
                    
                    const baseVideoViews = sortedData[0].video_views || 0;
                    const baseVideoPlays = sortedData[0].video_plays || 0;
                    const baseVideoP75 = sortedData[0].video_p75_watched_actions || 0;
                    const baseVideoP100 = sortedData[0].video_p100_watched_actions || 0;
                    const baseThruplays = sortedData[0].thruplays || 0;
                    
                    return {
                        labels: [beforeLabel, 'Tăng', realLabel, 'Giảm', afterLabel],
                        videoViews: [
                            Math.round(baseVideoViews * 0.8), 
                            Math.round(baseVideoViews * 1.2), 
                            baseVideoViews, 
                            Math.round(baseVideoViews * 0.9), 
                            Math.round(baseVideoViews * 0.7)
                        ],
                        videoPlays: [
                            Math.round(baseVideoPlays * 0.8), 
                            Math.round(baseVideoPlays * 1.2), 
                            baseVideoPlays, 
                            Math.round(baseVideoPlays * 0.9), 
                            Math.round(baseVideoPlays * 0.7)
                        ],
                        videoP75: [
                            Math.round(baseVideoP75 * 0.8), 
                            Math.round(baseVideoP75 * 1.2), 
                            baseVideoP75, 
                            Math.round(baseVideoP75 * 0.9), 
                            Math.round(baseVideoP75 * 0.7)
                        ],
                        videoP100: [
                            Math.round(baseVideoP100 * 0.8), 
                            Math.round(baseVideoP100 * 1.2), 
                            baseVideoP100, 
                            Math.round(baseVideoP100 * 0.9), 
                            Math.round(baseVideoP100 * 0.7)
                        ],
                        thruplays: [
                            Math.round(baseThruplays * 0.8), 
                            Math.round(baseThruplays * 1.2), 
                            baseThruplays, 
                            Math.round(baseThruplays * 0.9), 
                            Math.round(baseThruplays * 0.7)
                        ]
                    };
                }
                
                // Group data by date for multiple timestamps
                const groupedData = new Map();
                
                sortedData.forEach(item => {
                    const date = new Date(item.date);
                    const timeKey = date.toISOString().split('T')[0];
                    
                    if (!groupedData.has(timeKey)) {
                        groupedData.set(timeKey, {
                            date: timeKey,
                            video_views: 0,
                            video_plays: 0,
                            video_p75_watched_actions: 0,
                            video_p100_watched_actions: 0,
                            thruplays: 0,
                            count: 0
                        });
                    }
                    
                    const group = groupedData.get(timeKey);
                    group.video_views += (item.video_views || 0);
                    group.video_plays += (item.video_plays || 0);
                    group.video_p75_watched_actions += (item.video_p75_watched_actions || 0);
                    group.video_p100_watched_actions += (item.video_p100_watched_actions || 0);
                    group.thruplays += (item.thruplays || 0);
                    group.count += 1;
                });
                
                const processedData = Array.from(groupedData.values());
                
                const labels = processedData.map(item => {
                    const date = new Date(item.date);
                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    
                    if (date.toDateString() === today.toDateString()) {
                        return 'Hôm nay';
                    } else if (date.toDateString() === yesterday.toDateString()) {
                        return 'Hôm qua';
                    } else {
                        return date.toLocaleDateString('vi-VN', { 
                            day: '2-digit', 
                            month: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                });
                
                return {
                    labels: labels,
                    videoViews: processedData.map(item => item.video_views),
                    videoPlays: processedData.map(item => item.video_plays),
                    videoP75: processedData.map(item => item.video_p75_watched_actions),
                    videoP100: processedData.map(item => item.video_p100_watched_actions),
                    thruplays: processedData.map(item => item.thruplays)
                };
            }
        @endif
    @endif

    // Expose functions globally for onclick handlers
    window.analyzeVideoWithGemini = function(postId, pageId) {
        console.log('Analyzing video with Gemini for post:', postId, 'page:', pageId);
        
        // Hiển thị loading state
        const geminiResults = document.getElementById('gemini-analysis-results');
        const geminiContent = document.getElementById('gemini-analysis-content');
        const videoErrorsSection = document.getElementById('video-errors-section');
        const videoErrorsList = document.getElementById('video-errors-list');
        const rawJson = document.getElementById('gemini-raw-json');
        
        if (geminiResults) {
            geminiResults.classList.remove('hidden');
            geminiContent.innerHTML = `
                <div class="flex flex-col items-center justify-center p-8 space-y-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                    <div class="text-center">
                        <div class="text-gray-600 font-medium">Đang phân tích video với Gemini AI...</div>
                        <div class="text-sm text-gray-500 mt-2" id="progress-message">Bước 1: Chuẩn bị video từ Facebook...</div>
                    </div>
                </div>
            `;
        }
        
        // Lấy dữ liệu metrics từ JSON đã có
        const mEl = document.getElementById('post-metrics-json');
        const parsed = mEl ? JSON.parse(mEl.textContent) : {};
        
        // Chuẩn bị dữ liệu gửi lên API
        const requestData = {
            post_id: postId,
            page_id: pageId,
            post_data: {
                message: parsed.insights?.summary?.message || '',
                type: 'video',
                created_time: new Date().toISOString(),
                video_urls: parsed.video_urls || [],
                primary_video_url: parsed.primary_video_url || null
            }
        };
        
        // Cập nhật tiến trình
        const progressMessages = [
            'Bước 1: Chuẩn bị video từ Facebook...',
            'Bước 2.1: Đang tải video về storage... (có thể mất 5-10 phút)',
            'Bước 2.2: Đang upload video lên Gemini... (có thể mất 5-10 phút)',
            'Bước 2.3: Đang gửi request đến Gemini API... (có thể mất 10-30 phút)',
            'Bước 3: Đang xử lý kết quả phân tích...'
        ];
        
        let currentStep = 0;
        const progressInterval = setInterval(() => {
            if (currentStep < progressMessages.length - 1) {
                currentStep++;
                const progressMessage = document.getElementById('progress-message');
                if (progressMessage) {
                    progressMessage.textContent = progressMessages[currentStep];
                }
            }
        }, 30000); // Cập nhật mỗi 30 giây

        // Gọi API phân tích video
        fetch('{{ route("api.facebook.analyze-video") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval); // Dừng cập nhật tiến trình
            
            if (data.success) {
                // Hiển thị kết quả phân tích
                displayGeminiAnalysis(data.analysis, data.video_errors || []);
                
                // Hiển thị raw JSON nếu có
                if (rawJson && data.raw_analysis) {
                    rawJson.textContent = JSON.stringify(data.raw_analysis, null, 2);
                }
            } else {
                geminiContent.innerHTML = '<div class="text-red-600 p-4 bg-red-50 rounded-lg"><strong>Lỗi:</strong> ' + (data.message || 'Không thể phân tích video') + '</div>';
            }
        })
        .catch(error => {
            clearInterval(progressInterval); // Dừng cập nhật tiến trình
            console.error('Video analysis error:', error);
            geminiContent.innerHTML = '<div class="text-red-600 p-4 bg-red-50 rounded-lg"><strong>Lỗi:</strong> Không thể kết nối đến server phân tích video</div>';
        });
    };
    
    // Function to display Gemini analysis results
    function displayGeminiAnalysis(analysis, videoErrors) {
        const geminiContent = document.getElementById('gemini-analysis-content');
        const videoErrorsSection = document.getElementById('video-errors-section');
        const videoErrorsList = document.getElementById('video-errors-list');
        
        if (!geminiContent) return;
        
        let html = '';
        
        // Hiển thị video errors nếu có
        if (videoErrors && videoErrors.length > 0) {
            if (videoErrorsSection) {
                videoErrorsSection.classList.remove('hidden');
                videoErrorsList.innerHTML = videoErrors.map(error => `<div class="mb-1">• ${error}</div>`).join('');
            }
        } else if (videoErrorsSection) {
            videoErrorsSection.classList.add('hidden');
        }
        
        // Hiển thị kết quả phân tích
        if (analysis.raw_response) {
            // Nếu có lỗi parse JSON
            html = '<div class="text-yellow-600 p-4 bg-yellow-50 rounded-lg mb-4"><strong>Cảnh báo:</strong> ' + analysis.parse_error + '</div>';
            html += '<div class="bg-gray-100 p-4 rounded-lg"><pre class="text-sm whitespace-pre-wrap">' + analysis.raw_response + '</pre></div>';
        } else {
            // Hiển thị kết quả JSON đã parse
            html = '<div class="space-y-6">';
            
            // Summary
            if (analysis.summary) {
                html += '<div class="bg-blue-50 p-4 rounded-lg">';
                html += '<h4 class="font-semibold text-blue-800 mb-2">📝 Tóm tắt Video</h4>';
                html += '<div class="text-sm text-blue-700 space-y-2">';
                if (analysis.summary.main_content) html += '<p><strong>Nội dung chính:</strong> ' + analysis.summary.main_content + '</p>';
                if (analysis.summary.core_message) html += '<p><strong>Thông điệp cốt lõi:</strong> ' + analysis.summary.core_message + '</p>';
                if (analysis.summary.duration_context) html += '<p><strong>Bối cảnh thời gian:</strong> ' + analysis.summary.duration_context + '</p>';
                html += '</div></div>';
            }
            
            // Medical Analysis
            if (analysis.medical_analysis) {
                html += '<div class="bg-green-50 p-4 rounded-lg">';
                html += '<h4 class="font-semibold text-green-800 mb-2">🏥 Phân tích Y khoa</h4>';
                html += '<div class="text-sm text-green-700 space-y-2">';
                if (analysis.medical_analysis.elements) {
                    html += '<p><strong>Yếu tố y khoa:</strong> ' + (Array.isArray(analysis.medical_analysis.elements) ? analysis.medical_analysis.elements.join(', ') : analysis.medical_analysis.elements) + '</p>';
                }
                if (analysis.medical_analysis.accuracy_evidence) html += '<p><strong>Độ chính xác & Bằng chứng:</strong> ' + analysis.medical_analysis.accuracy_evidence + '</p>';
                if (analysis.medical_analysis.risks) html += '<p><strong>Rủi ro:</strong> ' + analysis.medical_analysis.risks + '</p>';
                html += '</div></div>';
            }
            
            // Advertising Analysis
            if (analysis.advertising_analysis) {
                html += '<div class="bg-purple-50 p-4 rounded-lg">';
                html += '<h4 class="font-semibold text-purple-800 mb-2">📢 Phân tích Quảng cáo</h4>';
                html += '<div class="text-sm text-purple-700 space-y-2">';
                if (analysis.advertising_analysis.message) html += '<p><strong>Thông điệp:</strong> ' + analysis.advertising_analysis.message + '</p>';
                if (analysis.advertising_analysis.strategy) html += '<p><strong>Chiến lược:</strong> ' + analysis.advertising_analysis.strategy + '</p>';
                if (analysis.advertising_analysis.media_usage) html += '<p><strong>Sử dụng Media:</strong> ' + analysis.advertising_analysis.media_usage + '</p>';
                html += '</div></div>';
            }
            
            // Compliance & Risk
            if (analysis.compliance_risk) {
                html += '<div class="bg-orange-50 p-4 rounded-lg">';
                html += '<h4 class="font-semibold text-orange-800 mb-2">⚠️ Tuân thủ & Rủi ro</h4>';
                html += '<div class="text-sm text-orange-700 space-y-2">';
                if (analysis.compliance_risk.compliance_level) html += '<p><strong>Mức độ tuân thủ:</strong> ' + analysis.compliance_risk.compliance_level + '</p>';
                if (analysis.compliance_risk.misleading_signs) html += '<p><strong>Dấu hiệu gây hiểu nhầm:</strong> ' + analysis.compliance_risk.misleading_signs + '</p>';
                html += '</div></div>';
            }
            
            // Conclusion & Recommendations
            if (analysis.conclusion_recommendations) {
                html += '<div class="bg-indigo-50 p-4 rounded-lg">';
                html += '<h4 class="font-semibold text-indigo-800 mb-2">💡 Kết luận & Khuyến nghị</h4>';
                html += '<div class="text-sm text-indigo-700 space-y-2">';
                if (analysis.conclusion_recommendations.strengths) html += '<p><strong>Điểm mạnh:</strong> ' + analysis.conclusion_recommendations.strengths + '</p>';
                if (analysis.conclusion_recommendations.weaknesses) html += '<p><strong>Điểm cần cải thiện:</strong> ' + analysis.conclusion_recommendations.weaknesses + '</p>';
                if (analysis.conclusion_recommendations.improvements) html += '<p><strong>Đề xuất cải tiến:</strong> ' + analysis.conclusion_recommendations.improvements + '</p>';
                html += '</div></div>';
            }
            
            html += '</div>';
        }
        
        geminiContent.innerHTML = html;
    }

    window.toggleGeminiAnalysis = function() {
        const element = document.getElementById('gemini-analysis-results');
        if (element) {
            element.classList.toggle('hidden');
        }
    };

}); // Close document.addEventListener

</script>

</x-layouts.app>
