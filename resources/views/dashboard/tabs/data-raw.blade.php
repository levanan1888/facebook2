<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Data Raw (Facebook Ads API)</h2>
        <div class="flex items-center gap-3">
            @can('facebook.sync')
                <div class="flex items-center gap-2">
                    <button id="btnSyncFacebook" type="button" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Đồng bộ Ads
                    </button>

                    <button id="btnStopSync" type="button" class="px-3 py-1.5 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Dừng đồng bộ
                    </button>

                    <button id="btnResetSync" type="button" class="px-3 py-1.5 rounded bg-gray-600 text-white text-sm hover:bg-gray-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </button>
                </div>
            @else
                <div class="text-sm text-red-600">
                    ❌ Không có quyền đồng bộ Facebook (facebook.sync)
                </div>
            @endcan
            @if(empty($data['data']))
                <span class="text-sm text-gray-500">Chưa có dữ liệu. Cấu hình FACEBOOK_ADS_TOKEN và nhấn Đồng bộ ngay.</span>
            @endif
        </div>
    </div>
    <div class="p-4">
        <div id="syncProgress" class="mb-4 hidden">
            <div class="mt-3 text-sm text-gray-700">
                <span class="font-medium">Giai đoạn: </span><span id="syncStage" class="text-blue-600 font-semibold">-</span>
            </div>
            <div id="syncCounts" class="mt-3 flex flex-wrap gap-3 text-xs">
                <!-- Progress counts sẽ được render động -->
            </div>
            <div id="syncErrors" class="mt-3 text-xs text-red-600"></div>
        </div>
        
        <!-- Hiển thị lỗi Facebook -->
        <div id="facebookErrors" class="mb-4 hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-sm font-medium text-red-800">Lỗi Facebook API</h3>
                </div>
                <div id="facebookErrorsList" class="text-xs text-red-700 space-y-1">
                    <!-- Lỗi sẽ được render động -->
                </div>
            </div>
        </div>
        
        <!-- Debug Panel để xem thông tin lỗi chi tiết -->
        <div id="debugPanel" class="mb-4 hidden">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-yellow-800">🔍 Debug Panel - Thông tin lỗi chi tiết</h3>
                    <button onclick="toggleDebugPanel()" class="text-xs text-yellow-600 hover:text-yellow-800">
                        Ẩn/Hiện
                    </button>
                </div>
                <div id="debugContent" class="text-xs text-yellow-700 space-y-2">
                    <!-- Debug info sẽ được render động -->
                </div>
            </div>
        </div>
        @if(session('success'))
            <div class="mb-3 px-3 py-2 rounded bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-3 px-3 py-2 rounded bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
        @endif
        
        <!-- Hàng 1: Tiếng Anh - Data đang đồng bộ -->
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Dữ liệu đang đồng bộ (tiếng Anh):</h3>
            <div class="flex flex-wrap gap-3">
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Business Managers</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['businesses'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Ad Accounts</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['accounts'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Campaigns</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['campaigns'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Ad Sets</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['adsets'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Ads</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['ads'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Hàng 2: Tiếng Việt - Data đã có sẵn -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Dữ liệu đã có sẵn trong hệ thống (tiếng Việt):</h3>
            <div class="flex flex-wrap gap-3">
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Quản lý doanh nghiệp</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['businesses'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Tài khoản quảng cáo</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['accounts'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Chiến dịch</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['campaigns'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Bộ quảng cáo</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['adsets'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200 flex-1 min-w-[120px]">
                    <div class="text-xs text-gray-500">Quảng cáo</div>
                    <div class="text-xl font-semibold">{{ number_format($data['totals']['ads'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div>
                <div class="text-sm font-semibold text-gray-800 mb-2">Business Managers</div>
                <div class="overflow-auto rounded border border-gray-200">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-600"><tr><th class="px-3 py-2 text-left">ID</th><th class="px-3 py-2 text-left">Name</th><th class="px-3 py-2 text-left">Verification</th><th class="px-3 py-2 text-left">Created</th><th class="px-3 py-2 text-left">#Accounts</th></tr></thead>
                        <tbody>
                            @foreach(($data['data']['businesses'] ?? []) as $b)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $b->id }}</td>
                                    <td class="px-3 py-2">{{ $b->name }}</td>
                                    <td class="px-3 py-2">{{ $b->verification_status }}</td>
                                    <td class="px-3 py-2">{{ optional($b->created_time)->toDateTimeString() }}</td>
                                    <td class="px-3 py-2">{{ $b->ad_accounts_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-gray-800 mb-2">Tài khoản quảng cáo</div>
                <div class="overflow-auto rounded border border-gray-200">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-600"><tr><th class="px-3 py-2 text-left">ID</th><th class="px-3 py-2 text-left">Account ID</th><th class="px-3 py-2 text-left">Name</th><th class="px-3 py-2 text-left">Status</th><th class="px-3 py-2 text-left">#Campaigns</th></tr></thead>
                        <tbody>
                            @foreach(($data['data']['accounts'] ?? []) as $acc)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $acc->id }}</td>
                                    <td class="px-3 py-2">{{ $acc->account_id }}</td>
                                    <td class="px-3 py-2">{{ $acc->name }}</td>
                                    <td class="px-3 py-2">{{ $acc->account_status }}</td>
                                    <td class="px-3 py-2">{{ $acc->campaigns_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Tất cả Quảng cáo (Ads)</h3>
                    <p class="text-sm text-gray-600 mt-1">Hiển thị tất cả quảng cáo với thông tin chi tiết</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th class="px-3 py-2 text-left">Ad ID</th>
                                <th class="px-3 py-2 text-left">Ad Name</th>
                                <th class="px-3 py-2 text-left">Campaign</th>
                                <th class="px-3 py-2 text-left">Ad Set</th>
                                <th class="px-3 py-2 text-left">Type</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Page ID</th>
                                <th class="px-3 py-2 text-left">Content</th>
                                <th class="px-3 py-2 text-left">Created</th>
                                <th class="px-3 py-2 text-left">Spend</th>
                                <th class="px-3 py-2 text-left">Impressions</th>
                                <th class="px-3 py-2 text-left">Clicks</th>
                                <th class="px-3 py-2 text-left">CTR (%)</th>
                                <th class="px-3 py-2 text-left">CPC</th>
                                <th class="px-3 py-2 text-left">Last Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($data['data']['ads'] ?? []) as $ad)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-xs">{{ Str::limit($ad->id, 15) }}</td>
                                    <td class="px-3 py-2 max-w-xs">
                                        <div class="truncate font-medium">{{ Str::limit($ad->name, 25) }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ Str::limit($ad->campaign->name ?? 'N/A', 20) }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($ad->campaign_id, 15) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ Str::limit($ad->adSet->name ?? 'N/A', 20) }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($ad->adset_id, 15) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($ad->post_id)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Post Ad
                                            </span>
                                        @elseif($ad->creative_link_url)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Link Ad
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                Standard
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            {{ $ad->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 
                                               ($ad->status === 'PAUSED' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $ad->status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ $ad->page_id ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 max-w-xs">
                                        @if($ad->post_id)
                                            <div class="text-sm">
                                                <div class="font-medium text-blue-600">Post:</div>
                                                <div class="truncate">{{ Str::limit($ad->post_message ?? 'N/A', 40) }}</div>
                                            </div>
                                        @elseif($ad->creative_link_url)
                                            <div class="text-sm">
                                                <div class="font-medium text-green-600">Link:</div>
                                                <div class="truncate">{{ Str::limit($ad->creative_link_name ?? 'N/A', 30) }}</div>
                                                <div class="text-xs text-gray-500">{{ Str::limit($ad->creative_link_url, 25) }}</div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">No content</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ optional($ad->created_time)->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 text-sm font-medium">${{ number_format($ad->insights->sum('spend') ?? 0, 2) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ number_format($ad->insights->sum('impressions') ?? 0) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ number_format($ad->insights->sum('clicks') ?? 0) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ number_format($ad->insights->avg('ctr') ?? 0, 2) }}%</td>
                                    <td class="px-3 py-2 text-sm">${{ number_format($ad->insights->avg('cpc') ?? 0, 2) }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-500">{{ optional($ad->last_insights_sync)->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Posts & Link Ads từ Quảng cáo</h3>
                    <p class="text-sm text-gray-600 mt-1">Hiển thị các quảng cáo có post hoặc link content với metrics chi tiết</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th class="px-3 py-2 text-left">Ad ID</th>
                                <th class="px-3 py-2 text-left">Ad Name</th>
                                <th class="px-3 py-2 text-left">Campaign</th>
                                <th class="px-3 py-2 text-left">Ad Set</th>
                                <th class="px-3 py-2 text-left">Type</th>
                                <th class="px-3 py-2 text-left">Content</th>
                                <th class="px-3 py-2 text-left">Page ID</th>
                                <th class="px-3 py-2 text-left">Created</th>
                                <th class="px-3 py-2 text-left">Post Metrics</th>
                                <th class="px-3 py-2 text-left">Ad Metrics</th>
                                <th class="px-3 py-2 text-left">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($data['data']['posts'] ?? []) as $ad)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-xs">{{ Str::limit($ad->id, 15) }}</td>
                                    <td class="px-3 py-2 max-w-xs">
                                        <div class="truncate font-medium">{{ Str::limit($ad->name, 25) }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ Str::limit($ad->campaign->name ?? 'N/A', 20) }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($ad->campaign_id, 15) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ Str::limit($ad->adSet->name ?? 'N/A', 20) }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($ad->adset_id, 15) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($ad->post_id)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Post Ad
                                            </span>
                                        @elseif($ad->creative_link_url)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Link Ad
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                Unknown
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 max-w-xs">
                                        @if($ad->post_id)
                                            <div class="text-sm">
                                                <div class="font-medium text-blue-600">Post:</div>
                                                <div class="truncate">{{ Str::limit($ad->post_message ?? 'N/A', 40) }}</div>
                                            </div>
                                        @elseif($ad->creative_link_url)
                                            <div class="text-sm">
                                                <div class="font-medium text-green-600">Link:</div>
                                                <div class="truncate">{{ Str::limit($ad->creative_link_name ?? 'N/A', 30) }}</div>
                                                <div class="text-xs text-gray-500">{{ Str::limit($ad->creative_link_url, 25) }}</div>
                                            </div>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ $ad->page_id ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 text-xs">{{ optional($ad->created_time)->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">
                                        <div class="text-xs space-y-1">
                                            <div>👍 {{ number_format($ad->post_likes ?? 0) }}</div>
                                            <div>🔄 {{ number_format($ad->post_shares ?? 0) }}</div>
                                            <div>💬 {{ number_format($ad->post_comments ?? 0) }}</div>
                                            <div>👁️ {{ number_format($ad->post_impressions ?? 0) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-xs space-y-1">
                                            <div>💰 ${{ number_format($ad->insights->sum('spend') ?? 0, 2) }}</div>
                                            <div>👁️ {{ number_format($ad->insights->sum('impressions') ?? 0) }}</div>
                                            <div>🖱️ {{ number_format($ad->insights->sum('clicks') ?? 0) }}</div>
                                            <div>📈 {{ number_format($ad->insights->sum('reach') ?? 0) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-xs space-y-1">
                                            <div>CTR: {{ number_format($ad->insights->avg('ctr') ?? 0, 2) }}%</div>
                                            <div>CPC: ${{ number_format($ad->insights->avg('cpc') ?? 0, 2) }}</div>
                                            <div>CPM: ${{ number_format($ad->insights->avg('cpm') ?? 0, 2) }}</div>
                                            <div>Eng: {{ number_format($ad->post_engagement_rate ?? 0, 2) }}%</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
try {
    document.addEventListener('DOMContentLoaded', function() {
        const btnSyncAds = document.getElementById('btnSyncFacebook');
        const btnStopSync = document.getElementById('btnStopSync');
        
        if (!btnSyncAds) {
            console.error('❌ Không tìm thấy btnSyncFacebook!');
            return;
        }
    
        const elProgress = document.getElementById('syncProgress');
        const elBar = document.getElementById('syncBar');
        const elPercent = document.getElementById('syncPercent');
        const elStage = document.getElementById('syncStage');
        const elCounts = document.getElementById('syncCounts');
        const elErrors = document.getElementById('syncErrors');
        
        let progressInterval = null;
        let syncStatus = null;

        function renderCounts(counts) {
            const items = [
                ['Businesses', counts?.businesses ?? 0, 'bg-blue-50 text-blue-700 border-blue-200'],
                ['Accounts', counts?.accounts ?? 0, 'bg-green-50 text-green-700 border-green-200'],
                ['Campaigns', counts?.campaigns ?? 0, 'bg-purple-50 text-purple-700 border-purple-200'],
                ['AdSets', counts?.adsets ?? 0, 'bg-yellow-50 text-yellow-700 border-yellow-200'],
                ['Ads', counts?.ads ?? 0, 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                ['Pages', counts?.pages ?? 0, 'bg-pink-50 text-pink-700 border-pink-200'],
                ['Posts', counts?.posts ?? 0, 'bg-orange-50 text-orange-700 border-orange-200'],
                ['Insights', counts?.insights ?? 0, 'bg-red-50 text-red-700 border-red-200'],
            ];
            
            elCounts.innerHTML = items.map(([label, value, colors]) => `
                <div class="px-3 py-2 rounded-lg border ${colors} text-center flex-1 min-w-[100px]">
                    <div class="text-xs font-medium opacity-75">${label}</div>
                    <div class="text-lg font-bold">${value.toLocaleString()}</div>
                </div>
            `).join('');
        }

        function renderErrors(errors) {
            if (!errors || errors.length === 0) { 
                elErrors.innerHTML = ''; 
                return; 
            }
            
            elErrors.innerHTML = errors.map(error => `
                <div class="px-3 py-2 bg-red-50 rounded-lg border border-red-200 mb-2">
                    <div class="text-sm font-medium text-red-800">${error.stage || 'Unknown'}</div>
                    <div class="text-sm text-red-600">${error.message}</div>
                    ${error.timestamp ? `<div class="text-xs text-red-500">${new Date(error.timestamp).toLocaleString()}</div>` : ''}
                </div>
            `).join('');
            
            // Hiển thị lỗi Facebook riêng biệt
            showFacebookErrors(errors);
        }

        function updateProgress(progress) {
            if (progress.message) {
                elStage.textContent = progress.message;
            }
            
            if (progress.counts) {
                renderCounts(progress.counts);
            }
            
            // Hiển thị progress
            elProgress.classList.remove('hidden');
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        async function startProgressPolling() {
            if (progressInterval) {
                clearInterval(progressInterval);
            }
            
            progressInterval = setInterval(async () => {
                try {
                    const response = await fetch('{{ route("facebook.sync.progress") }}');
                    const result = await response.json();
                    
                    if (result.success && result.progress) {
                        updateProgress(result.progress);
                        
                        // Nếu hoàn thành hoặc lỗi, dừng polling
                        if (result.status?.status === 'completed' || result.status?.status === 'failed') {
                            clearInterval(progressInterval);
                            progressInterval = null;
                            btnStopSync.classList.add('hidden');
                            
                            if (result.status.status === 'completed') {
                                showNotification('Đồng bộ Facebook Ads hoàn thành!', 'success');
                                // Reload trang sau 3 giây
                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            } else {
                                showNotification('Đồng bộ Facebook Ads thất bại!', 'error');
                            }
                            
                            // Reset button
                            btnSyncAds.removeAttribute('disabled');
                            btnSyncAds.innerHTML = `
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Đồng bộ Ads
                            `;
                        }
                    }
                } catch (error) {
                    console.error('Lỗi khi poll progress:', error);
                }
            }, 2000); // Poll mỗi 2 giây
        }

        async function checkSyncStatus() {
            try {
                const response = await fetch('{{ route("facebook.sync.status") }}');
                const result = await response.json();
                
                if (result.success && result.status) {
                    syncStatus = result.status;
                    
                    // Cập nhật UI dựa trên trạng thái
                    if (syncStatus.status === 'running') {
                        elProgress.classList.remove('hidden');
                        btnSyncAds.classList.add('hidden');
                        btnStopSync.classList.remove('hidden');
                        
                        // Bắt đầu polling progress
                        if (!progressInterval) {
                            progressInterval = setInterval(checkProgress, 1000);
                        }
                    } else if (syncStatus.status === 'completed') {
                        elProgress.classList.add('hidden');
                        btnSyncAds.classList.remove('hidden');
                        btnStopSync.classList.add('hidden');
                        
                        if (progressInterval) {
                            clearInterval(progressInterval);
                            progressInterval = null;
                        }
                        
                        showNotification('Đồng bộ hoàn thành!', 'success');
                        
                        // Refresh page sau 2 giây để hiển thị dữ liệu mới
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else if (syncStatus.status === 'failed') {
                        elProgress.classList.add('hidden');
                        btnSyncAds.classList.remove('hidden');
                        btnStopSync.classList.add('hidden');
                        
                        if (progressInterval) {
                            clearInterval(progressInterval);
                            progressInterval = null;
                        }
                        
                        // Hiển thị debug info nếu có
                        if (result.debug_info) {
                            showDebugInfo(result.debug_info);
                        }
                        
                        showNotification('Đồng bộ thất bại!', 'error');
                    } else if (syncStatus.status === 'stopped') {
                        elProgress.classList.add('hidden');
                        btnSyncAds.classList.remove('hidden');
                        btnStopSync.classList.add('hidden');
                        
                        if (progressInterval) {
                            clearInterval(progressInterval);
                            progressInterval = null;
                        }
                        
                        showNotification('Đồng bộ đã dừng!', 'info');
                    }
                } else {
                    // Không có trạng thái sync
                    elProgress.classList.add('hidden');
                    btnSyncAds.classList.remove('hidden');
                    btnStopSync.classList.add('hidden');
                    
                    if (progressInterval) {
                        clearInterval(progressInterval);
                        progressInterval = null;
                    }
                    
                    // Hiển thị debug info nếu có
                    if (result.debug_info) {
                        showDebugInfo(result.debug_info);
                    }
                }
            } catch (error) {
                console.error('Lỗi khi kiểm tra trạng thái sync:', error);
                showNotification('Lỗi khi kiểm tra trạng thái: ' + error.message, 'error');
            }
        }

        // Function để cập nhật UI dựa trên trạng thái sync
        function updateUIFromSyncStatus(status) {
            if (status.status === 'running' || status.status === 'queued') {
                // Hiển thị progress và nút dừng
                elProgress.classList.remove('hidden');
                elStage.textContent = status.message || 'Đang xử lý...';
                btnStopSync.classList.remove('hidden');
                
                if (status.status === 'running') {
                    startProgressPolling();
                }
            } else if (status.status === 'stopped') {
                // Hiển thị trạng thái dừng
                elProgress.classList.remove('hidden');
                elStage.textContent = 'Đã dừng bởi người dùng';
                btnStopSync.classList.add('hidden');
                
                // Reset counts về 0
                renderCounts({
                    businesses: 0,
                    accounts: 0,
                    campaigns: 0,
                    adsets: 0,
                    ads: 0,
                    pages: 0,
                    posts: 0,
                    insights: 0
                });
            } else {
                // Ẩn progress và nút dừng
                elProgress.classList.add('hidden');
                btnStopSync.classList.add('hidden');
            }
        }
        
        function showFacebookErrors(errors) {
            const facebookErrors = document.getElementById('facebookErrors');
            const facebookErrorsList = document.getElementById('facebookErrorsList');
            
            if (!errors || errors.length === 0) {
                facebookErrors.classList.add('hidden');
                return;
            }
            
            // Lọc lỗi Facebook API
            const facebookApiErrors = errors.filter(error => 
                error.stage && (
                    error.stage.includes('facebook') || 
                    error.stage.includes('api') || 
                    error.stage.includes('getPostDetails') ||
                    error.stage.includes('getPostInsights') ||
                    error.stage.includes('getInsightsForAd')
                )
            );
            
            if (facebookApiErrors.length === 0) {
                facebookErrors.classList.add('hidden');
                return;
            }
            
            facebookErrorsList.innerHTML = facebookApiErrors.map(error => `
                <div class="mb-2 p-2 bg-red-100 rounded border border-red-300">
                    <div class="font-medium">${error.stage}</div>
                    <div>${error.message}</div>
                    ${error.ad_id ? `<div class="text-xs">Ad ID: ${error.ad_id}</div>` : ''}
                    ${error.post_id ? `<div class="text-xs">Post ID: ${error.post_id}</div>` : ''}
                </div>
            `).join('');
            
            facebookErrors.classList.remove('hidden');
        }
        
        function showDebugInfo(debugInfo) {
            const debugPanel = document.getElementById('debugPanel');
            const debugContent = document.getElementById('debugContent');
            
            if (!debugInfo || Object.keys(debugInfo).length === 0) {
                debugPanel.classList.add('hidden');
                return;
            }
            
            debugContent.innerHTML = Object.entries(debugInfo).map(([key, value]) => {
                if (typeof value === 'object') {
                    return `<div><strong>${key}:</strong> <pre class="mt-1 p-2 bg-yellow-100 rounded text-xs overflow-x-auto">${JSON.stringify(value, null, 2)}</pre></div>`;
                }
                return `<div><strong>${key}:</strong> ${value}</div>`;
            }).join('');
            
            debugPanel.classList.remove('hidden');
        }
        
        function toggleDebugPanel() {
            const debugPanel = document.getElementById('debugPanel');
            debugPanel.classList.toggle('hidden');
        }

        // Kiểm tra trạng thái sync khi load trang
        checkSyncStatus();

        btnSyncAds.addEventListener('click', async function() {
            try {
                btnSyncAds.setAttribute('disabled', 'disabled');
                btnSyncAds.innerHTML = `
                    <svg class="w-4 h-4 inline mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Đang đồng bộ...
                `;
                
                elProgress.classList.remove('hidden');
                elStage.textContent = 'Đang khởi tạo...';
                renderCounts({});
                renderErrors([]);
                btnStopSync.classList.remove('hidden');
                
                const response = await fetch('{{ route("facebook.sync.ads") }}', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Đã bắt đầu đồng bộ Facebook Ads trong background', 'info');
                    
                    // Bắt đầu polling progress
                    startProgressPolling();
                    
                } else {
                    throw new Error(result.message || 'Lỗi không xác định');
                }
                
            } catch (error) {
                console.error('Lỗi khi bắt đầu đồng bộ:', error);
                showNotification('Lỗi: ' + error.message, 'error');
                
                // Reset button
                btnSyncAds.removeAttribute('disabled');
                btnSyncAds.innerHTML = `
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Đồng bộ Ads
                `;
                btnStopSync.classList.add('hidden');
            }
        });

        // Cleanup khi component unmount
        window.addEventListener('beforeunload', function() {
            if (progressInterval) {
                clearInterval(progressInterval);
            }
        });

        // Stop sync button
        btnStopSync.addEventListener('click', async function() {
            try {
                btnStopSync.setAttribute('disabled', 'disabled');
                btnStopSync.textContent = 'Đang dừng...';

                const response = await fetch('{{ route('facebook.sync.stop') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    if (progressInterval) { clearInterval(progressInterval); progressInterval = null; }
                    showNotification('Đã dừng quá trình đồng bộ', 'info');
                    
                    // Cập nhật UI ngay lập tức
                    elStage.textContent = 'Đã dừng bởi người dùng';
                    
                    // Reset counts về 0
                    renderCounts({
                        businesses: 0,
                        accounts: 0,
                        campaigns: 0,
                        adsets: 0,
                        ads: 0,
                        pages: 0,
                        posts: 0,
                        insights: 0
                    });
                    
                    // Ẩn nút dừng và hiển thị nút đồng bộ
                    btnStopSync.classList.add('hidden');
                    btnSyncAds.removeAttribute('disabled');
                    btnSyncAds.innerHTML = `
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Đồng bộ Ads
                    `;
                } else {
                    throw new Error(result.message || 'Không dừng được đồng bộ');
                }
            } catch (error) {
                console.error('Lỗi khi dừng đồng bộ:', error);
                showNotification('Lỗi khi dừng: ' + error.message, 'error');
            } finally {
                btnStopSync.removeAttribute('disabled');
                btnStopSync.textContent = 'Dừng đồng bộ';
            }
        });

        // Reset sync button
        const btnResetSync = document.getElementById('btnResetSync');
        btnResetSync.addEventListener('click', async function() {
            try {
                btnResetSync.setAttribute('disabled', 'disabled');
                btnResetSync.textContent = 'Đang reset...';

                const response = await fetch('{{ route('facebook.sync.reset') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    if (progressInterval) { clearInterval(progressInterval); progressInterval = null; }
                    showNotification('Đã reset hoàn toàn trạng thái đồng bộ', 'success');
                    
                    // Reset UI
                    elStage.textContent = 'Đã reset';
                    elProgress.classList.add('hidden');
                    
                    // Reset counts về 0
                    renderCounts({
                        businesses: 0,
                        accounts: 0,
                        campaigns: 0,
                        adsets: 0,
                        ads: 0,
                        pages: 0,
                        posts: 0,
                        insights: 0
                    });
                    
                    // Ẩn nút dừng và hiển thị nút đồng bộ
                    btnStopSync.classList.add('hidden');
                    btnSyncAds.removeAttribute('disabled');
                    btnSyncAds.innerHTML = `
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Đồng bộ Ads
                    `;
                    
                    // Reload trang sau 1 giây
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(result.message || 'Không reset được trạng thái');
                }
            } catch (error) {
                console.error('Lỗi khi reset đồng bộ:', error);
                showNotification('Lỗi khi reset: ' + error.message, 'error');
            } finally {
                btnResetSync.removeAttribute('disabled');
                btnResetSync.innerHTML = `
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset
                `;
            }
        });
    });
} catch (error) {
    console.error('Lỗi khởi tạo script cho Facebook Sync:', error);
    // Có thể hiển thị thông báo lỗi cho người dùng ở đây
}
</script>
@endpush
