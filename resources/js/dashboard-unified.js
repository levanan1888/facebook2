/**
 * Dashboard Unified JavaScript
 * Xử lý tương tác với dashboard thống nhất theo tư duy Supermetrics
 */

class DashboardUnified {
    constructor() {
        this.currentTab = 'overview';
        this.currentFilters = {
            dateRange: 30,
            dataSource: 'all',
            metric: 'spend'
        };
        this.charts = {};
        this.dataCache = {};
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialData();
        this.setupRealTimeUpdates();
    }

    bindEvents() {
        // Tab navigation
        document.querySelectorAll('[data-tab]').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Filter controls
        this.bindFilterEvents();
        
        // Data source management
        this.bindDataSourceEvents();
        
        // Chart controls
        this.bindChartEvents();
    }

    bindFilterEvents() {
        // Date range filter
        const dateRange = document.getElementById('dateRange');
        if (dateRange) {
            dateRange.addEventListener('change', (e) => {
                this.currentFilters.dateRange = parseInt(e.target.value);
                this.loadUnifiedData();
            });
        }

        // Data source filter
        const dataSource = document.getElementById('dataSource');
        if (dataSource) {
            dataSource.addEventListener('change', (e) => {
                this.currentFilters.dataSource = e.target.value;
                this.loadUnifiedData();
            });
        }

        // Comparison controls
        const comparisonDateRange = document.getElementById('comparisonDateRange');
        if (comparisonDateRange) {
            comparisonDateRange.addEventListener('change', (e) => {
                this.currentFilters.dateRange = parseInt(e.target.value);
                this.loadComparisonData();
            });
        }

        const source1 = document.getElementById('source1');
        const source2 = document.getElementById('source2');
        const metric = document.getElementById('metric');

        if (source1) {
            source1.addEventListener('change', () => this.loadComparisonData());
        }
        if (source2) {
            source2.addEventListener('change', () => this.loadComparisonData());
        }
        if (metric) {
            metric.addEventListener('change', () => this.loadComparisonData());
        }
    }

