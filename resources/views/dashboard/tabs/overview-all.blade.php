<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">All Platforms Overview</h2>
            <p class="text-gray-600">Tổng hợp chi tiêu và hiệu suất đa nền tảng</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Tổng chi tiêu</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($data['totals']['spend'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Hiển thị</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['impressions'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Clicks</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['clicks'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Reach</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($data['totals']['reach'] ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Theo nền tảng</h3>
        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Platform</th>
                        <th class="px-4 py-3 text-left">Spend</th>
                        <th class="px-4 py-3 text-left">Impr</th>
                        <th class="px-4 py-3 text-left">Clicks</th>
                        <th class="px-4 py-3 text-left">Reach</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['facebook'=>'Facebook','google'=>'Google Ads','tiktok'=>'TikTok Ads'] as $key=>$name)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-medium">{{ $name }}</td>
                            <td class="px-4 py-3">${{ number_format($data['platforms'][$key]['spend'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($data['platforms'][$key]['impressions'] ?? 0) }}</td>
                            <td class="px-4 py-3">{{ number_format($data['platforms'][$key]['clicks'] ?? 0) }}</td>
                            <td class="px-4 py-3">{{ number_format($data['platforms'][$key]['reach'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

