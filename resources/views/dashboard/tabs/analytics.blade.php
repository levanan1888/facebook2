<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Phân tích Facebook Ads</h2>
            <p class="text-gray-600">Phân tích chi tiết hiệu suất quảng cáo</p>
        </div>
    </div>

    <!-- Analytics Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tổng chi tiêu</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($data['totalSpend'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Hiển thị</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalImpressions']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Lượt click</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalClicks']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tiếp cận</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalReach']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">CTR trung bình</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['avgCTR'] * 100, 2) }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">CPC trung bình</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($data['avgCPC'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Click-Through Rate (CTR)</span>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format($data['avgCTR'] * 100, 2) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($data['avgCTR'] * 1000, 100) }}%"></div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Cost Per Click (CPC)</span>
                    <span class="text-sm font-semibold text-gray-900">${{ number_format($data['avgCPC'], 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min($data['avgCPC'] * 10, 100) }}%"></div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Cost Per Mille (CPM)</span>
                    <span class="text-sm font-semibold text-gray-900">${{ $data['totalImpressions'] > 0 ? number_format(($data['totalSpend'] / $data['totalImpressions']) * 1000, 2) : '0.00' }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $data['totalImpressions'] > 0 ? min(($data['totalSpend'] / $data['totalImpressions']) * 100, 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Efficiency Metrics -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Efficiency Metrics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Impressions per Dollar</p>
                        <p class="text-xs text-gray-500">How many impressions per dollar spent</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ $data['totalSpend'] > 0 ? number_format($data['totalImpressions'] / $data['totalSpend']) : 0 }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Clicks per Dollar</p>
                        <p class="text-xs text-gray-500">How many clicks per dollar spent</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ $data['totalSpend'] > 0 ? number_format($data['totalClicks'] / $data['totalSpend'], 2) : 0 }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Reach per Dollar</p>
                        <p class="text-xs text-gray-500">How many unique people reached per dollar</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ $data['totalSpend'] > 0 ? number_format($data['totalReach'] / $data['totalSpend']) : 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendations</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @if($data['avgCTR'] < 0.01)
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span class="text-sm font-medium text-yellow-800">Low CTR</span>
                    </div>
                    <p class="text-xs text-yellow-700 mt-1">Consider improving ad creative and targeting</p>
                </div>
            @endif

            @if($data['avgCPC'] > 2.00)
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span class="text-sm font-medium text-red-800">High CPC</span>
                    </div>
                    <p class="text-xs text-red-700 mt-1">Review bidding strategy and audience targeting</p>
                </div>
            @endif

            @if($data['totalSpend'] > 0 && $data['totalClicks'] > 0)
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-green-800">Good Performance</span>
                    </div>
                    <p class="text-xs text-green-700 mt-1">Campaigns are generating clicks and impressions</p>
                </div>
            @endif

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-blue-800">Optimization Tip</span>
                </div>
                <p class="text-xs text-blue-700 mt-1">Monitor performance daily and adjust budgets accordingly</p>
            </div>
        </div>
    </div>
</div>
