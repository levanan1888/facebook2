<!-- Time Filter for Engagement -->
<div class="flex justify-end mb-6">
    <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
        <button class="px-4 py-2 text-sm font-medium text-blue-600 hover:bg-gray-200 rounded-md">All time</button>
        <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md">Last 28 days</button>
    </div>
</div>

<!-- Posts and Engagement Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Posts Chart -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ $engagementData['posts']['value'] }}</h3>
                <p class="text-sm text-gray-600">Posts</p>
            </div>
            <div class="text-right">
                <span class="text-sm text-red-600">{{ $engagementData['posts']['change'] }}% (28 ngày trước)</span>
            </div>
        </div>
        <div class="h-64">
            <canvas id="postsChart"></canvas>
        </div>
    </div>

    <!-- Engagement Chart -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ $engagementData['engagement']['value'] }}</h3>
                <p class="text-sm text-gray-600">Engagement</p>
            </div>
            <div class="text-right">
                <span class="text-sm text-red-600">{{ $engagementData['engagement']['change'] }}% (28 ngày trước)</span>
            </div>
        </div>
        <div class="h-64">
            <canvas id="engagementChart"></canvas>
        </div>
    </div>
</div>

<!-- Top 10 Most Viewed Posts -->
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Top 10 most viewed posts in the last 28 days</h3>
            <select class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                <option>Category</option>
                <option>All</option>
                <option>Others</option>
                <option>Manager</option>
                <option>Sales</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posts</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reactions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ER</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($engagementData['topPosts'] as $post)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['member'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['posts'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['category'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['reactions'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['comments'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['views'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $post['er'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">88</span> results
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

<!-- Posts by Category and Top Contributors -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Posts by Category -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Posts by category</h3>
        <div class="h-64 mb-4">
            <!-- Donut chart placeholder -->
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="w-32 h-32 rounded-full border-8 border-blue-600 mx-auto mb-4"></div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Others (84.0%)</span>
                            <div class="w-4 h-4 bg-blue-600 rounded"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Manager (12.0%)</span>
                            <div class="w-4 h-4 bg-purple-600 rounded"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Sales (4.0%)</span>
                            <div class="w-4 h-4 bg-green-600 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Posts</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">L</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ER</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($engagementData['postsByCategory'] as $category)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $category['category'] }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $category['posts'] }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $category['likes'] }}%</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $category['er'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Contributors -->
    <div class="space-y-6">
        @foreach(['posts', 'likes', 'comments'] as $type)
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Contributors - {{ ucfirst($type) }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ ucfirst($type) }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach(array_slice($engagementData['topContributors'][$type], 0, 5) as $contributor)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $contributor['name'] }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $contributor['percentage'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">88</span> results
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
        @endforeach
    </div>
</div>

<!-- Active Users by Hour and Day -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- By Day -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top active users by hour and day - By Day</h3>
        <div class="space-y-3">
            @foreach($engagementData['activeUsers']['byDay'] as $day)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">{{ $day['day'] }}</span>
                <div class="flex items-center space-x-2">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $day['active'] }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600">{{ $day['active'] }}%</span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-sm text-gray-600">
            Tổng cộng 90 người dùng hoạt động, chiếm 100%
        </div>
    </div>

    <!-- By Hour -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top active users by hour and day - By Hour</h3>
        <div class="space-y-3">
            @foreach(array_slice($engagementData['activeUsers']['byHour'], 0, 10) as $hour)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">{{ $hour['hour'] }}</span>
                <div class="flex items-center space-x-2">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $hour['active'] }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600">{{ $hour['active'] }}%</span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Tổng cộng 90 người dùng hoạt động, chiếm 100%
            </div>
            <div class="text-sm text-gray-600">
                1-10/24
            </div>
        </div>
    </div>
</div>

<!-- Heatmap -->
<div class="bg-white border border-gray-200 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Heatmap active users by hour and day</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-2 py-2 text-xs font-medium text-gray-500"></th>
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <th class="px-2 py-2 text-xs font-medium text-gray-500">{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(['08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h', '20h', '21h', '22h', '23h'] as $hour)
                <tr>
                    <td class="px-2 py-1 text-xs text-gray-600">{{ $hour }}</td>
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    @php
                        $activity = $engagementData['heatmapData'][array_search($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])][strtolower($hour)] ?? 0;
                        $bgColor = $activity > 3 ? 'bg-blue-800' : ($activity > 2 ? 'bg-blue-600' : ($activity > 1 ? 'bg-blue-400' : 'bg-blue-200'));
                    @endphp
                    <td class="px-2 py-1">
                        <div class="w-8 h-8 {{ $bgColor }} rounded flex items-center justify-center text-xs text-white font-medium">
                            {{ $activity }}
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> 