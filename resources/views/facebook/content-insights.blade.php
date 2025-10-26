<x-layouts.app title="Tổng quan về nội dung - Facebook Insights">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Tổng quan về nội dung
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" onclick="syncContentInsights()">
                            <i class="fas fa-sync me-1"></i>
                            Đồng bộ dữ liệu
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Trang Facebook</label>
                            <select class="form-select" id="pageSelect" onchange="filterByPage()">
                                <option value="">Tất cả trang</option>
                                @foreach($pages as $page)
                                    <option value="{{ $page->page_id }}" {{ $pageId == $page->page_id ? 'selected' : '' }}>
                                        {{ $page->name ?? $page->page_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Khoảng thời gian</label>
                            <select class="form-select" id="dateRange" onchange="filterByDateRange()">
                                <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>7 ngày</option>
                                <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>30 ngày</option>
                                <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>90 ngày</option>
                                <option value="custom">Tùy chỉnh</option>
                            </select>
                        </div>
                        <div class="col-md-2" id="customDateRange" style="display: none;">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" class="form-control" id="startDate" value="{{ $since->toDateString() }}">
                        </div>
                        <div class="col-md-2" id="customDateRange2" style="display: none;">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" class="form-control" id="endDate" value="{{ $until->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số liệu chia nhỏ</label>
                            <select class="form-select" id="breakdownSelect">
                                <option value="organic_paid">Tự nhiên/Quảng cáo</option>
                                <option value="all">Tất cả</option>
                            </select>
                        </div>
                    </div>

                    <!-- Content Type Tabs -->
                    <ul class="nav nav-tabs mb-4" id="contentTypeTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all-content">Tất cả</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#posts">Bài viết</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#stories">Tin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#reels">Reels</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#live">Video trực tiếp</a>
                        </li>
                    </ul>

                    <!-- Key Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Lượt xem</span>
                                    <span class="info-box-number">{{ number_format($insightsData['totals']['content_views']) }}</span>
                                    <span class="info-box-more text-muted">Tổng cộng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Lượt xem (3s+)</span>
                                    <span class="info-box-number">{{ number_format($insightsData['totals']['content_views_3_seconds']) }}</span>
                                    <span class="info-box-more text-muted">Chất lượng cao</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Lượt xem (1m+)</span>
                                    <span class="info-box-number">{{ number_format($insightsData['totals']['content_views_1_minute']) }}</span>
                                    <span class="info-box-more text-muted">Tương tác sâu</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-heart"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tương tác</span>
                                    <span class="info-box-number">{{ number_format($insightsData['totals']['content_interactions']) }}</span>
                                    <span class="info-box-more text-muted">Tổng tương tác</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Người xem</span>
                                    <span class="info-box-number">{{ number_format($insightsData['totals']['content_viewers']) }}</span>
                                    <span class="info-box-more text-muted">Người xem duy nhất</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-dark">
                                    <i class="fas fa-chart-bar"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Số hiển thị</span>
                                    <span class="info-box-number">{{ number_format($insightsData['totals']['content_impressions']) }}</span>
                                    <span class="info-box-more text-muted">Tổng hiển thị</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Biểu đồ xu hướng</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="contentInsightsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Panel -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Số liệu chia nhỏ về lượt xem</h3>
                                    <div class="card-tools">
                                        <span class="badge bg-primary">{{ $since->format('d/m/Y') }} – {{ $until->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="description-block border-right">
                                                <h5 class="description-header text-primary">{{ number_format($insightsData['totals']['content_views']) }}</h5>
                                                <span class="description-text">Tổng</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="description-block border-right">
                                                <h5 class="description-header text-success">{{ number_format($insightsData['totals']['content_views_organic']) }}</h5>
                                                <span class="description-text">Từ nguồn tự nhiên ({{ $insightsData['totals']['organic_percentage'] }}%)</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="description-block">
                                                <h5 class="description-header text-warning">{{ number_format($insightsData['totals']['content_views_paid']) }}</h5>
                                                <span class="description-text">Từ quảng cáo ({{ $insightsData['totals']['paid_percentage'] }}%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Nội dung hàng đầu</h3>
                                </div>
                                <div class="card-body">
                                    @if($topContent->count() > 0)
                                        @foreach($topContent as $content)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <h6 class="mb-0">{{ Str::limit($content->message ?? 'Nội dung không có văn bản', 30) }}</h6>
                                                    <small class="text-muted">{{ $content->created_time }}</small>
                                                </div>
                                                <span class="badge bg-primary">{{ number_format($content->views) }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Chưa có dữ liệu nội dung</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let contentInsightsChart;

document.addEventListener('DOMContentLoaded', function() {
    initChart();
});

function initChart() {
    const ctx = document.getElementById('contentInsightsChart').getContext('2d');
    
    const dailyData = @json($insightsData['daily']);
    const labels = Object.keys(dailyData).map(date => {
        const d = new Date(date);
        return d.getDate() + '/' + (d.getMonth() + 1);
    });
    
    const viewsData = Object.values(dailyData).map(day => day.views);
    const organicData = Object.values(dailyData).map(day => day.views_organic);
    const paidData = Object.values(dailyData).map(day => day.views_paid);

    contentInsightsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Lượt xem',
                data: viewsData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Từ nguồn tự nhiên',
                data: organicData,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Từ quảng cáo',
                data: paidData,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function filterByPage() {
    const pageId = document.getElementById('pageSelect').value;
    const url = new URL(window.location);
    if (pageId) {
        url.searchParams.set('page_id', pageId);
    } else {
        url.searchParams.delete('page_id');
    }
    window.location.href = url.toString();
}

function filterByDateRange() {
    const dateRange = document.getElementById('dateRange').value;
    const customRange = document.getElementById('customDateRange');
    const customRange2 = document.getElementById('customDateRange2');
    
    if (dateRange === 'custom') {
        customRange.style.display = 'block';
        customRange2.style.display = 'block';
    } else {
        customRange.style.display = 'none';
        customRange2.style.display = 'none';
        
        const url = new URL(window.location);
        url.searchParams.set('date_range', dateRange);
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');
        window.location.href = url.toString();
    }
}

function syncContentInsights() {
    const pageId = document.getElementById('pageSelect').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!pageId) {
        alert('Vui lòng chọn trang Facebook');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang đồng bộ...';
    button.disabled = true;
    
    fetch('/facebook/sync-content-insights', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            page_id: pageId,
            start_date: startDate,
            end_date: endDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đồng bộ thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể đồng bộ dữ liệu'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi đồng bộ dữ liệu');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>
@endpush
</x-layouts.app>
