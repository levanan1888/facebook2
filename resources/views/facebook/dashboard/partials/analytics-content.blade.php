<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Phân tích Facebook Ads</h2>
            <p class="text-gray-600">Phân tích chi tiết hiệu suất quảng cáo</p>
        </div>
    </div>

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
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0), 2) }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalImpressions'] ?? ($data['performanceStats']['totalImpressions'] ?? 0)) }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalClicks'] ?? ($data['performanceStats']['totalClicks'] ?? 0)) }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totalReach'] ?? ($data['performanceStats']['totalReach'] ?? 0)) }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ number_format(($data['avgCTR'] ?? ($data['performanceStats']['avgCTR'] ?? 0)) * 100, 2) }}%</p>
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
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($data['avgCPC'] ?? ($data['performanceStats']['avgCPC'] ?? 0), 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Click-Through Rate (CTR)</span>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format(($data['avgCTR'] ?? ($data['performanceStats']['avgCTR'] ?? 0)) * 100, 2) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($data['avgCTR'] ?? ($data['performanceStats']['avgCTR'] ?? 0)) * 1000, 100) }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Cost Per Click (CPC)</span>
                    <span class="text-sm font-semibold text-gray-900">${{ number_format($data['avgCPC'] ?? ($data['performanceStats']['avgCPC'] ?? 0), 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(($data['avgCPC'] ?? ($data['performanceStats']['avgCPC'] ?? 0)) * 10, 100) }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Cost Per Mille (CPM)</span>
                    <span class="text-sm font-semibold text-gray-900">${{ ($data['totalImpressions'] ?? ($data['performanceStats']['totalImpressions'] ?? 0)) > 0 ? number_format((($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0)) / ($data['totalImpressions'] ?? ($data['performanceStats']['totalImpressions'] ?? 0))) * 1000, 2) : '0.00' }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($data['totalImpressions'] ?? ($data['performanceStats']['totalImpressions'] ?? 0)) > 0 ? min((($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0)) / ($data['totalImpressions'] ?? ($data['performanceStats']['totalImpressions'] ?? 0))) * 100, 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Efficiency Metrics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Impressions per Dollar</p>
                        <p class="text-xs text-gray-500">Impressions đạt được trên mỗi đô</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ ($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0)) > 0 ? number_format(($data['totalImpressions'] ?? ($data['performanceStats']['totalImpressions'] ?? 0)) / ($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0))) : 0 }}
                    </span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Clicks per Dollar</p>
                        <p class="text-xs text-gray-500">Clicks trên mỗi đô chi tiêu</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ ($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0)) > 0 ? number_format(($data['totalClicks'] ?? ($data['performanceStats']['totalClicks'] ?? 0)) / ($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0)), 2) : 0 }}
                    </span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Reach per Dollar</p>
                        <p class="text-xs text-gray-500">Reach trên mỗi đô chi tiêu</p>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ ($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0)) > 0 ? number_format(($data['totalReach'] ?? ($data['performanceStats']['totalReach'] ?? 0)) / ($data['totalSpend'] ?? ($data['performanceStats']['totalSpend'] ?? 0))) : 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>


