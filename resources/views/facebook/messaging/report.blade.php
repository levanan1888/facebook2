<x-layouts.app :title="__('Facebook Messaging Report')">
    <div class="p-6">
        <h1 class="text-xl font-semibold mb-2">Hiệu quả hoạt động nhắn tin</h1>

        <form method="get" class="flex gap-3 items-end mb-5">
        <div>
            <label class="block text-sm">Since</label>
            <input type="date" name="since" value="{{ $since }}" class="border p-1 rounded">
        </div>
        <div>
            <label class="block text-sm">Until</label>
            <input type="date" name="until" value="{{ $until }}" class="border p-1 rounded">
        </div>
        <div>
            <label class="block text-sm">Page</label>
            <select name="page_id" class="border p-1 rounded">
                <option value="">All pages</option>
                @foreach($pages as $p)
                    <option value="{{ $p->page_id }}" @selected($pageId===$p->page_id)>{{ $p->name }} ({{ $p->page_id }})</option>
                @endforeach
            </select>
        </div>
            <button class="bg-blue-600 text-white px-3 py-1 rounded">Áp dụng</button>
    </form>

        @php
            $totalNew = max((int)($totals['new'] ?? 0), 0);
            $totalPaid = max((int)($totals['paid'] ?? 0), 0);
            $totalOrganic = max((int)($totals['organic'] ?? 0), 0);
            $totalAll = max((int)($totals['total'] ?? ($totalNew)), 0);
            $totalReturning = max($totalAll - $totalNew, 0);
            
            // Calculate percentages
            $totalConversations = $totalPaid + $totalOrganic;
            $paidPercentage = $totalConversations > 0 ? round(($totalPaid / $totalConversations) * 100, 1) : 0;
            $organicPercentage = $totalConversations > 0 ? round(($totalOrganic / $totalConversations) * 100, 1) : 0;
        @endphp

        <div class="grid grid-cols-12 gap-4 items-start">
            <div class="col-span-12">
                <div class="bg-white rounded-xl shadow-sm p-4 border mb-4">
                    <div class="text-sm text-gray-600 mb-2">Xu hướng lượt bắt đầu theo ngày</div>
                    <div class="h-40">
                        <canvas id="msgChart"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-white rounded-xl shadow-sm p-4 border">
                        <div class="text-sm text-gray-600">Tổng lượt bắt đầu</div>
                        <div class="text-3xl font-bold text-gray-900">{{ number_format($totalNew) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 border">
                        <div class="text-sm text-gray-600">Organic</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $organicPercentage }}%</div>
                        <div class="text-xs text-gray-500">{{ number_format($totalOrganic) }} cuộc trò chuyện</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 border">
                        <div class="text-sm text-gray-600">Paid</div>
                        <div class="text-3xl font-bold text-gray-900">{{ $paidPercentage }}%</div>
                        <div class="text-xs text-gray-500">{{ number_format($totalPaid) }} cuộc trò chuyện</div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-2">
                    <div class="bg-white rounded-xl shadow-sm p-4 border">
                        <div class="text-sm text-gray-600 mb-2">Theo kiểu người liên hệ</div>
                        <canvas id="contactTypeChart" height="160"></canvas>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 border">
                        <div class="text-sm text-gray-600 mb-2">Tỉ lệ Paid vs Organic</div>
                        <canvas id="donutChart" height="160"></canvas>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 border flex items-center justify-center">
                        <div>
                            <div class="text-sm text-gray-600 mb-2 text-center">Tổng quan</div>
                            <div class="text-center text-2xl font-semibold">{{ number_format($totalNew) }}</div>
                            <div class="text-center text-xs text-gray-500">Lượt bắt đầu</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right-side daily list hidden as requested -->
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Line chart (daily new)
            const labels = {!! json_encode(array_keys($byDate)) !!};
            const dataNew = {!! json_encode(array_values(array_map(fn($r)=>$r['new'],$byDate))) !!};
            const ctxLine = document.getElementById('msgChart').getContext('2d');
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'Lượt bắt đầu', data: dataNew, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.15)', tension: 0.3, pointRadius: 2 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // Doughnut chart (Paid vs Organic)
            const ctxDonut = document.getElementById('donutChart').getContext('2d');
            const paid = {{ (int) $totalPaid }};
            const organic = {{ (int) $totalOrganic }};
            const total = Math.max(paid + organic, 1);
            new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: [`Paid ({{ $paidPercentage }}%)`, `Organic ({{ $organicPercentage }}%)`],
                    datasets: [{
                        data: [paid, organic],
                        backgroundColor: ['#10b981', '#6366f1'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '58%',
                    rotation: 0,
                    circumference: 360,
                    responsive: true,
                    animation: { animateRotate: true, animateScale: true, duration: 900, easing: 'easeOutCubic' },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const value = ctx.parsed;
                                    const pct = total ? (value * 100 / total) : 0;
                                    return `${ctx.label}: ${value.toLocaleString()} (${pct.toFixed(1)}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Doughnut chart (New vs Returning)
            const ctxType = document.getElementById('contactTypeChart').getContext('2d');
            const contactNew = {{ (int) $totalNew }};
            const contactReturning = {{ (int) $totalReturning }};
            const totalType = Math.max(contactNew + contactReturning, 1);
            new Chart(ctxType, {
                type: 'doughnut',
                data: {
                    labels: ['Người liên hệ mới', 'Quay lại'],
                    datasets: [{
                        data: [contactNew, contactReturning],
                        backgroundColor: ['#0ea5e9', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '58%',
                    rotation: 0,
                    circumference: 360,
                    responsive: true,
                    animation: { animateRotate: true, animateScale: true, duration: 900, easing: 'easeOutCubic' },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const value = ctx.parsed;
                                    const pct = totalType ? (value * 100 / totalType) : 0;
                                    return `${ctx.label}: ${value.toLocaleString()} (${pct.toFixed(1)}%)`;
                                }
                            }
                        }
                    }
                }
            });
        </script>
    </div>
</x-layouts.app>
