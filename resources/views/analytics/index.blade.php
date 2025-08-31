<x-layouts.app :title="__('Analytics Overview')">
    <div class="p-6 space-y-6">
        <div id="loading-indicator" class="hidden fixed top-3 right-3 z-50 flex items-center gap-2 bg-gray-900/80 text-white text-xs px-3 py-1.5 rounded shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span id="loading-text">Đang tải…</span>
        </div>
        <div class="p-4 rounded border border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-800">Bộ lọc</div>
                @permission('analytics.filter')
                <div class="flex items-center gap-2">
                    <button id="btn-toggle-filters" class="text-xs px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1">
                        <span>🔎</span><span>Lọc</span>
                    </button>
                    <button id="btn-reset" class="text-xs px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Reset</button>
                    <button id="btn-apply" class="text-xs px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white shadow">Áp dụng</button>
                </div>
                @endpermission
            </div>
            <div id="filters-panel" class="grid grid-cols-1 lg:grid-cols-3 gap-4 hidden">
                <div class="border rounded p-3 bg-blue-50/50">
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-700 mb-2">
                        <span>🗓️</span> <span>Thời gian</span>
                    </div>
                    <div class="flex flex-wrap items-end gap-3">
                        @permission('analytics.filter.time')
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Chọn nhanh</label>
                            <select id="flt-preset" class="text-xs border rounded px-2 py-1">
                                <option value="day">Hôm qua</option>
                                <option value="week">7 ngày</option>
                                <option value="month">30 ngày</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Từ ngày</label>
                            <input type="date" id="flt-from" class="text-xs border rounded px-2 py-1" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Đến ngày</label>
                            <input type="date" id="flt-to" class="text-xs border rounded px-2 py-1" />
                        </div>
                        @endpermission
                    </div>
                </div>
                <div class="border rounded p-3 bg-gray-50">
                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                        <span>🎯</span> <span>Chi tiết chiến dịch</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @permission('analytics.filter.scope')
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">BM</label>
                            <select id="flt-business" class="text-xs border rounded px-2 py-1 w-full"></select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Tài khoản</label>
                            <select id="flt-account" class="text-xs border rounded px-2 py-1 w-full"></select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Chiến dịch</label>
                            <select id="flt-campaign" class="text-xs border rounded px-2 py-1 w-full"></select>
                        </div>
                        @endpermission
                    </div>
                </div>
                <div class="border rounded p-3 bg-green-50/60">
                    <div class="flex items-center gap-2 text-xs font-semibold text-green-700 mb-2">
                        <span>⚙️</span> <span>Thông số & sắp xếp</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
                        @permission('analytics.filter.metrics')
                        <div>
                            <label class="block text-[10px] text-gray-600 mb-1">Phân rã theo</label>
                            <select id="flt-by" class="text-xs border rounded px-2 py-1 w-full">
                                <option value="business">BM</option>
                                <option value="account">Tài khoản</option>
                                <option value="campaign">Chiến dịch</option>
                                <option value="adset">Ad set</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-600 mb-1">Sắp xếp theo</label>
                            <select id="flt-sortBy" class="text-xs border rounded px-2 py-1 w-full">
                                <option value="spend">Spend</option>
                                <option value="impressions">Impressions</option>
                                <option value="clicks">Clicks</option>
                                <option value="cpc">CPC</option>
                                <option value="cpm">CPM</option>
                                <option value="ctr">CTR</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-600 mb-1">Chiều</label>
                            <select id="flt-sortDir" class="text-xs border rounded px-2 py-1 w-full">
                                <option value="desc">Giảm dần</option>
                                <option value="asc">Tăng dần</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-600 mb-1">Giới hạn</label>
                            <input type="number" id="flt-limit" class="text-xs border rounded px-2 py-1 w-full" value="10" min="1" />
                        </div>
                        @endpermission
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                        @permission('analytics.filter.metrics')
                        <div>
                            <div class="flex items-center justify-between text-[10px] text-gray-600 mb-1"><span>Min CPC</span><span id="lbl-minCpc">0</span></div>
                            <input type="range" id="rng-minCpc" min="0" max="100" step="0.1" class="w-full" />
                            <input type="hidden" id="flt-minCpc" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-[10px] text-gray-600 mb-1"><span>Min CTR (%)</span><span id="lbl-minCtr">0</span></div>
                            <input type="range" id="rng-minCtr" min="0" max="100" step="0.1" class="w-full" />
                            <input type="hidden" id="flt-minCtr" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-[10px] text-gray-600 mb-1"><span>Min CPM</span><span id="lbl-minCpm">0</span></div>
                            <input type="range" id="rng-minCpm" min="0" max="50000" step="10" class="w-full" />
                            <input type="hidden" id="flt-minCpm" />
                        </div>
                        @endpermission
                    </div>
                </div>
            </div>
            
        </div>
        <div class="flex flex-wrap gap-3" id="summary-cards">
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">Chi tiêu ({{ $rangeLabel }})</div>
                <div class="text-2xl font-semibold" id="sum-spend">{{ number_format($totals['spend_period'] ?? 0, 2) }}</div>
            </div>
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">Lượt hiển thị ({{ $rangeLabel }})</div>
                <div class="text-2xl font-semibold" id="sum-impressions">{{ number_format($totals['impressions_period'] ?? 0) }}</div>
            </div>
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">Lượt nhấp ({{ $rangeLabel }})</div>
                <div class="text-2xl font-semibold" id="sum-clicks">{{ number_format($totals['clicks_period'] ?? 0) }}</div>
            </div>
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">CTR (%)</div>
                <div class="text-2xl font-semibold" id="sum-ctr">{{ number_format($totals['ctr_period'] ?? 0, 2) }}</div>
            </div>
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">CPC</div>
                <div class="text-2xl font-semibold" id="sum-cpc">{{ number_format($totals['cpc_period'] ?? 0, 2) }}</div>
            </div>
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">CPM</div>
                <div class="text-2xl font-semibold" id="sum-cpm">{{ number_format($totals['cpm_period'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="p-4 rounded border border-gray-200">
                <div class="text-xs text-gray-700">ROAS</div>
                <div class="text-2xl font-semibold" id="sum-roas">{{ number_format($totals['roas_period'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="w-full md:w-1/2 lg:w-1/3 p-4 rounded border border-gray-200">
                <div class="text-sm font-semibold text-gray-800 mb-2">Phân bổ Spend theo <span id="lbl-breakdown">Business</span></div>
                <div class="h-64"><canvas id="chart-spend-share" class="w-full h-full"></canvas></div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/3 p-4 rounded border border-gray-200">
                <div class="text-sm font-semibold text-gray-800 mb-2">Phân bổ Clicks theo <span id="lbl-breakdown-2">Business</span></div>
                <div class="h-64"><canvas id="chart-click-share" class="w-full h-full"></canvas></div>
            </div>
            <div class="w-full md:w-1/2 lg:w-1/3 p-4 rounded border border-gray-200">
                <div class="text-sm font-semibold text-gray-800 mb-2">Phân bổ Impressions theo <span id="lbl-breakdown-3">Business</span></div>
                <div class="h-64"><canvas id="chart-impressions-share" class="w-full h-full"></canvas></div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-xs">
                <button id="tab-overview" class="px-3 py-1.5 rounded bg-blue-600 text-white">Tổng quan</button>
                <button id="tab-ads" class="px-3 py-1.5 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Ads (Posts)</button>
            </div>
            <div>
                <button id="btn-view-ads" class="text-xs px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white">Xem Ads (Posts)</button>
            </div>
        </div>

        <div id="panel-overview">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-gray-800">Spend by Business ({{ $rangeLabel }})</h2>
                <a href="{{ route('dashboard', ['tab' => 'data-raw']) }}" class="text-xs text-blue-600 hover:underline">Go to Data Raw</a>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-gray-600"><tr><th class="px-3 py-2 text-left">Business</th><th class="px-3 py-2 text-left">#Accounts</th><th class="px-3 py-2 text-left">Spend</th></tr></thead>
                    <tbody>
                        @foreach($spendByBusiness as $row)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $row['name'] ?? $row['id'] }}</td>
                                <td class="px-3 py-2">{{ $row['accounts'] }}</td>
                                <td class="px-3 py-2">{{ number_format($row['spend'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="panel-breakdown">
            <div class="text-sm font-semibold text-gray-800 mb-2">Top Accounts by Spend ({{ $rangeLabel }})</div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-xs" id="tbl-breakdown">
                    <thead class="bg-gray-50 text-gray-600"><tr><th class="px-3 py-2 text-left">Account</th><th class="px-3 py-2 text-left">Name</th><th class="px-3 py-2 text-left">Spend</th></tr></thead>
                    <tbody>
                        @foreach($topAccounts as $a)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $a['account_id'] }}</td>
                                <td class="px-3 py-2">{{ $a['name'] }}</td>
                                <td class="px-3 py-2">{{ number_format($a['spend'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="panel-ads" class="hidden">
            <div class="flex items-center justify-between mb-2">
                <div class="text-sm font-semibold text-gray-800">Ads (Posts) details ({{ $rangeLabel }})</div>
                <div id="ads-summary" class="text-[11px] text-gray-600 flex items-center gap-4">
                    <div><span class="font-semibold">Spend:</span> <span id="ads-sum-spend">0</span></div>
                    <div><span class="font-semibold">Impr.:</span> <span id="ads-sum-impr">0</span></div>
                    <div><span class="font-semibold">Clicks:</span> <span id="ads-sum-clicks">0</span></div>
                    <div><span class="font-semibold">CTR%:</span> <span id="ads-sum-ctr">0</span></div>
                    <div><span class="font-semibold">CPC:</span> <span id="ads-sum-cpc">0</span></div>
                </div>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-xs" id="tbl-ads">
                    <thead class="bg-gray-50 text-gray-600"><tr>
                        <th class="px-3 py-2 text-left">Ad ID</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Ad Set</th>
                        <th class="px-3 py-2 text-left">Campaign</th>
                        <th class="px-3 py-2 text-left">Account</th>
                        <th class="px-3 py-2 text-left">Spend</th>
                        <th class="px-3 py-2 text-left">Impr.</th>
                        <th class="px-3 py-2 text-left">Clicks</th>
                        <th class="px-3 py-2 text-left">CTR (%)</th>
                        <th class="px-3 py-2 text-left">CPC</th>
                        <th class="px-3 py-2 text-left">CPM</th>
                    </tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const SERVER_DATE = "{{ $dateFrom ?? '' }}";
            const API = {
                options: "{{ route('api.analytics.options') }}",
                summary: "{{ route('api.analytics.summary') }}",
                breakdown: "{{ route('api.analytics.breakdown') }}",
                series: "{{ route('api.analytics.series') }}",
                adDetails: "{{ route('api.analytics.ad-details') }}",
            };

            const el = (id) => document.getElementById(id);
            const fmt = new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 });

            const ddPreset = el('flt-preset');
            const inFrom = el('flt-from');
            const inTo = el('flt-to');
            const ddBusiness = el('flt-business');
            const ddAccount = el('flt-account');
            const ddCampaign = el('flt-campaign');
            const ddBy = el('flt-by');
            const ddSortBy = el('flt-sortBy');
            const ddSortDir = el('flt-sortDir');
            const inLimit = el('flt-limit');
            const inMinCpc = el('flt-minCpc');
            const inMinCtr = el('flt-minCtr');
            const inMinCpm = el('flt-minCpm');
            const rngMinCpc = el('rng-minCpc');
            const rngMinCtr = el('rng-minCtr');
            const rngMinCpm = el('rng-minCpm');
            const lblMinCpc = el('lbl-minCpc');
            const lblMinCtr = el('lbl-minCtr');
            const lblMinCpm = el('lbl-minCpm');

            const sumSpend = el('sum-spend');
            const sumImp = el('sum-impressions');
            const sumClk = el('sum-clicks');
            const sumCtr = el('sum-ctr');
            const sumCpc = el('sum-cpc');
            const sumCpm = el('sum-cpm');
            const sumRoas = el('sum-roas');
            const lblBreakdown = el('lbl-breakdown');
            const lblBreakdown2 = el('lbl-breakdown-2');
            const lblBreakdown3 = el('lbl-breakdown-3');

            let chartSpendShare, chartClickShare, chartImpressionsShare;
            let currentContext = { by: null, id: null };

            function setTodayDefaults() {
                const d = (dt) => dt.toISOString().slice(0,10);
                if (SERVER_DATE) {
                    inFrom.value = SERVER_DATE;
                    inTo.value = SERVER_DATE;
                    ddPreset.value = 'day';
                    return;
                }
                const today = new Date();
                const y = new Date(today.getTime() - 86400000);
                inFrom.value = d(y);
                inTo.value = d(y);
                ddPreset.value = 'day';
            }

            function qs(params) {
                const query = new URLSearchParams(Object.fromEntries(Object.entries(params).filter(([,v]) => v !== undefined && v !== null && v !== '')));
                return '?' + query.toString();
            }

            async function get(url, params) {
                try {
                    setLoading(true);
                    const res = await fetch(url + qs(params));
                    return await res.json();
                } finally {
                    setLoading(false);
                }
            }

            function readFilters() {
                return {
                    preset: 'custom',
                    dateFrom: inFrom.value,
                    dateTo: inTo.value,
                    businessId: ddBusiness.value || undefined,
                    accountId: ddAccount.value || undefined,
                    campaignId: ddCampaign.value || undefined,
                    by: ddBy.value,
                    sortBy: ddSortBy.value,
                    sortDir: ddSortDir.value,
                    limit: inLimit.value,
                    minCpc: inMinCpc.value || undefined,
                    minCtr: inMinCtr.value || undefined,
                    minCpm: inMinCpm.value || undefined,
                };
            }

            function renderSummary(s) {
                sumSpend.textContent = fmt.format(s.spend || 0);
                sumImp.textContent = fmt.format(s.impressions || 0);
                sumClk.textContent = fmt.format(s.clicks || 0);
                sumCtr.textContent = fmt.format((s.ctr || 0));
                sumCpc.textContent = fmt.format((s.cpc || 0));
                sumCpm.textContent = fmt.format((s.cpm || 0));
                sumRoas.textContent = fmt.format((s.roas || 0));
            }

            function ensureChart(ctx, type, data, options) {
                if (ctx.__chart) {
                    ctx.__chart.data = data; ctx.__chart.options = options; ctx.__chart.update();
                    return ctx.__chart;
                }
                const chart = new Chart(ctx, { type, data, options });
                ctx.__chart = chart; return chart;
            }

            function renderPieChartsFromBreakdown(resp) {
                const rows = (resp.rows || []).slice(0, 6); // top 6
                const labels = rows.map(r => r.name || r.id);
                const spend = rows.map(r => r.spend || 0);
                const clicks = rows.map(r => r.clicks || 0);
                const imps = rows.map(r => r.impressions || 0);
                const colors = ['#2563eb','#16a34a','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16'];
                const nonEmpty = labels.length > 0 && valuesNonZero(spend, clicks, imps);
                const toDataset = (values) => ({ labels: nonEmpty ? labels : ['No data'], datasets: [{ data: nonEmpty ? values : [1], backgroundColor: (nonEmpty ? labels : ['No data']).map((_,i)=> colors[i%colors.length]) }] });

                function valuesNonZero(...arrs) {
                    return arrs.some(a => (a || []).some(v => Number(v) > 0));
                }

                lblBreakdown.textContent = resp.by === 'account' ? 'Account' : (resp.by === 'campaign' ? 'Campaign' : (resp.by === 'adset' ? 'Ad set' : 'Business'));
                lblBreakdown2.textContent = lblBreakdown.textContent;
                lblBreakdown3.textContent = lblBreakdown.textContent;

                const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
                chartSpendShare = ensureChart(el('chart-spend-share').getContext('2d'), 'doughnut', toDataset(spend), chartOptions);
                chartClickShare = ensureChart(el('chart-click-share').getContext('2d'), 'doughnut', toDataset(clicks), chartOptions);
                chartImpressionsShare = ensureChart(el('chart-impressions-share').getContext('2d'), 'doughnut', toDataset(imps), chartOptions);
            }

            function renderBreakdown(resp) {
                const rows = resp.rows || [];
                lblBreakdown.textContent = (resp.by === 'account') ? 'Account' : (resp.by === 'campaign' ? 'Campaign' : (resp.by === 'adset' ? 'Ad set' : 'Business'));

                // Render table
                const tbl = el('tbl-breakdown');
                if (tbl) {
                    const thead = tbl.querySelector('thead tr');
                    thead.innerHTML = '<th class="px-3 py-2 text-left">' + (resp.by === 'account' ? 'Account' : (resp.by === 'campaign' ? 'Campaign' : (resp.by === 'adset' ? 'Ad set' : 'Business'))) + '</th>' +
                        '<th class="px-3 py-2 text-left">Name</th>' + (resp.by === 'account' ? '<th class="px-3 py-2 text-left">BM</th>' : '') +
                        '<th class="px-3 py-2 text-left">Spend</th>' +
                        '<th class="px-3 py-2 text-left">Impressions</th>' +
                        '<th class="px-3 py-2 text-left">Clicks</th>' +
                        '<th class="px-3 py-2 text-left">CTR (%)</th>' +
                        '<th class="px-3 py-2 text-left">CPC</th>' +
                        '<th class="px-3 py-2 text-left">CPM</th>' +
                        '<th class="px-3 py-2 text-left"></th>';
                    const tbody = tbl.querySelector('tbody');
                    tbody.innerHTML = rows.map(r => `<tr class="border-t">
                        <td class="px-3 py-2">${r.id}</td>
                        <td class="px-3 py-2">${r.name}</td>` + (resp.by === 'account' ? `<td class=\"px-3 py-2\">${r.businessName || ''}</td>` : '') + `
                        <td class="px-3 py-2">${fmt.format(r.spend || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.impressions || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.clicks || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.ctr || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.cpc || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.cpm || 0)}</td>
                        <td class="px-3 py-2">
                            <a href="#" data-action="drill" data-by="${resp.by}" data-id="${r.id}" class="text-blue-600 hover:underline">Drill</a>
                            <span class="mx-1">·</span>
                            <a href="#" data-action="ads" data-by="${resp.by}" data-id="${r.id}" class="text-emerald-600 hover:underline">Ads</a>
                        </td>
                    </tr>`).join('');
                    // Bind actions
                    tbody.querySelectorAll('a[data-action="drill"]').forEach(a => a.addEventListener('click', (e) => {
                        e.preventDefault();
                        const by = a.getAttribute('data-by');
                        const id = a.getAttribute('data-id');
                        handleDrillDown(by, id);
                    }));
                    tbody.querySelectorAll('a[data-action="ads"]').forEach(a => a.addEventListener('click', (e) => {
                        e.preventDefault();
                        const by = a.getAttribute('data-by');
                        const id = a.getAttribute('data-id');
                        setAdsContext(by, id);
                        switchTab('ads');
                        loadAdsDetails();
                    }));
                }
            }
            function setAdsContext(by, id) {
                currentContext = { by, id };
            }

            function handleDrillDown(by, id) {
                // Drill logic: business -> account, account -> campaign, campaign -> adset
                if (by === 'business') {
                    ddBusiness.value = id;
                    filterAccountsByBusiness(id, cacheOptions?.adAccounts || []);
                    ddBy.value = 'account';
                } else if (by === 'account') {
                    ddAccount.value = id;
                    filterCampaignsByAccount(id, cacheOptions?.campaigns || []);
                    ddBy.value = 'campaign';
                } else if (by === 'campaign') {
                    ddCampaign.value = id;
                    ddBy.value = 'adset';
                }
                refreshAll();
            }

            async function loadAdsDetails() {
                const f = readFilters();
                // Merge context into filters
                if (currentContext?.by === 'adset') {
                    f.adsetId = currentContext.id;
                } else if (currentContext?.by === 'campaign') {
                    f.campaignId = currentContext.id;
                } else if (currentContext?.by === 'account') {
                    f.accountId = currentContext.id;
                }
                const data = await get(API.adDetails, f);
                const tbody = document.querySelector('#tbl-ads tbody');
                tbody.innerHTML = (data.rows || []).map(r => `
                    <tr class="border-t">
                        <td class="px-3 py-2">${r.id}</td>
                        <td class="px-3 py-2">${r.name}</td>
                        <td class="px-3 py-2">${r.adsetId}</td>
                        <td class="px-3 py-2">${r.campaignName || r.campaignId}</td>
                        <td class="px-3 py-2">${r.accountName || r.accountCode || r.accountId}</td>
                        <td class="px-3 py-2">${fmt.format(r.spend || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.impressions || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.clicks || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.ctr || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.cpc || 0)}</td>
                        <td class="px-3 py-2">${fmt.format(r.cpm || 0)}</td>
                    </tr>
                `).join('');
                // Summary for Ads (current filter)
                const rows = data.rows || [];
                const sumSpend = rows.reduce((t,r)=>t + (Number(r.spend)||0), 0);
                const sumImpr = rows.reduce((t,r)=>t + (Number(r.impressions)||0), 0);
                const sumClicks = rows.reduce((t,r)=>t + (Number(r.clicks)||0), 0);
                const ctr = sumImpr > 0 ? (sumClicks / sumImpr) * 100 : 0;
                const cpc = sumClicks > 0 ? (sumSpend / sumClicks) : 0;
                el('ads-sum-spend').textContent = fmt.format(sumSpend);
                el('ads-sum-impr').textContent = fmt.format(sumImpr);
                el('ads-sum-clicks').textContent = fmt.format(sumClicks);
                el('ads-sum-ctr').textContent = fmt.format(ctr);
                el('ads-sum-cpc').textContent = fmt.format(cpc);
            }

            function switchTab(tab) {
                const overBtn = el('tab-overview');
                const adsBtn = el('tab-ads');
                const pOverview = el('panel-overview');
                const pBreakdown = el('panel-breakdown');
                const pAds = el('panel-ads');
                if (tab === 'ads') {
                    overBtn.classList.remove('bg-blue-600','text-white');
                    overBtn.classList.add('bg-gray-100','text-gray-700');
                    adsBtn.classList.add('bg-blue-600','text-white');
                    adsBtn.classList.remove('bg-gray-100','text-gray-700');
                    pAds.classList.remove('hidden');
                    pOverview.classList.add('hidden');
                    pBreakdown.classList.add('hidden');
                    // Summary theo cấp ad để khớp với tab Ads
                    refreshAll({ level: 'ad' });
                } else {
                    adsBtn.classList.remove('bg-blue-600','text-white');
                    adsBtn.classList.add('bg-gray-100','text-gray-700');
                    overBtn.classList.add('bg-blue-600','text-white');
                    overBtn.classList.remove('bg-gray-100','text-gray-700');
                    pAds.classList.add('hidden');
                    pOverview.classList.remove('hidden');
                    pBreakdown.classList.remove('hidden');
                    // Trở lại summary theo by
                    refreshAll();
                }
            }

            async function refreshAll(opts = {}) {
                const f = readFilters();
                // Đồng bộ cấp dữ liệu cho summary để số liệu khớp bối cảnh
                const computeLevelFromBy = () => {
                    if (ddBy.value === 'campaign') return 'campaign';
                    if (ddBy.value === 'adset') return 'adset';
                    return 'account';
                };
                f.level = opts.level || computeLevelFromBy();
                setLoading(true, 'Đang lọc dữ liệu…');
                const [sum, brk] = await Promise.all([
                    get(API.summary, f),
                    get(API.breakdown, f)
                ]);
                renderSummary(sum);
                renderBreakdown(brk);
                renderPieChartsFromBreakdown(brk);
                setLoading(false);
            }

            function populateOptions(opt) {
                const setOptions = (select, placeholder, items, idKey = 'id', labelKey = 'name') => {
                    select.innerHTML = `<option value="">${placeholder}</option>` + items.map(i => `<option value="${i[idKey]}">${i[labelKey]}</option>`).join('');
                };
                setOptions(ddBusiness, 'Chọn Business', opt.businesses || []);
                setOptions(ddAccount, 'Chọn Account', opt.adAccounts || [], 'id', 'name');
                setOptions(ddCampaign, 'Chọn Campaign', opt.campaigns || [], 'id', 'name');
            }

            function filterAccountsByBusiness(bizId, all) {
                const items = (all || []).filter(a => !bizId || a.businessId === bizId);
                ddAccount.innerHTML = `<option value="">Chọn Account</option>` + items.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
                ddCampaign.innerHTML = `<option value="">Chọn Campaign</option>`;
            }

            function filterCampaignsByAccount(accId, all) {
                const items = (all || []).filter(c => !accId || c.adAccountId === accId);
                ddCampaign.innerHTML = `<option value="">Chọn Campaign</option>` + items.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
            }

            // Init
            // Mặc định: cả tháng hiện tại để đồng bộ số liệu
            (function setMonthDefaults(){
                const now = new Date();
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                const fmt = (d)=> d.toISOString().slice(0,10);
                inFrom.value = fmt(start);
                inTo.value = fmt(now);
                if (ddPreset) ddPreset.value = 'month';
            })();
            // Mặc định xem Top theo Tài khoản để rõ ràng hơn
            ddBy.value = 'account';
            let cacheOptions = null;
            get(API.options, {}).then(opt => {
                cacheOptions = opt; populateOptions(opt);
            });

            // Range bindings
            function bindRange(rng, hidden, lbl, factor = 1) {
                const sync = () => { hidden.value = (rng.value / factor).toString(); lbl.textContent = rng.value; refreshAll(); };
                rng.addEventListener('input', sync);
                sync();
            }
            bindRange(rngMinCpc, inMinCpc, lblMinCpc, 1);
            bindRange(rngMinCtr, inMinCtr, lblMinCtr, 1);
            bindRange(rngMinCpm, inMinCpm, lblMinCpm, 1);

            ddBusiness.addEventListener('change', () => {
                if (cacheOptions) filterAccountsByBusiness(ddBusiness.value, cacheOptions.adAccounts);
            });
            ddAccount.addEventListener('change', () => {
                if (cacheOptions) filterCampaignsByAccount(ddAccount.value, cacheOptions.campaigns);
            });

            // Không tự động chạy khi thay đổi; chỉ khi nhấn Áp dụng
            const delayedChange = () => {};
            [ddPreset, inFrom, inTo, ddBy, ddSortBy, ddSortDir, inLimit]
                .forEach(ctrl => ctrl.addEventListener('change', delayedChange));
            [ddBusiness, ddAccount, ddCampaign].forEach(ctrl => ctrl.addEventListener('change', delayedChange));
            document.getElementById('btn-apply').addEventListener('click', () => refreshAll());
            document.getElementById('btn-reset').addEventListener('click', () => {
                resetFiltersToDefault();
            });
            document.getElementById('btn-toggle-filters').addEventListener('click', function() {
                const panel = document.getElementById('filters-panel');
                panel.classList.toggle('hidden');
            });
            el('tab-overview').addEventListener('click', () => switchTab('overview'));
            el('tab-ads').addEventListener('click', () => { switchTab('ads'); loadAdsDetails(); });
            el('btn-view-ads').addEventListener('click', () => {
                // Prefer the deepest selected filter as context
                if (ddCampaign.value) setAdsContext('campaign', ddCampaign.value);
                else if (ddAccount.value) setAdsContext('account', ddAccount.value);
                else setAdsContext(null, null);
                switchTab('ads');
                loadAdsDetails();
            });

            // Đảm bảo tab mặc định là Tổng quan và các panel hiển thị đúng
            switchTab('overview');
            // Không tự fetch đến khi người dùng nhấn Áp dụng
            function resetFiltersToDefault() {
                // Reset thời gian về đầu tháng → hôm nay
                const now = new Date();
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                const fmt = (d)=> d.toISOString().slice(0,10);
                inFrom.value = fmt(start);
                inTo.value = fmt(now);
                if (ddPreset) ddPreset.value = 'month';
                // Reset phạm vi
                if (ddBusiness) ddBusiness.value = '';
                if (ddAccount) ddAccount.value = '';
                if (ddCampaign) ddCampaign.value = '';
                // Reset sắp xếp & giới hạn
                if (ddBy) ddBy.value = 'account';
                if (ddSortBy) ddSortBy.value = 'spend';
                if (ddSortDir) ddSortDir.value = 'desc';
                if (inLimit) inLimit.value = 10;
                // Reset các ngưỡng
                const setRange = (rngId, hiddenId, lblId, val) => {
                    const rng = el(rngId); const hid = el(hiddenId); const lb = el(lblId);
                    if (rng) rng.value = String(val);
                    if (hid) hid.value = String(val);
                    if (lb) lb.textContent = String(val);
                };
                setRange('rng-minCpc','flt-minCpc','lbl-minCpc',0);
                setRange('rng-minCtr','flt-minCtr','lbl-minCtr',0);
                setRange('rng-minCpm','flt-minCpm','lbl-minCpm',0);
            }

            function setLoading(isLoading, text = 'Đang tải…') {
                const box = el('loading-indicator');
                const lbl = el('loading-text');
                if (!box) return;
                if (lbl) lbl.textContent = text || 'Đang tải…';
                if (isLoading) box.classList.remove('hidden');
                else box.classList.add('hidden');
            }
        });
        </script>
        @endpush
    </div>
</x-layouts.app>


