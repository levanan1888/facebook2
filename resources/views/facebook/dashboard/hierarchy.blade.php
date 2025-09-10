<x-layouts.app :title="__('Facebook Dashboard - Hierarchy')">
    <div class="p-6">
        <div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Phân cấp Facebook Ads</h2>
            <p class="text-sm text-gray-600 mt-1">Khám phá cấu trúc dữ liệu Facebook: Business Manager → Tài khoản quảng cáo → Chiến dịch → Bộ quảng cáo → Bài đăng</p>
        </div>
        <button id="toggle-help" class="ml-4 p-2 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50" title="Hướng dẫn">
            i
        </button>
    </div>
    
    <!-- Advanced Filter Controls -->
    <div class="px-4 py-4 border-b border-gray-200 bg-gray-50">
        <div id="help-panel" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-900">
            - Chọn khoảng thời gian; dữ liệu sẽ lọc theo bảng facebook_ad_insights.<br>
            - Chọn Business → Account → Campaign → Ad set để thu hẹp dữ liệu bd.
        </div>
        <!-- Main Filter Row -->
        <div class="flex flex-wrap gap-4 items-center mb-4">
            <!-- Search Filter -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Tìm kiếm:</label>
                <input type="text" id="filter-search" placeholder="Tên, ID..." class="px-3 py-1 border border-gray-300 rounded-md text-sm w-48">
            </div>
            
            <!-- Status Filter -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Trạng thái:</label>
                <select id="filter-status" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                    <option value="">Tất cả</option>
                    <option value="ACTIVE">Hoạt động</option>
                    <option value="PAUSED">Tạm dừng</option>
                    <option value="DELETED">Đã xóa</option>
                    <option value="PENDING_REVIEW">Chờ duyệt</option>
                    <option value="DISAPPROVED">Không được phê duyệt</option>
                </select>
            </div>
            
            <!-- Objective Filter -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Mục tiêu:</label>
                <select id="filter-objective" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                    <option value="">Tất cả</option>
                    <option value="OUTCOME_TRAFFIC">Traffic</option>
                    <option value="OUTCOME_ENGAGEMENT">Engagement</option>
                    <option value="OUTCOME_AWARENESS">Awareness</option>
                    <option value="OUTCOME_LEADS">Leads</option>
                    <option value="OUTCOME_SALES">Sales</option>
                </select>
            </div>
        </div>
        
        <!-- Advanced Filter Row (removed per requirements) -->
        <div class="flex flex-wrap gap-4 items-center mb-4"></div>
        
        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <button id="apply-filters" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                    </svg>
                    Áp dụng
                </button>
                <button id="reset-filters" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset
                </button>
            </div>
            
        </div>
        
        <!-- Summary Statistics -->
        <div id="summary-stats" class="mt-4 grid grid-cols-2 md:grid-cols-6 gap-4 hidden">
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Tổng chiến dịch</div>
                <div id="total-campaigns" class="text-2xl font-bold text-blue-600">0</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Tổng Ad Sets</div>
                <div id="total-adsets" class="text-2xl font-bold text-green-600">0</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Tổng Ads</div>
                <div id="total-ads" class="text-2xl font-bold text-purple-600">0</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Tổng Posts</div>
                <div id="total-posts" class="text-2xl font-bold text-orange-600">0</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Tổng Pages</div>
                <div id="total-pages" class="text-2xl font-bold text-indigo-600">0</div>
            </div>
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Tổng Accounts</div>
                <div id="total-accounts" class="text-2xl font-bold text-red-600">0</div>
            </div>
        </div>
    </div>
    
    <div class="p-4">
        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <button class="hierarchy-nav text-sm font-medium text-blue-600 hover:text-blue-800" data-level="businesses">
                        Business Managers
                    </button>
                </li>
                <li id="breadcrumb-accounts" class="hidden">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <button class="hierarchy-nav text-sm font-medium text-blue-600 hover:text-blue-800" data-level="accounts">
                            Tài khoản quảng cáo
                        </button>
                    </div>
                </li>
                <li id="breadcrumb-campaigns" class="hidden">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <button class="hierarchy-nav text-sm font-medium text-blue-600 hover:text-blue-800" data-level="campaigns">
                            Chiến dịch
                        </button>
                    </div>
                </li>
                <li id="breadcrumb-adsets" class="hidden">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <button class="hierarchy-nav text-sm font-medium text-blue-600 hover:text-blue-800" data-level="adsets">
                            Bộ quảng cáo
                        </button>
                    </div>
                </li>
                <li id="breadcrumb-posts" class="hidden">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-500">Bài đăng</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Content Area -->
        <div id="hierarchy-content">
            <!-- Loading indicator -->
            <div id="loading" class="hidden text-center py-8">
                <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600 mt-2">Đang tải...</p>
            </div>

            <!-- Data will be loaded here -->
            <div id="data-container"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
class FacebookHierarchy {
    constructor() {
        this.currentLevel = 'businesses';
        this.currentFilters = {};
        this.cache = new Map(); // Cache để tránh gọi API trùng lặp
        this.currentPage = 1;
        this.perPage = 10;
        this.searchTerm = '';
        this.statusFilter = '';
        this.objectiveFilter = '';
        this.sortBy = 'created_at_desc';
        this.groupBy = '';
        this.viewType = 'table';
        this.dateFrom = '';
        this.dateTo = '';
        this.breakdownMode = false;
        this.summaryStats = {
            totalCampaigns: 0,
            totalAdSets: 0,
            totalAds: 0,
            totalPosts: 0,
            totalPages: 0,
            totalAccounts: 0
        };
        this.init();
    }

    init() {
        this.bindEvents();
        // Delay loading để tránh block Livewire navigation
        setTimeout(() => {
            this.loadBusinesses();
        }, 100);
    }

