<x-layouts.app :title="__('Unified Marketing Dashboard')">
    <div class="min-h-screen bg-gray-50">
        <!-- Header với branding mới -->
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
                        <p class="text-sm text-gray-600">Tích hợp dữ liệu từ nhiều nền tảng marketing</p>
                    </div>
                </div>
                
                <!-- Data Source Status -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">Facebook Ads</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">Facebook Posts</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                        <span class="text-sm text-gray-400">Google Ads</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                        <span class="text-sm text-gray-400">TikTok Ads</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs mới -->
        <div class="bg-white border-b border-gray-200 px-6 py-3">
            <div class="flex space-x-1">
                <a href="?tab=overview" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $tab == 'overview' ? 'text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Tổng quan</span>
                </a>
                <a href="?tab=unified-data" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $tab == 'unified-data' ? 'text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Dữ liệu thống nhất</span>
                </a>
                <a href="?tab=data-raw" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $tab == 'data-raw' ? 'text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Data Raw</span>
                </a>
                <a href="?tab=hierarchy" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $tab == 'hierarchy' ? 'text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M3 7l9 6 9-6" />
                    </svg>
                    <span>Hierarchy</span>
                </a>
                <a href="?tab=analytics" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $tab == 'analytics' ? 'text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Analytics</span>
                </a>
                <a href="?tab=comparison" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $tab == 'comparison' ? 'text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>So sánh</span>
                </a>
                <a href="?tab=data-raw" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-md {{ $activeTab == 'data-raw' ? 'text-white bg-blue-600' : 'text-blue-600 hover:bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Data Raw</span>
                </a>
            </div>
        </div>

        <!-- Content với layout mới -->
        <div class="p-6">
            @if($tab == 'overview')
                @include('dashboard.tabs.overview-all')
            @elseif($tab == 'fb-overview')
                @include('dashboard.tabs.overview')
            @elseif($tab == 'unified-data')
                @include('dashboard.tabs.unified-data')
            @elseif($tab == 'data-raw')
                @include('dashboard.tabs.data-raw')
            @elseif($tab == 'hierarchy')
                @include('dashboard.tabs.hierarchy')
            @elseif($tab == 'analytics')
                @include('dashboard.tabs.analytics')
            @elseif($tab == 'comparison')
                @include('dashboard.tabs.comparison')
            @endif
        </div>
    </div>
</x-layouts.app>
