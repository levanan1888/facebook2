<!-- Weekly KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach(['join', 'view', 'engagement'] as $kpi)
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Weekly KPI: {{ ucfirst($kpi) }}</h3>
        </div>
        <div class="space-y-3">
            <div class="text-3xl font-bold text-gray-900">{{ $weeklyKpis[$kpi]['value'] }}</div>
            <div class="text-sm text-gray-600">{{ $weeklyKpis[$kpi]['percentage'] }}%</div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $weeklyKpis[$kpi]['progress'] }}%"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Detailed Metrics -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Metrics</h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach($detailedMetrics as $metric => $data)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($data['icon'] == 'users')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            @elseif($data['icon'] == 'user-group')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            @elseif($data['icon'] == 'document-text')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            @elseif($data['icon'] == 'eye')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            @elseif($data['icon'] == 'heart')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            @elseif($data['icon'] == 'chat-bubble-left-right')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            @endif
                        </svg>
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $metric)) }}</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        @if($data['trend'] == 'up')
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <span class="text-sm text-green-600">+{{ $data['change'] }}</span>
                        @else
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                        <span class="text-sm text-red-600">{{ $data['change'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $data['value'] }}</div>
                <div class="text-xs text-gray-500">from 4 days ago</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Chart</h3>
        <div class="h-64">
            <canvas id="growthChart"></canvas>
        </div>
    </div>
</div>

<!-- Weekly Data Table -->
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Weekly Data</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weekly</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KPI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Active</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posts</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Viewed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reactions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($weeklyData as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['date'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($row['status'] == 'pass KPI')
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-green-600">{{ $row['status'] }}</span>
                        </div>
                        @else
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-red-600">{{ $row['status'] }}</span>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['kpi'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['joined'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['no_active'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['posts'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['viewed'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['reactions'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['comments'] }}</td>
                </tr>
                @endforeach
                <!-- Totals Row -->
                <tr class="bg-gray-50 font-semibold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tổng cộng</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['kpi'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['joined'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['no_active'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['posts'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['viewed'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['reactions'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $totals['comments'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">4</span> results
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 text-sm text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="px-3 py-1 text-sm text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>
</div> 