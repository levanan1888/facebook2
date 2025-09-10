/**
 * Hierarchical Filter Manager
 * Quản lý filter đa cấp: BM → Ad Account → Campaign → Ads
 */
class HierarchicalFilterManager {
    constructor() {
        this.baseUrl = '/api/filter';
        this.cache = new Map();
        this.currentFilters = {
            businessId: null,
            accountId: null,
            campaignId: null,
            adId: null,
            pageId: null
        };
        
        // Debounce timers
        this.debounceTimers = new Map();
        
        // Load saved filters from localStorage
        this.loadSavedFilters();
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadBusinessManagers();
        // Restore saved filter values after loading
        setTimeout(() => this.restoreFilterValues(), 500);
    }

    bindEvents() {
        // Business Manager change
        document.addEventListener('change', (e) => {
            if (e.target.matches('#business_id')) {
                this.debounce('business', () => this.onBusinessChange(e.target.value), 300);
            } else if (e.target.matches('#account_id')) {
                this.debounce('account', () => this.onAccountChange(e.target.value), 300);
            } else if (e.target.matches('#campaign_id')) {
                this.debounce('campaign', () => this.onCampaignChange(e.target.value), 300);
            } else if (e.target.matches('#ad_id')) {
                this.debounce('ad', () => this.onAdChange(e.target.value), 300);
            } else if (e.target.matches('#page_id')) {
                this.debounce('page', () => this.onPageChange(e.target.value), 300);
            }
        });
    }

    debounce(key, callback, delay) {
        // Clear existing timer
        if (this.debounceTimers.has(key)) {
            clearTimeout(this.debounceTimers.get(key));
        }
        
        // Set new timer
        const timer = setTimeout(() => {
            callback();
            this.debounceTimers.delete(key);
        }, delay);
        
        this.debounceTimers.set(key, timer);
    }

    setLoadingState(selector, isLoading) {
        const select = document.querySelector(selector);
        if (!select) return;

        if (isLoading) {
            select.disabled = true;
            select.innerHTML = '<option value="">Đang tải...</option>';
        } else {
            select.disabled = false;
        }
    }

    async loadBusinessManagers() {
        try {
            this.setLoadingState('#business_id', true);
            const data = await this.fetchData(`${this.baseUrl}/businesses`);
            this.populateSelect('#business_id', data, 'id', 'name');
        } catch (error) {
            console.error('Lỗi khi tải Business Managers:', error);
            this.showError('Không thể tải danh sách Business Managers');
        } finally {
            this.setLoadingState('#business_id', false);
        }
    }

    async onBusinessChange(businessId) {
        this.currentFilters.businessId = businessId;
        this.resetLowerLevels(['account', 'campaign', 'ad', 'page']);
        
        if (businessId) {
            // Load tuần tự để tránh duplicate calls
            await this.loadAdAccounts(businessId);
            await this.loadPages(businessId);
        }
        
        // Save filter state
        this.saveFilters();
    }

    async onAccountChange(accountId) {
        this.currentFilters.accountId = accountId;
        this.resetLowerLevels(['campaign', 'ad']);
        
        if (accountId) {
            await this.loadCampaigns(accountId);
        }
        
        // Save filter state
        this.saveFilters();
    }

    async onCampaignChange(campaignId) {
        this.currentFilters.campaignId = campaignId;
        this.resetLowerLevels(['ad']);
        
        if (campaignId) {
            await this.loadAds(campaignId);
        }
        
        // Save filter state
        this.saveFilters();
    }

    onAdChange(adId) {
        this.currentFilters.adId = adId;
        // Save filter state
        this.saveFilters();
    }

    onPageChange(pageId) {
        this.currentFilters.pageId = pageId;
        // Save filter state
        this.saveFilters();
    }

    async loadAdAccounts(businessId) {
        try {
            this.setLoadingState('#account_id', true);
            const data = await this.fetchData(`${this.baseUrl}/businesses/${businessId}/ad-accounts`);
            this.populateSelect('#account_id', data, 'id', 'name');
        } catch (error) {
            console.error('Lỗi khi tải Ad Accounts:', error);
            this.showError('Không thể tải danh sách Ad Accounts');
        } finally {
            this.setLoadingState('#account_id', false);
        }
    }

    async loadCampaigns(accountId) {
        try {
            this.setLoadingState('#campaign_id', true);
            const data = await this.fetchData(`${this.baseUrl}/ad-accounts/${accountId}/campaigns`);
            this.populateSelect('#campaign_id', data, 'id', 'name');
        } catch (error) {
            console.error('Lỗi khi tải Campaigns:', error);
            this.showError('Không thể tải danh sách Campaigns');
        } finally {
            this.setLoadingState('#campaign_id', false);
        }
    }

    async loadAds(campaignId) {
        try {
            this.setLoadingState('#ad_id', true);
            const data = await this.fetchData(`${this.baseUrl}/campaigns/${campaignId}/ads`);
            this.populateSelect('#ad_id', data, 'id', 'name');
        } catch (error) {
            console.error('Lỗi khi tải Ads:', error);
            this.showError('Không thể tải danh sách Ads');
        } finally {
            this.setLoadingState('#ad_id', false);
        }
    }

