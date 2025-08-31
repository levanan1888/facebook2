<div class="space-y-6">
    <!-- Comparison Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">So sánh hiệu suất</h2>
                <p class="text-gray-600 mt-1">Đánh giá và so sánh hiệu suất giữa các nền tảng marketing</p>
            </div>
            <div class="flex items-center space-x-4">
                <button id="btnExportReport" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Xuất báo cáo</span>
                </button>
                <button id="btnScheduleReport" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Lên lịch báo cáo</span>
                </button>
            </div>
        </div>

        <!-- Comparison Controls -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Khoảng thời gian</label>
                <select id="comparisonDateRange" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="7">7 ngày qua</option>
                    <option value="30" selected>30 ngày qua</option>
                    <option value="90">90 ngày qua</option>
                    <option value="365">1 năm qua</option>
                    <option value="custom">Tùy chỉnh</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nguồn dữ liệu 1</label>
                <select id="source1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="facebook_ads" selected>Facebook Ads</option>
                    <option value="facebook_posts">Facebook Posts</option>
                    <option value="google_ads">Google Ads</option>
                    <option value="tiktok_ads">TikTok Ads</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nguồn dữ liệu 2</label>
                <select id="source2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="facebook_posts" selected>Facebook Posts</option>
                    <option value="facebook_ads">Facebook Ads</option>
                    <option value="google_ads">Google Ads</option>
                    <option value="tiktok_ads">TikTok Ads</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Chỉ số so sánh</label>
                <select id="metric" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="spend">Chi tiêu</option>
                    <option value="impressions">Impressions</option>
                    <option value="clicks">Clicks</option>
                    <option value="reach">Reach</option>
                    <option value="ctr">CTR</option>
                    <option value="cpc">CPC</option>
                    <option value="cpm">CPM</option>
                </select>
            </div>
        </div>

        <!-- Comparison Chart -->
        <div class="bg-gray-50 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Biểu đồ so sánh</h3>
                <div class="flex items-center space-x-2">
                    <button id="chartTypeLine" class="px-3 py-1 text-sm bg-blue-600 text-white border border-blue-600 rounded-lg">Đường</button>
                    <button id="chartTypeBar" class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cột</button>
                    <button id="chartTypeArea" class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Vùng</button>
                </div>
            </div>
            <div class="h-80 bg-white rounded-lg border border-gray-200 flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p>Biểu đồ so sánh sẽ được hiển thị ở đây</p>
                    <p class="text-sm">Tích hợp với Chart.js để hiển thị dữ liệu so sánh</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Comparison Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Bảng so sánh chi tiết</h3>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Cập nhật lần cuối:</span>
                <span class="text-sm font-medium text-gray-900">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chỉ số</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                            <span id="source1Label">Facebook Ads</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-green-600 uppercase tracking-wider">
                            <span id="source2Label">Facebook Posts</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Chênh lệch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">% Thay đổi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Đánh giá</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Chi tiêu</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($data['totals']['spend'] ?? 0) }} VND</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Không có dữ liệu
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Impressions</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($data['totals']['impressions'] ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Không có dữ liệu
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Clicks</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($data['totals']['clicks'] ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Không có dữ liệu
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CTR</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format(($data['totals']['ctr'] ?? 0) * 100, 2) }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Không có dữ liệu
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Insights & Recommendations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Thông tin chi tiết & Khuyến nghị</h3>
            <button id="btnRefreshInsights" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Làm mới</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Key Insights -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-sm font-semibold text-blue-900">Thông tin chính</h4>
                </div>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li>• Facebook Ads có hiệu suất cao nhất về impressions</li>
                    <li>• CTR trung bình đạt 2.1%</li>
                    <li>• Chi tiêu quảng cáo tăng 12.5% so với tháng trước</li>
                </ul>
            </div>

            <!-- Recommendations -->
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h4 class="text-sm font-semibold text-green-900">Khuyến nghị</h4>
                </div>
                <ul class="space-y-2 text-sm text-green-800">
                    <li>• Tăng ngân sách cho các chiến dịch có CTR cao</li>
                    <li>• Tối ưu hóa targeting để cải thiện reach</li>
                    <li>• Theo dõi hiệu suất theo thời gian thực</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Comparison Controls
    const comparisonDateRange = document.getElementById('comparisonDateRange');
    const source1 = document.getElementById('source1');
    const source2 = document.getElementById('source2');
    const metric = document.getElementById('metric');
    const source1Label = document.getElementById('source1Label');
    const source2Label = document.getElementById('source2Label');
    
    // Chart Type Controls
    const chartTypeLine = document.getElementById('chartTypeLine');
    const chartTypeBar = document.getElementById('chartTypeBar');
    const chartTypeArea = document.getElementById('chartTypeArea');
    
    // Buttons
    const btnExportReport = document.getElementById('btnExportReport');
    const btnScheduleReport = document.getElementById('btnScheduleReport');
    const btnRefreshInsights = document.getElementById('btnRefreshInsights');

    // Update source labels when selection changes
    function updateSourceLabels() {
        const source1Text = source1.options[source1.selectedIndex].text;
        const source2Text = source2.options[source2.selectedIndex].text;
        source1Label.textContent = source1Text;
        source2Label.textContent = source2Text;
    }

    // Chart type selection
    function setChartType(activeButton, inactiveButtons) {
        activeButton.className = 'px-3 py-1 text-sm bg-blue-600 text-white border border-blue-600 rounded-lg';
        inactiveButtons.forEach(btn => {
            btn.className = 'px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50';
        });
    }

    // Event Listeners
    source1.addEventListener('change', updateSourceLabels);
    source2.addEventListener('change', updateSourceLabels);
    
    chartTypeLine.addEventListener('click', () => setChartType(chartTypeLine, [chartTypeBar, chartTypeArea]));
    chartTypeBar.addEventListener('click', () => setChartType(chartTypeBar, [chartTypeLine, chartTypeArea]));
    chartTypeArea.addEventListener('click', () => setChartType(chartTypeArea, [chartTypeLine, chartTypeBar]));

    // Comparison controls change
    [comparisonDateRange, source1, source2, metric].forEach(control => {
        control.addEventListener('change', function() {
            console.log('Comparison parameters changed:', {
                dateRange: comparisonDateRange.value,
                source1: source1.value,
                source2: source2.value,
                metric: metric.value
            });
            // Implement comparison data loading logic
        });
    });

    // Button actions
    btnExportReport.addEventListener('click', function() {
        alert('Tính năng xuất báo cáo sẽ được phát triển');
    });

    btnScheduleReport.addEventListener('click', function() {
        alert('Tính năng lên lịch báo cáo sẽ được phát triển');
    });

    btnRefreshInsights.addEventListener('click', function() {
        // Simulate refreshing insights
        const insights = document.querySelectorAll('.bg-blue-50, .bg-green-50');
        insights.forEach(insight => {
            insight.style.opacity = '0.5';
            setTimeout(() => {
                insight.style.opacity = '1';
            }, 500);
        });
    });

    // Initialize
    updateSourceLabels();
});
</script>
@endpush