    async fetchJson(url, options = {}) {
        const defaultHeaders = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        
        // Thêm timeout để tránh chờ quá lâu
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout
        
        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { ...(options.headers || {}), ...defaultHeaders },
                signal: controller.signal,
                ...options,
            });

            clearTimeout(timeoutId);
            const contentType = response.headers.get('content-type') || '';

            if (!response.ok) {
                let bodyText = '';
                try { bodyText = await response.text(); } catch (_) {}
                const message = `HTTP ${response.status} - ${response.statusText}`;
                throw new Error(message);
            }

            if (!contentType.includes('application/json')) {
                throw new Error('Phản hồi không phải JSON (có thể bị chuyển hướng hoặc lỗi quyền truy cập)');
            }

            return response.json();
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                throw new Error('Request timeout - vui lòng thử lại');
            }
            throw error;
        }
    }

    bindEvents() {
        document.querySelectorAll('.hierarchy-nav').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const level = e.target.dataset.level;
                this.navigateToLevel(level);
            });
        });
        
        // Filter controls
        document.getElementById('apply-filters')?.addEventListener('click', () => {
            this.applyFilters();
        });
        
        document.getElementById('reset-filters')?.addEventListener('click', () => {
            this.resetFilters();
        });
        
        
        // removed: refresh and breakdown buttons
        
        // Auto-apply filters on change
        document.getElementById('filter-search')?.addEventListener('input', 
            this.debounce(() => this.applyFilters(), 500)
        );
        
        document.getElementById('filter-status')?.addEventListener('change', () => {
            this.applyFilters();
        });
        
        document.getElementById('filter-objective')?.addEventListener('change', () => {
            this.applyFilters();
        });
        
        // removed: sort/group/view/quick-period bindings
        
        // removed: default date range initialization
    }

    async navigateToLevel(level) {
        this.currentLevel = level;
        this.currentPage = 1; // Reset to first page when navigating
        this.updateBreadcrumb();
        switch(level) {
            case 'businesses': this.loadBusinesses(); break;
            case 'accounts': this.loadAccounts(); break;
            case 'campaigns': this.loadCampaigns(); break;
            case 'adsets': this.loadAdSets(); break;
            case 'posts': this.loadPosts(); break;
        }
    }
    
    applyFilters() {
        this.searchTerm = document.getElementById('filter-search')?.value || '';
        this.statusFilter = document.getElementById('filter-status')?.value || '';
        this.objectiveFilter = document.getElementById('filter-objective')?.value || '';
        this.sortBy = 'created_at_desc';
        this.groupBy = '';
        this.viewType = 'table';
        // no date filter
        this.currentPage = 1; // Reset to first page when applying filters
        
        // Clear cache when filters change
        this.cache.clear();
        
        // Reload current level with new filters
        this.navigateToLevel(this.currentLevel);
    }
    
    refreshData() {
        this.cache.clear();
        this.navigateToLevel(this.currentLevel);
    }
    
    toggleBreakdown() {
        this.breakdownMode = !this.breakdownMode;
        const btn = document.getElementById('toggle-breakdown');
        if (this.breakdownMode) {
            btn.classList.add('bg-purple-700');
            btn.classList.remove('bg-purple-600');
        } else {
            btn.classList.add('bg-purple-600');
            btn.classList.remove('bg-purple-700');
        }
        this.applyFilters();
    }
    
    changeViewType() {
        this.viewType = document.getElementById('filter-view-type')?.value || 'table';
        this.renderCurrentView();
    }
    
    setQuickPeriod() {
        const period = document.getElementById('filter-quick-period')?.value || '';
        if (!period) return;
        
        const today = new Date();
        let from, to;
        
        switch(period) {
            case 'today':
                from = to = today.toISOString().split('T')[0];
                break;
            case 'yesterday':
                const yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                from = to = yesterday.toISOString().split('T')[0];
                break;
            case 'last_7_days':
                const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                from = weekAgo.toISOString().split('T')[0];
                to = today.toISOString().split('T')[0];
                break;
            case 'last_30_days':
                const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                from = monthAgo.toISOString().split('T')[0];
                to = today.toISOString().split('T')[0];
                break;
            case 'this_month':
                from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                to = today.toISOString().split('T')[0];
                break;
            case 'last_month':
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                from = lastMonth.toISOString().split('T')[0];
                to = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
                break;
            case 'this_quarter':
                const quarterStart = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 1);
                from = quarterStart.toISOString().split('T')[0];
                to = today.toISOString().split('T')[0];
                break;
            case 'last_quarter':
                const lastQuarterStart = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3 - 3, 1);
                const lastQuarterEnd = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 0);
                from = lastQuarterStart.toISOString().split('T')[0];
                to = lastQuarterEnd.toISOString().split('T')[0];
                break;
            case 'this_year':
                from = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                to = today.toISOString().split('T')[0];
                break;
        }
        
        if (from && to) {
            document.getElementById('filter-from').value = from;
            document.getElementById('filter-to').value = to;
            this.applyFilters();
        }
    }
    
    renderCurrentView() {
        // This will be called when view type changes
        // For now, just reload the current level
        this.navigateToLevel(this.currentLevel);
    }
    
    resetFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-objective').value = '';
        // removed: reset removed controls
        document.getElementById('filter-quick-period').value = '';
        
        // removed: date inputs reset
        
        this.breakdownMode = false;
        const btn = document.getElementById('toggle-breakdown');
        btn.classList.add('bg-purple-600');
        btn.classList.remove('bg-purple-700');
        
        this.applyFilters();
    }
    
    exportData() {
        // Create CSV data from current view
        const table = document.querySelector('#data-container table');
        if (!table) {
            alert('Không có dữ liệu để xuất');
            return;
        }
        
        let csv = '';
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = Array.from(cells).map(cell => {
                return '"' + cell.textContent.replace(/"/g, '""') + '"';
            });
            csv += rowData.join(',') + '\n';
        });
        
        // Download CSV
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `facebook-hierarchy-${this.currentLevel}-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
    
    updateSummaryStats(data, pagination = null) {
        if (!data || !Array.isArray(data)) return;
        
        // Use pagination totals if available, otherwise count from items
        if (pagination) {
            this.summaryStats = {
                totalCampaigns: pagination.total_campaigns || 0,
                totalAdSets: pagination.total_adsets || 0,
                totalAds: pagination.total_ads || 0,
                totalPosts: pagination.total_posts || 0,
                totalPages: pagination.total_pages || 0,
                totalAccounts: pagination.total_accounts || 0
            };
        } else {
            // Fallback: Count different types of items
            this.summaryStats = {
                totalCampaigns: 0,
                totalAdSets: 0,
                totalAds: 0,
                totalPosts: 0,
                totalPages: 0,
                totalAccounts: 0
            };
            
            data.forEach(item => {
                switch(this.currentLevel) {
                    case 'businesses':
                        this.summaryStats.totalAccounts += item.ad_accounts_count || item.adAccounts_count || 0;
                        break;
                    case 'accounts':
                        this.summaryStats.totalCampaigns += parseInt(item.campaigns_count || 0);
                        this.summaryStats.totalAdSets += parseInt(item.adsets_count || 0);
                        this.summaryStats.totalAds += parseInt(item.ads_count || 0);
                        this.summaryStats.totalPosts += parseInt(item.posts_count || 0);
                        break;
                    case 'campaigns':
                        this.summaryStats.totalAdSets += parseInt(item.ad_sets_count || 0);
                        this.summaryStats.totalAds += parseInt(item.ads_count || 0);
                        this.summaryStats.totalPosts += parseInt(item.posts_count || 0);
                        this.summaryStats.totalPages += parseInt(item.pages_count || 0);
                        break;
                    case 'adsets':
                        this.summaryStats.totalAds += parseInt(item.ads_count || 0);
                        break;
                    case 'posts':
                        this.summaryStats.totalPosts += 1;
                        if (item.page_id) this.summaryStats.totalPages += 1;
                        break;
                }
            });
        }
        
        // Update UI - check if elements exist before updating
        const totalCampaigns = document.getElementById('total-campaigns');
        const totalAdSets = document.getElementById('total-adsets');
        const totalAds = document.getElementById('total-ads');
        const totalPosts = document.getElementById('total-posts');
        const totalPages = document.getElementById('total-pages');
        const totalAccounts = document.getElementById('total-accounts');
        
        if (totalCampaigns) totalCampaigns.textContent = this.summaryStats.totalCampaigns.toLocaleString();
        if (totalAdSets) totalAdSets.textContent = this.summaryStats.totalAdSets.toLocaleString();
        if (totalAds) totalAds.textContent = this.summaryStats.totalAds.toLocaleString();
        if (totalPosts) totalPosts.textContent = this.summaryStats.totalPosts.toLocaleString();
        if (totalPages) totalPages.textContent = this.summaryStats.totalPages.toLocaleString();
        if (totalAccounts) totalAccounts.textContent = this.summaryStats.totalAccounts.toLocaleString();
        
        // Show summary stats
        document.getElementById('summary-stats').classList.remove('hidden');
    }
    
    updateSortIndicators() {
        // Clear all indicators
        document.querySelectorAll('.sort-indicator').forEach(indicator => {
            indicator.textContent = '↕️';
        });
        
        // Update current sort indicator
        const currentField = this.sortBy.split('_')[0];
        const currentDirection = this.sortBy.split('_')[1];
        
        document.querySelectorAll(`[data-sort="${currentField}"] .sort-indicator`).forEach(indicator => {
            indicator.textContent = currentDirection === 'asc' ? '↑' : '↓';
        });
    }

    updateBreadcrumb() {
        ['accounts', 'campaigns', 'adsets', 'posts'].forEach(level => {
            const element = document.getElementById(`breadcrumb-${level}`);
            if (element) element.classList.add('hidden');
        });
        const levels = ['accounts', 'campaigns', 'adsets', 'posts'];
        const currentIndex = levels.indexOf(this.currentLevel);
        for (let i = 0; i <= currentIndex; i++) {
            const element = document.getElementById(`breadcrumb-${levels[i]}`);
            if (element) element.classList.remove('hidden');
        }
    }

    showLoading() {
        const loadingEl = document.getElementById('loading');
        const dataEl = document.getElementById('data-container');
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (dataEl) dataEl.innerHTML = '';
    }
    
    hideLoading() {
        const loadingEl = document.getElementById('loading');
        if (loadingEl) loadingEl.classList.add('hidden');
    }
    
    // Debounce để tránh gọi API quá nhiều
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async loadBusinesses() {
        const cacheKey = 'businesses';
        
        // Kiểm tra cache trước
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            this.renderBusinesses(cached.data || cached);
            if (cached.pagination) {
                this.updateSummaryStats(cached.data || cached, cached.pagination);
            }
            return;
        }
        
        this.showLoading();
        try {
            const result = await this.fetchJson(`/api/hierarchy/businesses`);
            if (result.error) return this.renderError(result.error);
            
            // Lưu vào cache
            this.cache.set(cacheKey, result);
            
            if (result.success && result.data) {
                this.renderBusinesses(result.data);
                this.updateSummaryStats(result.data, result.pagination);
            } else if (Array.isArray(result)) {
                this.renderBusinesses(result);
                this.updateSummaryStats(result);
            } else {
                this.renderError('Không có dữ liệu business managers');
            }
        } catch (error) {
            this.renderError('Lỗi khi tải Business Managers: ' + error.message);
        } finally { this.hideLoading(); }
    }

    renderBusinesses(businesses) {
        if (!businesses || businesses.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có Business Managers nào</p>
                    <p class="text-sm">Chưa có dữ liệu Business Manager được đồng bộ</p>
                </div>`;
            return;
        }
        const html = `
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Tên Business</th>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Xác minh</th>
                            <th class="px-4 py-3 text-left">Tài khoản quảng cáo</th>
                            <th class="px-4 py-3 text-left">Ngày đồng bộ</th>
                            <th class="px-4 py-3 text-left">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${businesses.map(business => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">${business.name}</td>
                                <td class="px-4 py-3 font-mono text-xs">${business.id}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${business.verification_status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${business.verification_status}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        ${business.ad_accounts_count ?? business.adAccounts_count ?? 0}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">${business.created_at ? new Date(business.created_at).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                <td class="px-4 py-3">
                                    <button class="view-accounts text-blue-600 hover:text-blue-800 text-sm font-medium" data-business-id="${business.id}" data-business-name="${business.name}">
                                        Xem tài khoản →
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>`;
        document.getElementById('data-container').innerHTML = html;
        document.querySelectorAll('.view-accounts').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const businessId = e.target.dataset.businessId;
                const businessName = e.target.dataset.businessName;
                this.currentFilters = { businessId, businessName };
                this.navigateToLevel('accounts');
            });
        });
    }

    async loadAccounts(page = 1) {
        this.showLoading();
        try {
            const params = new URLSearchParams({
                businessId: this.currentFilters.businessId,
                page: page,
                per_page: this.perPage,
                search: this.searchTerm,
                status: this.statusFilter,
                sort: this.sortBy,
                group_by: this.groupBy
            });
            
            const result = await this.fetchJson(`/api/hierarchy/accounts?${params.toString()}`);
            if (result.error) return this.renderError(result.error);
            if (result.success && result.data) {
                this.renderAccounts(result.data, result.pagination);
                this.updateSummaryStats(result.data, result.pagination);
            } else if (Array.isArray(result)) {
                this.renderAccounts(result);
                this.updateSummaryStats(result);
            } else {
                this.renderError('Không có dữ liệu accounts');
            }
        } catch (error) { 
            this.renderError('Lỗi khi tải tài khoản quảng cáo: ' + error.message); 
        } finally { 
            this.hideLoading(); 
        }
    }

    renderAccounts(accounts, pagination = null) {
        if (!accounts || accounts.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có tài khoản quảng cáo nào</p>
                    <p class="text-sm">Business Manager này chưa có tài khoản quảng cáo</p>
                </div>`;
            return;
        }
        
        // Calculate totals for breakdown - use pagination total if available
        const totals = {
            campaigns: pagination?.total_campaigns || accounts.reduce((acc, account) => acc + parseInt(account.campaigns_count || 0), 0),
            adsets: pagination?.total_adsets || accounts.reduce((acc, account) => acc + parseInt(account.adsets_count || 0), 0),
            ads: pagination?.total_ads || accounts.reduce((acc, account) => acc + parseInt(account.ads_count || 0), 0),
            posts: pagination?.total_posts || accounts.reduce((acc, account) => acc + parseInt(account.posts_count || 0), 0),
            pages: pagination?.total_pages || 0,
            accounts: pagination?.total_accounts || 0
        };
        
        // Group by status for breakdown
        const statusBreakdown = accounts.reduce((acc, account) => {
            const status = account.account_status || 'UNKNOWN';
            if (!acc[status]) acc[status] = 0;
            acc[status]++;
            return acc;
        }, {});
        
        const html = `
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Tài khoản quảng cáo cho ${this.currentFilters.businessName}</h3>
                <p class="text-sm text-gray-600">Tìm thấy ${accounts.length} tài khoản</p>
                
                ${this.breakdownMode ? `
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Breakdown theo trạng thái</h4>
                        <div class="space-y-1">
                            ${Object.entries(statusBreakdown).map(([status, count]) => `
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">${status}</span>
                                    <span class="font-medium">${count}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Tổng quan</h4>
                                   <div class="space-y-1">
                                       <div class="flex justify-between text-sm">
                                           <span class="text-gray-600">Tổng Campaigns</span>
                                           <span class="font-medium text-blue-600">${totals.campaigns}</span>
                                       </div>
                                       <div class="flex justify-between text-sm">
                                           <span class="text-gray-600">Tổng Ad Sets</span>
                                           <span class="font-medium text-green-600">${totals.adsets}</span>
                                       </div>
                                       <div class="flex justify-between text-sm">
                                           <span class="text-gray-600">Tổng Ads</span>
                                           <span class="font-medium text-purple-600">${totals.ads}</span>
                                       </div>
                                       <div class="flex justify-between text-sm">
                                           <span class="text-gray-600">Tổng Posts</span>
                                           <span class="font-medium text-orange-600">${totals.posts}</span>
                                       </div>
                                       <div class="flex justify-between text-sm">
                                           <span class="text-gray-600">Tổng Pages</span>
                                           <span class="font-medium text-pink-600">${totals.pages}</span>
                                       </div>
                                       <div class="flex justify-between text-sm">
                                           <span class="text-gray-600">Tổng Accounts</span>
                                           <span class="font-medium text-indigo-600">${totals.accounts}</span>
                                       </div>
                                   </div>
                    </div>
                </div>
                ` : ''}
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="name">
                                Tên tài khoản
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left">ID tài khoản</th>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="status">
                                Trạng thái
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left">Campaigns</th>
                            <th class="px-4 py-3 text-left">Ad Sets</th>
                            <th class="px-4 py-3 text-left">Ads</th>
                            <th class="px-4 py-3 text-left">Posts</th>
                            <th class="px-4 py-3 text-left">Pages</th>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="spend">
                                Chi phí
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="created_at">
                                Ngày đồng bộ
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${accounts.map(account => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full mr-2 ${account.account_status === 'ACTIVE' ? 'bg-green-500' : 'bg-gray-400'}"></div>
                                        ${account.name}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">ID: ${account.account_id}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">${account.account_id}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${account.account_status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${account.account_status}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ${account.campaigns_count || 0}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Campaigns</div>
                                        <div class="text-xs text-gray-400 mt-1">Tổng: ${account.campaigns_count || 0}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ${account.adsets_count || 0}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Ad Sets</div>
                                        <div class="text-xs text-gray-400 mt-1">Tổng: ${account.adsets_count || 0}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            ${account.ads_count || 0}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Ads</div>
                                        <div class="text-xs text-gray-400 mt-1">Tổng: ${account.ads_count || 0}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            ${account.posts_count || 0}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Posts</div>
                                        <div class="text-xs text-gray-400 mt-1">Tổng: ${account.posts_count || 0}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                            ${account.pages_count || 0}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Pages</div>
                                        <div class="text-xs text-gray-400 mt-1">Tổng: ${account.pages_count || 0}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-center">
                                        <span class="text-sm font-semibold text-green-600">
                                            ${(account.total_spend || 0).toLocaleString('vi-VN')} VND
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Chi phí</div>
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    ${account.created_at ? new Date(account.created_at).toLocaleDateString('vi-VN') : 'N/A'}
                                </td>
                                <td class="px-4 py-3">
                                    <button class="view-campaigns text-blue-600 hover:text-blue-800 text-sm font-medium" data-account-id="${account.id}" data-account-name="${account.name}">
                                        📊 Chiến dịch
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                        <!-- Totals Row -->
                        <tr class="border-t-2 border-gray-300 bg-gray-100 font-semibold">
                            <td class="px-4 py-3">TỔNG CỘNG</td>
                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3 text-blue-600">${totals.campaigns}</td>
                            <td class="px-4 py-3 text-green-600">${totals.adsets}</td>
                            <td class="px-4 py-3 text-purple-600">${totals.ads}</td>
                            <td class="px-4 py-3 text-orange-600">${totals.posts}</td>
                            <td class="px-4 py-3 text-pink-600">${totals.pages}</td>
                            <td class="px-4 py-3 text-green-600">${(pagination?.total_spend ?? accounts.reduce((s,a)=> s + (a.total_spend||0),0)).toLocaleString('vi-VN')} VND</td>
                            
                            <td class="px-4 py-3">-</td>
                            <td class="px-4 py-3">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            ${pagination ? this.renderPagination(pagination, 'accounts') : ''}`;
        document.getElementById('data-container').innerHTML = html;
        document.querySelectorAll('.view-campaigns').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const accountId = e.target.dataset.accountId;
                const accountName = e.target.dataset.accountName;
                this.currentFilters = { ...this.currentFilters, accountId, accountName };
                this.navigateToLevel('campaigns');
            });
        });
        
        // Add pagination event listeners
        document.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.dataset.page);
                const level = e.target.dataset.level;
                this.loadPage(page, level);
            });
        });
        
        // Add sortable header event listeners
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', (e) => {
                const sortField = e.currentTarget.dataset.sort;
                const currentSort = this.sortBy;
                const currentField = currentSort.split('_')[0];
                const currentDirection = currentSort.split('_')[1];
                
                let newDirection = 'asc';
                if (currentField === sortField && currentDirection === 'asc') {
                    newDirection = 'desc';
                }
                
                this.sortBy = `${sortField}_${newDirection}`;
                // no external control to reflect; indicators will update below
                this.applyFilters();
            });
        });
        
        // Update sort indicators
        this.updateSortIndicators();
    }

    async loadCampaigns(page = 1) {
        this.showLoading();
        try {
            const params = new URLSearchParams({
                accountId: this.currentFilters.accountId,
                page: page,
                per_page: this.perPage,
                search: this.searchTerm,
                status: this.statusFilter,
                objective: this.objectiveFilter,
                sort: this.sortBy,
                group_by: this.groupBy
            });
            
            const result = await this.fetchJson(`/api/hierarchy/campaigns?${params.toString()}`);
            if (result.error) return this.renderError(result.error);
            if (result.success && result.data) {
                this.renderCampaigns(result.data, result.pagination);
                this.updateSummaryStats(result.data, result.pagination);
            } else if (Array.isArray(result)) {
                this.renderCampaigns(result);
                this.updateSummaryStats(result);
            } else {
                this.renderError('Không có dữ liệu campaigns');
            }
        } catch (error) { 
            this.renderError('Lỗi khi tải campaigns: ' + error.message); 
        } finally { 
            this.hideLoading(); 
        }
    }

    renderCampaigns(campaigns, pagination = null) {
        if (!campaigns || campaigns.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có campaigns nào</p>
                    <p class="text-sm">Tài khoản này chưa có chiến dịch nào được tạo</p>
                </div>`;
            return;
        }
        
        // Calculate totals for breakdown - use pagination total if available
        const totals = {
            adSets: pagination?.total_adsets || campaigns.reduce((acc, campaign) => acc + parseInt(campaign.ad_sets_count ?? 0), 0),
            ads: pagination?.total_ads || campaigns.reduce((acc, campaign) => acc + parseInt(campaign.ads_count ?? 0), 0),
            posts: pagination?.total_posts || campaigns.reduce((acc, campaign) => acc + parseInt(campaign.posts_count ?? 0), 0),
            pages: pagination?.total_pages || campaigns.reduce((acc, campaign) => acc + parseInt(campaign.pages_count ?? 0), 0)
        };
        
        // Group by status for breakdown
        const statusBreakdown = campaigns.reduce((acc, campaign) => {
            const status = campaign.effective_status || 'UNKNOWN';
            if (!acc[status]) acc[status] = 0;
            acc[status]++;
            return acc;
        }, {});
        
        // Group by objective for breakdown
        const objectiveBreakdown = campaigns.reduce((acc, campaign) => {
            const objective = campaign.objective || 'UNKNOWN';
            if (!acc[objective]) acc[objective] = 0;
            acc[objective]++;
            return acc;
        }, {});
        
        const html = `
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Chiến dịch cho ${this.currentFilters.accountName}</h3>
                <p class="text-sm text-gray-600">Tìm thấy ${campaigns.length} chiến dịch</p>
                
                ${this.breakdownMode ? `
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Breakdown theo trạng thái</h4>
                        <div class="space-y-1">
                            ${Object.entries(statusBreakdown).map(([status, count]) => `
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">${status}</span>
                                    <span class="font-medium">${count}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Breakdown theo mục tiêu</h4>
                        <div class="space-y-1">
                            ${Object.entries(objectiveBreakdown).map(([objective, count]) => `
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">${objective}</span>
                                    <span class="font-medium">${count}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Tổng quan</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tổng Ad Sets</span>
                                <span class="font-medium text-blue-600">${totals.adSets}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tổng Ads</span>
                                <span class="font-medium text-green-600">${totals.ads}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tổng Posts</span>
                                <span class="font-medium text-purple-600">${totals.posts}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tổng Pages</span>
                                <span class="font-medium text-orange-600">${totals.pages}</span>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="name">
                                Tên chiến dịch
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="spend">
                                Chi phí
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left">Impr</th>
                            <th class="px-4 py-3 text-left">Clicks</th>
                            <th class="px-4 py-3 text-left">Reach</th>
                            <th class="px-4 py-3 text-left">CTR</th>
                            <th class="px-4 py-3 text-left">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${campaigns.map(campaign => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full mr-2 ${campaign.effective_status === 'ACTIVE' ? 'bg-green-500' : 'bg-gray-400'}"></div>
                                        ${campaign.name || 'Không có tên'}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">ID: ${campaign.id}</div>
                                </td>
                                <td class="px-4 py-3"><span class="text-sm font-semibold text-green-600">${(campaign.total_spend || 0).toLocaleString('vi-VN')} VND</span></td>
                                <td class="px-4 py-3">${Number(campaign.total_impressions || 0).toLocaleString('vi-VN')}</td>
                                <td class="px-4 py-3">${Number(campaign.total_clicks || 0).toLocaleString('vi-VN')}</td>
                                <td class="px-4 py-3">${Number(campaign.total_reach || 0).toLocaleString('vi-VN')}</td>
                                <td class="px-4 py-3">${campaign.ctr ? Number(campaign.ctr).toFixed(2) : '0.00'}%</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col space-y-1">
                                        <button class="view-adsets text-blue-600 hover:text-blue-800 text-xs font-medium" data-campaign-id="${campaign.id}" data-campaign-name="${campaign.name || 'Unknown'}">
                                            📊 Ad Sets
                                        </button>
                                        <button class="view-posts text-green-600 hover:text-green-800 text-xs font-medium" data-campaign-id="${campaign.id}" data-campaign-name="${campaign.name || 'Unknown'}">
                                            📝 Posts
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                        <!-- Totals Row -->
                        <tr class="border-t-2 border-gray-300 bg-gray-100 font-semibold">
                            <td class="px-4 py-3">TỔNG CỘNG</td>
                            <td class="px-4 py-3 text-green-600">${(pagination?.total_spend ?? campaigns.reduce((s,c)=> s + (c.total_spend||0),0)).toLocaleString('vi-VN')} VND</td>
                            <td class="px-4 py-3 text-blue-600">${(pagination?.total_impressions ?? campaigns.reduce((s,c)=> s + (c.total_impressions||0),0)).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-purple-600">${(pagination?.total_clicks ?? campaigns.reduce((s,c)=> s + (c.total_clicks||0),0)).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-indigo-600">${(pagination?.total_reach ?? campaigns.reduce((s,c)=> s + (c.total_reach||0),0)).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-orange-600">${(pagination?.total_ctr ?? (()=>{const i=(pagination?.total_impressions)||campaigns.reduce((s,c)=>s+(c.total_impressions||0),0);const ck=(pagination?.total_clicks)||campaigns.reduce((s,c)=>s+(c.total_clicks||0),0);return i>0?((ck/i)*100).toFixed(2):'0.00';})())}%</td>
                            <td class="px-4 py-3">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            ${pagination ? this.renderPagination(pagination, 'campaigns') : ''}`;
        document.getElementById('data-container').innerHTML = html;
        document.querySelectorAll('.view-adsets').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const campaignId = e.target.dataset.campaignId;
                const campaignName = e.target.dataset.campaignName;
                this.currentFilters = { ...this.currentFilters, campaignId, campaignName };
                this.navigateToLevel('adsets');
            });
        });
        
        // Add pagination event listeners
        document.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.dataset.page);
                const level = e.target.dataset.level;
                this.loadPage(page, level);
            });
        });
        
        // Add sortable header event listeners
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', (e) => {
                const sortField = e.currentTarget.dataset.sort;
                const currentSort = this.sortBy;
                const currentField = currentSort.split('_')[0];
                const currentDirection = currentSort.split('_')[1];
                
                let newDirection = 'asc';
                if (currentField === sortField && currentDirection === 'asc') {
                    newDirection = 'desc';
                }
                
                this.sortBy = `${sortField}_${newDirection}`;
                // no external control to reflect; indicators will update below
                this.applyFilters();
            });
        });
        
        document.querySelectorAll('.view-posts').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const campaignId = e.target.dataset.campaignId;
                const campaignName = e.target.dataset.campaignName;
                this.currentFilters = { ...this.currentFilters, campaignId, campaignName };
                this.navigateToLevel('posts');
            });
        });
        
        // Update sort indicators
        this.updateSortIndicators();
    }

    async loadAdSets(page = 1) {
        this.showLoading();
        try {
            const params = new URLSearchParams({
                campaignId: this.currentFilters.campaignId,
                page: page,
                per_page: this.perPage,
                search: this.searchTerm,
                status: this.statusFilter,
                sort: this.sortBy,
                group_by: this.groupBy
            });
            
            const result = await this.fetchJson(`/api/hierarchy/adsets?${params.toString()}`);
            if (result.error) return this.renderError('Lỗi khi tải Ad Sets: ' + result.error);
            if (result.success && result.data) {
                this.renderAdSets(result.data, result.pagination);
                this.updateSummaryStats(result.data, result.pagination);
            } else if (Array.isArray(result)) {
                this.renderAdSets(result);
                this.updateSummaryStats(result);
            } else {
                this.renderError('Error loading ad sets');
            }
        } catch (error) { 
            this.renderError('Error loading ad sets'); 
        } finally { 
            this.hideLoading(); 
        }
    }
    renderAdSets(adsets, pagination = null) {
        if (!adsets || adsets.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có Ad Sets nào</p>
                    <p class="text-sm">Chiến dịch này chưa có bộ quảng cáo nào</p>
                </div>`;
            return;
        }
        
        // Calculate totals
        const totals = adsets.reduce((acc, adset) => {
            acc.spend += parseFloat(adset.total_spend ?? adset.kpi?.spend ?? 0);
            acc.impressions += parseInt(adset.total_impressions ?? adset.kpi?.impressions ?? 0);
            acc.clicks += parseInt(adset.total_clicks ?? adset.kpi?.clicks ?? 0);
            acc.reach += parseInt(adset.total_reach ?? adset.kpi?.reach ?? 0);
            acc.ads += parseInt(adset.ads_count ?? 0);
            return acc;
        }, { spend: 0, impressions: 0, clicks: 0, reach: 0, ads: 0 });
        
        const avgCtr = totals.impressions > 0 ? (totals.clicks / totals.impressions) * 100 : 0;
        const avgCpc = totals.clicks > 0 ? totals.spend / totals.clicks : 0;
        const avgCpm = totals.impressions > 0 ? (totals.spend / totals.impressions) * 1000 : 0;
        
        const html = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Ad Sets cho ${this.currentFilters.campaignName}</h3>
                <p class="text-sm text-gray-600">Tìm thấy ${adsets.length} bộ quảng cáo</p>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="name">
                                Tên Ad Set
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left">Chi phí</th>
                            <th class="px-4 py-3 text-left">Impr</th>
                            <th class="px-4 py-3 text-left">Clicks</th>
                            <th class="px-4 py-3 text-left">Reach</th>
                            <th class="px-4 py-3 text-left">CTR</th>
                            <th class="px-4 py-3 text-left">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${adsets.map(adset => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">${adset.name}</td>
                                <td class="px-4 py-3"><span class="text-sm font-semibold text-green-600">${Number(adset.total_spend ?? adset.kpi?.spend ?? 0).toLocaleString('vi-VN')} VND</span></td>
                                <td class="px-4 py-3">${Number(adset.total_impressions ?? adset.kpi?.impressions ?? 0).toLocaleString('vi-VN')}</td>
                                <td class="px-4 py-3">${Number(adset.total_clicks ?? adset.kpi?.clicks ?? 0).toLocaleString('vi-VN')}</td>
                                <td class="px-4 py-3">${Number(adset.total_reach ?? adset.kpi?.reach ?? 0).toLocaleString('vi-VN')}</td>
                                <td class="px-4 py-3">${adset.avg_ctr ? Number(adset.avg_ctr).toFixed(2) : '0.00'}%</td>
                                <td class="px-4 py-3">
                                    <button class="view-posts text-blue-600 hover:text-blue-800 text-sm font-medium" data-adset-id="${adset.id}" data-adset-name="${adset.name}">
                                        Xem Posts →
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                        <!-- Totals Row -->
                        <tr class="border-t-2 border-gray-300 bg-gray-100 font-semibold">
                            <td class="px-4 py-3">TỔNG CỘNG</td>
                            <td class="px-4 py-3 text-green-600">${(pagination?.total_spend ?? totals.spend).toLocaleString('vi-VN')} VND</td>
                            <td class="px-4 py-3 text-blue-600">${(pagination?.total_impressions ?? totals.impressions).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-purple-600">${(pagination?.total_clicks ?? totals.clicks).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-indigo-600">${(pagination?.total_reach ?? totals.reach).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-orange-600">${(pagination?.total_ctr ?? (totals.impressions>0?((totals.clicks/totals.impressions)*100):0)).toLocaleString('vi-VN', {maximumFractionDigits:2, minimumFractionDigits:2})}%</td>
                            <td class="px-4 py-3">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            ${pagination ? this.renderPagination(pagination, 'adsets') : ''}`;
        document.getElementById('data-container').innerHTML = html;
        document.querySelectorAll('.view-posts').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const adsetId = e.target.dataset.adsetId;
                const adsetName = e.target.dataset.adsetName;
                this.currentFilters = { ...this.currentFilters, adsetId, adsetName };
                this.navigateToLevel('posts');
            });
        });
        
        // Add pagination event listeners
        document.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.dataset.page);
                const level = e.target.dataset.level;
                this.loadPage(page, level);
            });
        });
        
        // Add sortable header event listeners
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', (e) => {
                const sortField = e.currentTarget.dataset.sort;
                const currentSort = this.sortBy;
                const currentField = currentSort.split('_')[0];
                const currentDirection = currentSort.split('_')[1];
                
                let newDirection = 'asc';
                if (currentField === sortField && currentDirection === 'asc') {
                    newDirection = 'desc';
                }
                
                this.sortBy = `${sortField}_${newDirection}`;
                document.getElementById('filter-sort').value = this.sortBy;
                this.applyFilters();
            });
        });
        
        // Update sort indicators
        this.updateSortIndicators();
    }

    async loadPosts(page = 1) {
        this.showLoading();
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: this.perPage,
                search: this.searchTerm,
                status: this.statusFilter,
                sort: this.sortBy,
                group_by: this.groupBy
            });
            
            if (this.currentFilters.adsetId) params.append('adsetId', this.currentFilters.adsetId);
            else if (this.currentFilters.campaignId) params.append('campaignId', this.currentFilters.campaignId);
            else if (this.currentFilters.accountId) params.append('accountId', this.currentFilters.accountId);
            
            const result = await this.fetchJson(`/api/hierarchy/posts?${params.toString()}`);
            if (result.error) return this.renderError('Lỗi khi tải Posts: ' + result.error);
            
            if (result.success && result.data) {
                this.renderPosts(result.data, result.pagination);
                this.updateSummaryStats(result.data, result.pagination);
            } else if (Array.isArray(result)) {
                this.renderPosts(result);
                this.updateSummaryStats(result);
            } else {
                this.renderError('Không có dữ liệu posts');
            }
        } catch (error) { 
            this.renderError('Error loading posts'); 
        } finally { 
            this.hideLoading(); 
        }
    }
    renderPosts(posts, pagination = null) {
        if (!posts || posts.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có Posts nào</p>
                    <p class="text-sm">Chưa có bài đăng nào được tìm thấy</p>
                </div>`;
            return;
        }
        
        const contextName = this.currentFilters.adsetName || this.currentFilters.campaignName || this.currentFilters.accountName;
        
        // Calculate totals
        const totals = posts.reduce((acc, post) => {
            acc.likes += parseInt(post.post_likes || 0);
            acc.shares += parseInt(post.post_shares || 0);
            acc.comments += parseInt(post.post_comments || 0);
            acc.impressions += parseInt(post.ad_impressions || 0);
            acc.reach += parseInt(post.ad_reach || 0);
            acc.clicks += parseInt(post.ad_clicks || 0);
            return acc;
        }, { likes: 0, shares: 0, comments: 0, impressions: 0, reach: 0, clicks: 0 });
        
        const totalEngagement = totals.likes + totals.shares + totals.comments;
        
        const html = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Posts cho ${contextName}</h3>
                <p class="text-sm text-gray-600 mt-1">Tìm thấy ${posts.length} bài đăng</p>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="name">
                                Post
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 sortable" data-sort="page_id">
                                Page
                                <span class="sort-indicator ml-1">↕️</span>
                            </th>
                            <th class="px-4 py-3 text-left">Chi phí</th>
                            <th class="px-4 py-3 text-left">Impr</th>
                            <th class="px-4 py-3 text-left">Clicks</th>
                            <th class="px-4 py-3 text-left">Reach</th>
                            <th class="px-4 py-3 text-left">CTR</th>
                            <th class="px-4 py-3 text-left">Liên kết</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${posts.map(post => {
                            const spend = (post.ad_spend || 0);
                            const impressions = (post.ad_impressions || 0);
                            const reach = (post.ad_reach || 0);
                            const clicks = (post.ad_clicks || 0);
                            const postIdText = post.post_id ? (typeof post.post_id === 'string' ? post.post_id : JSON.stringify(post.post_id)) : '';
                            const pageIdText = post.page_id ? (typeof post.page_id === 'string' ? post.page_id : JSON.stringify(post.page_id)) : '';
                            const fallbackLink = (pageIdText && postIdText) ? `https://www.facebook.com/${pageIdText.replace(/\"/g,'').replace(/"/g,'')}/posts/${postIdText.replace(/\"/g,'').replace(/"/g,'')}` : null;
                            const permalink = post.post_permalink_url || fallbackLink;
                            return `
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium">${permalink ? `<a href=\"${permalink}\" target=\"_blank\" class=\"text-blue-600 hover:text-blue-800\">${post.post_message ? (post.post_message.length>80?post.post_message.substring(0,80)+'...':post.post_message) : (post.creative_link_message || post.creative_link_name || 'No message')}</a>` : (post.post_message ? (post.post_message.length>80?post.post_message.substring(0,80)+'...':post.post_message) : (post.creative_link_message || post.creative_link_name || 'No message'))}</div>
                                        ${post.post_id ? `<div class=\"text-xs text-gray-500 font-mono\">${postIdText}</div>` : ''}
                                        ${post.post_created_time ? `<div class=\"text-xs text-gray-500\">${new Date(post.post_created_time).toLocaleDateString('vi-VN')}</div>` : ''}
                                    </td>
                                    <td class="px-4 py-3">
                                        ${pageIdText ? `<a href=\"https://www.facebook.com/${pageIdText.replace(/\\\"/g,'').replace(/"/g,'')}\" target=\"_blank\" class=\"text-blue-600 hover:text-blue-800 text-xs\">${pageIdText}</a>` : '<div class="text-xs text-gray-500 font-mono">-</div>'}
                                    </td>
                                    <td class="px-4 py-3"><div class="text-sm font-medium">${spend.toLocaleString('vi-VN')} đ</div></td>
                                    <td class="px-4 py-3">${Number(impressions).toLocaleString('vi-VN')}</td>
                                    <td class="px-4 py-3">${Number(clicks).toLocaleString('vi-VN')}</td>
                                    <td class="px-4 py-3">${Number(reach).toLocaleString('vi-VN')}</td>
                                    <td class="px-4 py-3">${impressions>0 ? ((clicks/impressions)*100).toFixed(2) : '0.00'}%</td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            ${permalink ? `<a href=\"${permalink}\" target=\"_blank\" class=\"text-blue-600 hover:text-blue-800 text-xs block\">🔗 View Post</a>` : ''}
                                            ${post.creative_link_url ? `<a href=\"${post.creative_link_url}\" target=\"_blank\" class=\"text-blue-600 hover:text-blue-800 text-xs block\">🌐 Link</a>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                        <!-- Totals Row -->
                        <tr class="border-t-2 border-gray-300 bg-gray-100 font-semibold">
                            <td class="px-4 py-3">TỔNG CỘNG</td>
                            <td class="px-4 py-3 text-green-600">${(pagination?.total_spend ?? posts.reduce((s,p)=> s + (p.ad_spend||0),0)).toLocaleString('vi-VN')} đ</td>
                            <td class="px-4 py-3 text-blue-600">${(pagination?.total_impressions ?? totals.impressions).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-purple-600">${(pagination?.total_clicks ?? totals.clicks).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-indigo-600">${(pagination?.total_reach ?? totals.reach).toLocaleString('vi-VN')}</td>
                            <td class="px-4 py-3 text-orange-600">${(pagination?.total_ctr ?? (totals.impressions>0?((totals.clicks/totals.impressions)*100):0)).toLocaleString('vi-VN', {maximumFractionDigits:2, minimumFractionDigits:2})}%</td>
                            <td class="px-4 py-3">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            ${pagination ? this.renderPagination(pagination, 'posts') : ''}`;
        document.getElementById('data-container').innerHTML = html;
        
        // Add pagination event listeners
        document.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.dataset.page);
                const level = e.target.dataset.level;
                this.loadPage(page, level);
            });
        });
        
        // Add sortable header event listeners
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', (e) => {
                const sortField = e.currentTarget.dataset.sort;
                const currentSort = this.sortBy;
                const currentField = currentSort.split('_')[0];
                const currentDirection = currentSort.split('_')[1];
                
                let newDirection = 'asc';
                if (currentField === sortField && currentDirection === 'asc') {
                    newDirection = 'desc';
                }
                
                this.sortBy = `${sortField}_${newDirection}`;
                document.getElementById('filter-sort').value = this.sortBy;
                this.applyFilters();
            });
        });
        
        // Update sort indicators
        this.updateSortIndicators();
    }

    loadPage(page, level) {
        switch(level) {
            case 'accounts':
                this.loadAccounts(page);
                break;
            case 'campaigns':
                this.loadCampaigns(page);
                break;
            case 'adsets':
                this.loadAdSets(page);
                break;
            case 'ads':
                this.loadAds(page);
                break;
            case 'posts':
                this.loadPosts(page);
                break;
        }
    }

    renderPagination(pagination, level) {
        if (!pagination || pagination.last_page <= 1) return '';
        
        const { current_page, last_page, total, from, to } = pagination;
        
        let paginationHtml = `
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Hiển thị ${from} đến ${to} trong tổng số ${total} kết quả
                </div>
                <div class="flex items-center space-x-2">
        `;
        
        // Previous button
        if (current_page > 1) {
            paginationHtml += `
                <button class="pagination-btn px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50" 
                        data-page="${current_page - 1}" data-level="${level}">
                    Trước
                </button>
            `;
        }
        
        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === current_page;
            paginationHtml += `
                <button class="pagination-btn px-3 py-2 text-sm font-medium ${isActive ? 'text-white bg-blue-600 border-blue-600' : 'text-gray-500 bg-white border-gray-300'} border rounded-md hover:bg-gray-50" 
                        data-page="${i}" data-level="${level}">
                    ${i}
                </button>
            `;
        }
        
        // Next button
        if (current_page < last_page) {
            paginationHtml += `
                <button class="pagination-btn px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50" 
                        data-page="${current_page + 1}" data-level="${level}">
                    Sau
                </button>
            `;
        }
        
        paginationHtml += `
                </div>
            </div>
        `;
        
        return paginationHtml;
    }

    renderError(message) {
        const dataContainer = document.getElementById('data-container');
        if (!dataContainer) return;
        
        dataContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="text-red-600 mb-2">
                    <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-gray-600">${message}</p>
                <button onclick="window.facebookHierarchy?.loadBusinesses()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Thử lại
                </button>
            </div>`;
    }
}

function initFacebookHierarchy(){
    // Chỉ khởi tạo nếu chưa có instance
    if (!window.facebookHierarchy) {
        window.facebookHierarchy = new FacebookHierarchy();
    }
}

// Lazy load để tránh block Livewire navigation
document.addEventListener('DOMContentLoaded', () => {
    // Delay để Livewire hoàn thành navigation
    setTimeout(initFacebookHierarchy, 200);
});

// SPA re-init for wire:navigate với delay
window.addEventListener('livewire:navigated', () => {
    // Reset instance cũ nếu có
    if (window.facebookHierarchy) {
        window.facebookHierarchy = null;
    }
    // Delay để tránh conflict
    setTimeout(initFacebookHierarchy, 150);
});
</script>
@endpush
    </div>
</x-layouts.app>