    async loadPages(businessId) {
        try {
            this.setLoadingState('#page_id', true);
            const data = await this.fetchData(`${this.baseUrl}/businesses/${businessId}/pages`);
            this.populateSelect('#page_id', data, 'id', 'name');
        } catch (error) {
            console.error('Lỗi khi tải Pages:', error);
            this.showError('Không thể tải danh sách Pages');
        } finally {
            this.setLoadingState('#page_id', false);
        }
    }

    resetLowerLevels(levels) {
        levels.forEach(level => {
            const selectId = `#${level}_id`;
            const select = document.querySelector(selectId);
            if (select) {
                select.innerHTML = '<option value="">Chọn ' + this.getLevelName(level) + '...</option>';
                select.disabled = false; // cho phép mở rộng lại ngay khi reset
            }
        });
    }

    getLevelName(level) {
        const names = {
            'account': 'Ad Account',
            'campaign': 'Campaign',
            'ad': 'Ad',
            'page': 'Page'
        };
        return names[level] || level;
    }

    populateSelect(selector, data, valueKey, textKey) {
        const select = document.querySelector(selector);
        if (!select) return;

        // Clear existing options except first one
        select.innerHTML = '<option value="">Chọn ' + this.getLevelName(selector.replace('#', '').replace('_id', '')) + '...</option>';
        
        // Add new options
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[textKey];
            select.appendChild(option);
        });

        // Enable select
        select.disabled = false;
    }

    async fetchData(url) {
        // Check cache first
        if (this.cache.has(url)) {
            return this.cache.get(url);
        }

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'API error');
        }

        // Cache the result
        this.cache.set(url, result.data);
        return result.data;
    }

    showError(message) {
        // You can implement a toast notification here
        console.error(message);
        // For now, just log to console
    }

    getCurrentFilters() {
        return { ...this.currentFilters };
    }

    clearCache() {
        this.cache.clear();
    }

    // Lưu filter state vào localStorage
    saveFilters() {
        const filtersToSave = {
            businessId: this.currentFilters.businessId,
            accountId: this.currentFilters.accountId,
            campaignId: this.currentFilters.campaignId,
            adId: this.currentFilters.adId,
            pageId: this.currentFilters.pageId
        };
        localStorage.setItem('facebook_filter_state', JSON.stringify(filtersToSave));
    }

    // Load filter state từ localStorage
    loadSavedFilters() {
        try {
            const saved = localStorage.getItem('facebook_filter_state');
            if (saved) {
                const savedFilters = JSON.parse(saved);
                this.currentFilters = { ...this.currentFilters, ...savedFilters };
            }
        } catch (e) {
            console.warn('Không thể load saved filters:', e);
        }
    }

    // Khôi phục giá trị filter vào các select
    async restoreFilterValues() {
        // Restore Business Manager
        if (this.currentFilters.businessId) {
            const businessSelect = document.querySelector('#business_id');
            if (businessSelect) {
                businessSelect.value = this.currentFilters.businessId;
                // Load dependent data
                await this.loadAdAccounts(this.currentFilters.businessId);
                await this.loadPages(this.currentFilters.businessId);
            }
        }

        // Restore Ad Account
        if (this.currentFilters.accountId) {
            const accountSelect = document.querySelector('#account_id');
            if (accountSelect) {
                accountSelect.value = this.currentFilters.accountId;
                // Load dependent data
                await this.loadCampaigns(this.currentFilters.accountId);
            }
        }

        // Restore Campaign
        if (this.currentFilters.campaignId) {
            const campaignSelect = document.querySelector('#campaign_id');
            if (campaignSelect) {
                campaignSelect.value = this.currentFilters.campaignId;
                // Load dependent data
                await this.loadAds(this.currentFilters.campaignId);
            }
        }

        // Restore Ad
        if (this.currentFilters.adId) {
            const adSelect = document.querySelector('#ad_id');
            if (adSelect) {
                adSelect.value = this.currentFilters.adId;
            }
        }

        // Restore Page
        if (this.currentFilters.pageId) {
            const pageSelect = document.querySelector('#page_id');
            if (pageSelect) {
                pageSelect.value = this.currentFilters.pageId;
            }
        }
    }

    // Clear saved filters
    clearSavedFilters() {
        localStorage.removeItem('facebook_filter_state');
        this.currentFilters = {
            businessId: null,
            accountId: null,
            campaignId: null,
            adId: null,
            pageId: null
        };
    }

    reset() {
        this.currentFilters = {
            businessId: null,
            accountId: null,
            campaignId: null,
            adId: null,
            pageId: null
        };
        
        // Reset all selects
        ['business', 'account', 'campaign', 'ad', 'page'].forEach(level => {
            const select = document.querySelector(`#${level}_id`);
            if (select) {
                select.innerHTML = '<option value="">Chọn ' + this.getLevelName(level) + '...</option>';
                select.disabled = level !== 'business';
            }
        });
        
        this.clearCache();
        this.clearSavedFilters();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.hierarchicalFilter = new HierarchicalFilterManager();
});

// Re-initialize on Livewire navigation
document.addEventListener('livewire:navigated', () => {
    if (window.hierarchicalFilter) {
        // Don't reset, just re-initialize to preserve filter state
        window.hierarchicalFilter.init();
    } else {
        window.hierarchicalFilter = new HierarchicalFilterManager();
    }
});
