<x-layouts.app :title="__('Unified Marketing Dashboard')">
    <div class="min-h-screen bg-gray-50">
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Unified Marketing Dashboard</h1>
                        <p class="text-sm text-gray-600">Tổng hợp dữ liệu đa nền tảng</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">All Platforms Overview</h2>
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
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border border-gray-200 rounded-lg p-4 bg-white">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">Facebook • Spend (7 ngày)</h4>
                                <span class="text-xs text-gray-500">USD</span>
                            </div>
                            <div class="h-36"><canvas id="fbPlatformChart"></canvas></div>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-white">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">Google Ads • Spend (7 ngày)</h4>
                                <span class="text-xs text-gray-500">USD</span>
                            </div>
                            <div class="h-36"><canvas id="ggPlatformChart"></canvas></div>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-white">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">TikTok Ads • Spend (7 ngày)</h4>
                                <span class="text-xs text-gray-500">USD</span>
                            </div>
                            <div class="h-36"><canvas id="ttPlatformChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Chi tiêu 7 ngày (Tổng)</h3>
                    <div class="h-72"><canvas id="unifiedSpendChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Phân bổ nền tảng</h3>
                    <div class="h-72"><canvas id="unifiedPieChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
<script>
function initUnifiedCharts() {
    // Destroy existing charts if any to prevent duplication on SPA navigations
    window.__unifiedCharts ||= {};

    const series = @json($data['series'] ?? []);
    const labels = series.map(s=>s.date);
    const fbSpend = series.map(s=>s.spend||0);
    const zero = labels.map(()=>0);
    const platforms = @json($data['platforms'] ?? []);

    if (document.getElementById('unifiedSpendChart')) {
        const ctx = document.getElementById('unifiedSpendChart').getContext('2d');
        window.__unifiedCharts.total && window.__unifiedCharts.total.destroy();
        window.__unifiedCharts.total = new Chart(ctx, { type: 'bar', data: { labels, datasets: [{ label: 'Spend ($)', data: fbSpend, backgroundColor: 'rgba(59,130,246,0.6)' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } } });
    }

    if (document.getElementById('unifiedPieChart')) {
        const ctx2 = document.getElementById('unifiedPieChart').getContext('2d');
        const allocLabels = ['Facebook','Google Ads','TikTok Ads'];
        const allocValues = [platforms.facebook?.spend||0, platforms.google?.spend||0, platforms.tiktok?.spend||0];
        window.__unifiedCharts.pie && window.__unifiedCharts.pie.destroy();
        window.__unifiedCharts.pie = new Chart(ctx2, { type: 'doughnut', data: { labels: allocLabels, datasets: [{ data: allocValues, backgroundColor: ['#1877F2','#34A853','#000000'] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
    }

    // Platform mini charts (spend only for now)
    const smallOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } };
    if (document.getElementById('fbPlatformChart')) {
        const c = document.getElementById('fbPlatformChart').getContext('2d');
        window.__unifiedCharts.fb && window.__unifiedCharts.fb.destroy();
        window.__unifiedCharts.fb = new Chart(c, { type: 'line', data: { labels, datasets: [{ data: fbSpend, borderColor: '#1877F2', backgroundColor: 'rgba(24,119,242,0.15)', fill: true, tension: 0.4, pointRadius: 0 }] }, options: smallOpts });
    }
    if (document.getElementById('ggPlatformChart')) {
        const c = document.getElementById('ggPlatformChart').getContext('2d');
        window.__unifiedCharts.gg && window.__unifiedCharts.gg.destroy();
        window.__unifiedCharts.gg = new Chart(c, { type: 'line', data: { labels, datasets: [{ data: zero, borderColor: '#34A853', backgroundColor: 'rgba(52,168,83,0.15)', fill: true, tension: 0.4, pointRadius: 0 }] }, options: smallOpts });
    }
    if (document.getElementById('ttPlatformChart')) {
        const c = document.getElementById('ttPlatformChart').getContext('2d');
        window.__unifiedCharts.tt && window.__unifiedCharts.tt.destroy();
        window.__unifiedCharts.tt = new Chart(c, { type: 'line', data: { labels, datasets: [{ data: zero, borderColor: '#000000', backgroundColor: 'rgba(0,0,0,0.12)', fill: true, tension: 0.4, pointRadius: 0 }] }, options: smallOpts });
    }
}

function ensureUnifiedChartLibAndInit(){
    if (window.Chart) { initUnifiedCharts(); return; }
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    s.onload = initUnifiedCharts;
    document.head.appendChild(s);
}

document.addEventListener('DOMContentLoaded', ensureUnifiedChartLibAndInit);
window.addEventListener('livewire:navigated', ensureUnifiedChartLibAndInit);
</script>
@endpush