    bindDataSourceEvents() {
        // Add data source button
        const btnAddDataSource = document.getElementById('btnAddDataSource');
        if (btnAddDataSource) {
            btnAddDataSource.addEventListener('click', () => {
                this.showAddDataSourceModal();
            });
        }

        // Sync buttons
        document.querySelectorAll('[data-sync]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const source = e.target.dataset.sync;
                this.syncDataSource(source);
            });
        });
    }

    bindChartEvents() {
        // Chart type controls
        const chartTypeLine = document.getElementById('chartTypeLine');
        const chartTypeBar = document.getElementById('chartTypeBar');
        const chartTypeArea = document.getElementById('chartTypeArea');

        if (chartTypeLine) {
            chartTypeLine.addEventListener('click', () => this.changeChartType('line'));
        }
        if (chartTypeBar) {
            chartTypeBar.addEventListener('click', () => this.changeChartType('bar'));
        }
        if (chartTypeArea) {
            chartTypeArea.addEventListener('click', () => this.changeChartType('area'));
        }
    }

    async loadInitialData() {
        try {
            await this.loadUnifiedData();
            await this.loadDataSourcesStatus();
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showNotification('Lỗi khi tải dữ liệu ban đầu', 'error');
        }
    }

    async loadUnifiedData() {
        try {
            const response = await fetch(`/api/dashboard/unified-data?${new URLSearchParams(this.currentFilters)}`);
            const result = await response.json();

            if (result.success) {
                this.dataCache.unified = result.data;
                this.renderUnifiedData(result.data);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading unified data:', error);
            this.showNotification('Lỗi khi tải dữ liệu thống nhất', 'error');
        }
    }

    async loadComparisonData() {
        try {
            const filters = {
                date_range: this.currentFilters.dateRange,
                source1: document.getElementById('source1')?.value || 'facebook_ads',
                source2: document.getElementById('source2')?.value || 'facebook_posts',
                metric: document.getElementById('metric')?.value || 'spend'
            };

            const response = await fetch(`/api/dashboard/comparison-data?${new URLSearchParams(filters)}`);
            const result = await response.json();

            if (result.success) {
                this.dataCache.comparison = result.data;
                this.renderComparisonData(result.data);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading comparison data:', error);
            this.showNotification('Lỗi khi tải dữ liệu so sánh', 'error');
        }
    }

    async loadDataSourcesStatus() {
        try {
            const response = await fetch('/api/dashboard/data-sources-status');
            const result = await response.json();

            if (result.success) {
                this.renderDataSourcesStatus(result.data);
            }
        } catch (error) {
            console.error('Error loading data sources status:', error);
        }
    }

    renderUnifiedData(data) {
        // Render metrics grid
        this.renderMetricsGrid(data.totals);
        
        // Render performance chart
        this.renderPerformanceChart(data.performance);
        
        // Render trends
        this.renderTrends(data.trends);
    }

    renderComparisonData(data) {
        // Render comparison chart
        this.renderComparisonChart(data.comparisons);
        
        // Render comparison table
        this.renderComparisonTable(data.comparisons);
        
        // Render insights
        this.renderInsights(data);
    }

    renderMetricsGrid(totals) {
        // Update metric cards with real data
        Object.entries(totals).forEach(([metric, value]) => {
            const element = document.querySelector(`[data-metric="${metric}"]`);
            if (element) {
                if (metric === 'spend') {
                    element.textContent = this.formatCurrency(value);
                } else if (metric === 'ctr') {
                    element.textContent = `${(value * 100).toFixed(2)}%`;
                } else {
                    element.textContent = this.formatNumber(value);
                }
            }
        });
    }

    renderPerformanceChart(performance) {
        // Placeholder for chart rendering
        // In real implementation, use Chart.js or ApexCharts
        console.log('Rendering performance chart with data:', performance);
    }

    renderComparisonChart(comparisons) {
        // Placeholder for comparison chart rendering
        console.log('Rendering comparison chart with data:', comparisons);
    }

    renderComparisonTable(comparisons) {
        // Update comparison table with real data
        const tableBody = document.querySelector('#comparisonTable tbody');
        if (tableBody && comparisons.summary) {
            // Implementation for updating comparison table
        }
    }

    renderTrends(trends) {
        // Update trend indicators
        Object.entries(trends).forEach(([metric, trend]) => {
            const element = document.querySelector(`[data-trend="${metric}"]`);
            if (element) {
                const changeElement = element.querySelector('.change-percent');
                const trendElement = element.querySelector('.trend-indicator');
                
                if (changeElement) {
                    changeElement.textContent = `${trend.change_percent}%`;
                    changeElement.className = `change-percent ${trend.trend === 'up' ? 'text-green-600' : 'text-red-600'}`;
                }
                
                if (trendElement) {
                    trendElement.className = `trend-indicator ${trend.trend === 'up' ? 'text-green-500' : 'text-red-500'}`;
                }
            }
        });
    }

    renderDataSourcesStatus(sources) {
        // Update data source status indicators
        Object.entries(sources).forEach(([key, source]) => {
            const indicator = document.querySelector(`[data-source="${key}"] .status-indicator`);
            if (indicator) {
                indicator.className = `status-indicator w-3 h-3 rounded-full ${
                    source.status === 'connected' ? 'bg-green-500 animate-pulse' : 'bg-gray-300'
                }`;
            }
        });
    }

    renderInsights(data) {
        // Update insights and recommendations
        const insightsContainer = document.querySelector('#insightsContainer');
        if (insightsContainer) {
            // Implementation for updating insights
        }
    }

    async syncDataSource(source) {
        try {
            const button = document.querySelector(`[data-sync="${source}"]`);
            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="animate-spin">⏳</span> Đang đồng bộ...';
            }

            // Call sync API
            const response = await fetch(`/facebook/sync/${source}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                this.showNotification(`Đồng bộ ${source} thành công!`, 'success');
                await this.loadDataSourcesStatus();
            } else {
                throw new Error('Sync failed');
            }
        } catch (error) {
            console.error(`Error syncing ${source}:`, error);
            this.showNotification(`Lỗi đồng bộ ${source}`, 'error');
        } finally {
            const button = document.querySelector(`[data-sync="${source}"]`);
            if (button) {
                button.disabled = false;
                button.innerHTML = 'Đồng bộ';
            }
        }
    }

    showAddDataSourceModal() {
        // Implementation for add data source modal
        alert('Tính năng thêm nguồn dữ liệu mới sẽ được phát triển trong tương lai');
    }

    changeChartType(type) {
        // Update chart type buttons
        const buttons = ['chartTypeLine', 'chartTypeBar', 'chartTypeArea'];
        buttons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                if (btnId === `chartType${type.charAt(0).toUpperCase() + type.slice(1)}`) {
                    btn.className = 'px-3 py-1 text-sm bg-blue-600 text-white border border-blue-600 rounded-lg';
                } else {
                    btn.className = 'px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50';
                }
            }
        });

        // Re-render chart with new type
        if (this.dataCache.comparison) {
            this.renderComparisonChart(this.dataCache.comparison);
        }
    }

    switchTab(tabName) {
        this.currentTab = tabName;
        
        // Update active tab styling
        document.querySelectorAll('[data-tab]').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');

        // Load tab-specific data
        switch (tabName) {
            case 'unified-data':
                this.loadUnifiedData();
                break;
            case 'comparison':
                this.loadComparisonData();
                break;
            default:
                // Other tabs handled by existing logic
                break;
        }
    }

    setupRealTimeUpdates() {
        // Update data sources status every 30 seconds
        setInterval(() => {
            this.loadDataSourcesStatus();
        }, 30000);

        // Update unified data every 5 minutes
        setInterval(() => {
            this.loadUnifiedData();
        }, 300000);
    }

    formatNumber(value) {
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        }
        return value.toLocaleString();
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(value);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DashboardUnified();
});

// Export for global access
window.DashboardUnified = DashboardUnified;
