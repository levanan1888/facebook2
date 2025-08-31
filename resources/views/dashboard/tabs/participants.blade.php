<!-- Age & Gender Chart -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Age & Gender</h3>
        <div class="h-64">
            <canvas id="ageGenderChart"></canvas>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="space-y-6">
            <!-- Total Engagement -->
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <canvas id="engagementMetricsChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($participantsData['engagementMetrics']['total'] / 1000, 1) }}K</div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center space-x-4 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-600 rounded mr-2"></div>
                        <span>{{ number_format($participantsData['engagementMetrics']['total'] / 1000, 1) }}K</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-purple-600 rounded mr-2"></div>
                        <span>{{ number_format($participantsData['engagementMetrics']['engaged'] / 1000, 1) }}K</span>
                    </div>
                </div>
            </div>

            <!-- Engagement Rate -->
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <canvas id="engagementRateChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <div class="text-2xl font-bold text-gray-900">{{ $participantsData['engagementMetrics']['engagement_rate'] }}%</div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center space-x-4 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-purple-600 rounded mr-2"></div>
                        <span>{{ $participantsData['engagementMetrics']['engagement_rate'] }}%</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-600 rounded mr-2"></div>
                        <span>{{ 100 - $participantsData['engagementMetrics']['engagement_rate'] }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Countries and Cities Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Tag Country -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Tag Country</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($participantsData['countries'] as $country)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $country['country'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($country['members']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $country['percentage'] }}%"></div>
                                </div>
                                <span class="text-sm text-gray-900">{{ $country['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <!-- Total Row -->
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tổng cộng</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(array_sum(array_column($participantsData['countries'], 'members'))) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">100.0%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                1-{{ count($participantsData['countries']) }}/{{ count($participantsData['countries']) }}
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

    <!-- Tag Cities -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Tag Cities</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($participantsData['cities'] as $city)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $city['city'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($city['members']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $city['percentage'] }}%"></div>
                                </div>
                                <span class="text-sm text-gray-900">{{ $city['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <!-- Total Row -->
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tổng cộng</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(array_sum(array_column($participantsData['cities'], 'members'))) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">100.0%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                1-10/20
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
</div> 