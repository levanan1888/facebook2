<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-4 py-3 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Phân cấp Facebook Ads</h2>
        <p class="text-sm text-gray-600 mt-1">Khám phá cấu trúc dữ liệu Facebook: Business Manager → Tài khoản quảng cáo → Chiến dịch → Bộ quảng cáo → Bài đăng</p>
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
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadBusinesses();
        
        // Auto test API after a short delay
        setTimeout(() => {
            testAPI();
        }, 1000);
    }

    bindEvents() {
        // Breadcrumb navigation
        document.querySelectorAll('.hierarchy-nav').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const level = e.target.dataset.level;
                this.navigateToLevel(level);
            });
        });
    }

    async navigateToLevel(level) {
        this.currentLevel = level;
        this.updateBreadcrumb();
        
        switch(level) {
            case 'businesses':
                this.loadBusinesses();
                break;
            case 'accounts':
                this.loadAccounts();
                break;
            case 'campaigns':
                this.loadCampaigns();
                break;
            case 'adsets':
                this.loadAdSets();
                break;
            case 'posts':
                this.loadPosts();
                break;
        }
    }

    updateBreadcrumb() {
        
        // Hide all breadcrumb items except businesses
        ['accounts', 'campaigns', 'adsets', 'posts'].forEach(level => {
            const element = document.getElementById(`breadcrumb-${level}`);
            if (element) {
                element.classList.add('hidden');
            }
        });

        // Show breadcrumb items up to current level
        const levels = ['accounts', 'campaigns', 'adsets', 'posts'];
        const currentIndex = levels.indexOf(this.currentLevel);
        
        for (let i = 0; i <= currentIndex; i++) {
            const element = document.getElementById(`breadcrumb-${levels[i]}`);
            if (element) {
                element.classList.remove('hidden');
            }
        }
    }

    showLoading() {
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('data-container').innerHTML = '';
    }

    hideLoading() {
        document.getElementById('loading').classList.add('hidden');
    }

    async loadBusinesses() {
        this.showLoading();
        try {
            const response = await fetch('/api/hierarchy/businesses');
            const data = await response.json();
            
            if (data.error) {
                this.renderError(data.error);
                return;
            }
            
            this.renderBusinesses(data);
        } catch (error) {
            this.renderError('Lỗi khi tải Business Managers: ' + error.message);
        } finally {
            this.hideLoading();
        }
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

                </div>
            `;
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
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">
                                    <button class="view-accounts text-blue-600 hover:text-blue-800 text-sm font-medium" data-business-id="${business.id}" data-business-name="${business.name}">
                                        Xem tài khoản →
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('data-container').innerHTML = html;
        
        // Bind view accounts buttons
        document.querySelectorAll('.view-accounts').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const businessId = e.target.dataset.businessId;
                const businessName = e.target.dataset.businessName;
                this.currentFilters = { businessId, businessName };
                this.navigateToLevel('accounts');
            });
        });
    }

    async loadAccounts() {
        this.showLoading();
        try {
            const response = await fetch(`/api/hierarchy/accounts?businessId=${this.currentFilters.businessId}`);
            const data = await response.json();
            
            if (data.error) {
                this.renderError(data.error);
                return;
            }
            
            this.renderAccounts(data);
        } catch (error) {
            this.renderError('Lỗi khi tải tài khoản quảng cáo: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    renderAccounts(accounts) {

        
        if (!accounts || accounts.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có tài khoản quảng cáo nào</p>
                    <p class="text-sm">Business Manager này chưa có tài khoản quảng cáo</p>

                </div>
            `;
            return;
        }

        const html = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tài khoản quảng cáo cho ${this.currentFilters.businessName}</h3>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Tên tài khoản</th>
                            <th class="px-4 py-3 text-left">ID tài khoản</th>
                            <th class="px-4 py-3 text-left">Trạng thái</th>
                            <th class="px-4 py-3 text-left">Spend (tháng)</th>
                            <th class="px-4 py-3 text-left">Impr</th>
                            <th class="px-4 py-3 text-left">Clicks</th>
                            <th class="px-4 py-3 text-left">Chiến dịch</th>
                            <th class="px-4 py-3 text-left">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${accounts.map(account => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">${account.name}</td>
                                <td class="px-4 py-3 font-mono text-xs">${account.account_id}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${account.account_status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${account.account_status}
                                    </span>
                                </td>
                                <td class="px-4 py-3">${(account.kpi?.spend ?? 0).toLocaleString()}</td>
                                <td class="px-4 py-3">${(account.kpi?.impressions ?? 0).toLocaleString()}</td>
                                <td class="px-4 py-3">${(account.kpi?.clicks ?? 0).toLocaleString()}</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">
                                    <button class="view-campaigns text-blue-600 hover:text-blue-800 text-sm font-medium" data-account-id="${account.id}" data-account-name="${account.name}">
                                        Xem chiến dịch →
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('data-container').innerHTML = html;
        
        // Bind view campaigns buttons
        document.querySelectorAll('.view-campaigns').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const accountId = e.target.dataset.accountId;
                const accountName = e.target.dataset.accountName;
                this.currentFilters = { ...this.currentFilters, accountId, accountName };
                this.navigateToLevel('campaigns');
            });
        });
    }

    async loadCampaigns() {
        this.showLoading();
        try {
            const ym = new Date();
            const month = ym.toISOString().slice(0,7);
            const response = await fetch(`/api/hierarchy/campaigns?accountId=${this.currentFilters.accountId}&month=${month}`);
            const result = await response.json();
            
            if (result.error) {
                this.renderError(result.error);
                return;
            }
            
            if (result.success && result.data) {
                this.renderCampaigns(result.data);
            } else {
                this.renderError('Không có dữ liệu campaigns');
            }
        } catch (error) {
            this.renderError('Lỗi khi tải campaigns: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    renderCampaigns(campaigns) {

        
        if (!campaigns || campaigns.length === 0) {
            document.getElementById('data-container').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Không có campaigns nào</p>
                    <p class="text-sm">Tài khoản này chưa có chiến dịch nào được tạo</p>

                </div>
            `;
            return;
        }

        const html = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Chiến dịch cho ${this.currentFilters.accountName}</h3>
                <p class="text-sm text-gray-600">Tìm thấy ${campaigns.length} chiến dịch</p>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Tên chiến dịch</th>
                            <th class="px-4 py-3 text-left">Trạng thái</th>
                            <th class="px-4 py-3 text-left">Mục tiêu</th>
                            <th class="px-4 py-3 text-left">Spend (tháng)</th>
                            <th class="px-4 py-3 text-left">Impr</th>
                            <th class="px-4 py-3 text-left">Clicks</th>
                            <th class="px-4 py-3 text-left">CTR</th>
                            <th class="px-4 py-3 text-left">Ad Sets</th>
                            <th class="px-4 py-3 text-left">Ngày bắt đầu</th>
                            <th class="px-4 py-3 text-left">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${campaigns.map(campaign => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">${campaign.name || 'Không có tên'}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${campaign.effective_status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${campaign.effective_status || 'UNKNOWN'}
                                    </span>
                                </td>
                                <td class="px-4 py-3">${campaign.objective || 'Không xác định'}</td>
                                <td class="px-4 py-3">${(campaign.kpi?.spend ?? 0).toLocaleString()}</td>
                                <td class="px-4 py-3">${(campaign.kpi?.impressions ?? 0).toLocaleString()}</td>
                                <td class="px-4 py-3">${(campaign.kpi?.clicks ?? 0).toLocaleString()}</td>
                                <td class="px-4 py-3">${campaign.kpi?.ctr ? Number(campaign.kpi.ctr).toFixed(2) + '%' : '-'}</td>
                                <td class="px-4 py-3">${campaign.ad_sets_count || 0}</td>
                                <td class="px-4 py-3">${campaign.start_time ? new Date(campaign.start_time).toLocaleDateString('vi-VN') : '-'}</td>
                                <td class="px-4 py-3">
                                    <button class="view-adsets text-blue-600 hover:text-blue-800 text-sm font-medium" data-campaign-id="${campaign.id}" data-campaign-name="${campaign.name || 'Unknown'}">
                                        Xem Ad Sets →
                                    </button>
                                    <button class="view-posts ml-2 text-green-600 hover:text-green-800 text-sm font-medium" data-campaign-id="${campaign.id}" data-campaign-name="${campaign.name || 'Unknown'}">
                                        Xem Posts
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('data-container').innerHTML = html;
        
        // Bind buttons
        document.querySelectorAll('.view-adsets').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const campaignId = e.target.dataset.campaignId;
                const campaignName = e.target.dataset.campaignName;
                this.currentFilters = { ...this.currentFilters, campaignId, campaignName };
                this.navigateToLevel('adsets');
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
    }

    async loadAdSets() {
        this.showLoading();
        try {
            const response = await fetch(`/api/hierarchy/adsets?campaignId=${this.currentFilters.campaignId}`);
            const data = await response.json();
            this.renderAdSets(data);
        } catch (error) {
            this.renderError('Error loading ad sets');
        } finally {
            this.hideLoading();
        }
    }

    renderAdSets(adsets) {
        const html = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Ad Sets for ${this.currentFilters.campaignName}</h3>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Ad Set Name</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Optimization Goal</th>
                            <th class="px-4 py-3 text-left">Ads</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${adsets.map(adset => `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">${adset.name}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${adset.status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${adset.status}
                                    </span>
                                </td>
                                <td class="px-4 py-3">${adset.optimization_goal}</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">
                                    <button class="view-posts text-blue-600 hover:text-blue-800 text-sm font-medium" data-adset-id="${adset.id}" data-adset-name="${adset.name}">
                                        View Posts →
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('data-container').innerHTML = html;
        
        // Bind view posts buttons
        document.querySelectorAll('.view-posts').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const adsetId = e.target.dataset.adsetId;
                const adsetName = e.target.dataset.adsetName;
                this.currentFilters = { ...this.currentFilters, adsetId, adsetName };
                this.navigateToLevel('posts');
            });
        });
    }

    async loadPosts() {
        this.showLoading();
        try {
            let url = '/api/hierarchy/posts?';
            const params = new URLSearchParams();
            
            if (this.currentFilters.adsetId) {
                params.append('adsetId', this.currentFilters.adsetId);
            } else if (this.currentFilters.campaignId) {
                params.append('campaignId', this.currentFilters.campaignId);
            } else if (this.currentFilters.accountId) {
                params.append('accountId', this.currentFilters.accountId);
            }
            
            const response = await fetch(url + params.toString());
            const data = await response.json();
            this.renderPosts(data);
        } catch (error) {
            this.renderError('Error loading posts');
        } finally {
            this.hideLoading();
        }
    }

    renderPosts(posts) {
        const contextName = this.currentFilters.adsetName || this.currentFilters.campaignName || this.currentFilters.accountName;
        
        const html = `
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Posts for ${contextName}</h3>
                <p class="text-sm text-gray-600 mt-1">Found ${posts.length} posts</p>
            </div>
            <div class="overflow-auto rounded border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Post</th>
                            <th class="px-4 py-3 text-left">Page</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Engagement</th>
                            <th class="px-4 py-3 text-left">Performance</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Links</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${posts.map(post => {
                            const totalEngagement = post.likes_count + post.shares_count + post.comments_count;
                            return `
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium">${post.message ? post.message.substring(0, 50) + '...' : 'No message'}</div>
                                        <div class="text-xs text-gray-500 font-mono">${post.post_id}</div>
                                        <div class="text-xs text-gray-500">${new Date(post.created_time).toLocaleDateString()}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">${post.page_name}</div>
                                        <div class="text-xs text-gray-500">${post.page_id}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            ${post.type}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm">
                                            <div>👍 ${post.likes_count.toLocaleString()}</div>
                                            <div>🔄 ${post.shares_count.toLocaleString()}</div>
                                            <div>💬 ${post.comments_count.toLocaleString()}</div>
                                            <div class="font-medium">Total: ${totalEngagement.toLocaleString()}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm">
                                            <div>👁️ ${post.impressions.toLocaleString()}</div>
                                            <div>🎯 ${post.reach.toLocaleString()}</div>
                                            <div>🖱️ ${post.clicks.toLocaleString()}</div>
                                            ${post.engagement_rate ? `<div>📊 ${post.engagement_rate}%</div>` : ''}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        ${post.is_promoted ? `
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Promoted
                                            </span>
                                            ${post.ad_name ? `<div class="text-xs text-gray-500 mt-1">Ad: ${post.ad_name}</div>` : ''}
                                            ${post.campaign_name ? `<div class="text-xs text-gray-500">Campaign: ${post.campaign_name}</div>` : ''}
                                        ` : `
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Organic
                                            </span>
                                        `}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            <div>
                                                <a href="${post.post_link}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs block">
                                                    📝 View Post
                                                </a>
                                            </div>
                                            <div>
                                                <a href="${post.page_link}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs block">
                                                    📄 View Page
                                                </a>
                                            </div>
                                            ${post.permalink_url ? `
                                                <div>
                                                    <a href="${post.permalink_url}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs block">
                                                        🔗 Permalink
                                                    </a>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('data-container').innerHTML = html;
    }

    renderError(message) {
        document.getElementById('data-container').innerHTML = `
            <div class="text-center py-8">
                <div class="text-red-600 mb-2">
                    <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-gray-600">${message}</p>

                <button onclick="window.location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Thử lại
                </button>
            </div>
        `;
    }


}



// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.facebookHierarchy = new FacebookHierarchy();
});
</script>
@endpush
