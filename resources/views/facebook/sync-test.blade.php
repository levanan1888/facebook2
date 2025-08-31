@extends('layouts.app')

@section('title', 'Test Facebook Sync Direct')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Test Facebook Sync Direct</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sync với Job -->
                <div class="border rounded-lg p-4">
                    <h2 class="text-xl font-semibold mb-4">Sync với Job (Background)</h2>
                    <p class="text-gray-600 mb-4">Sử dụng queue job để xử lý trong background</p>
                    
                    <button id="syncWithJob" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bắt đầu Sync với Job
                    </button>
                    
                    <div id="jobStatus" class="mt-4 p-3 bg-gray-100 rounded hidden">
                        <p class="text-sm text-gray-700">Trạng thái: <span id="jobStatusText">Đang xử lý...</span></p>
                    </div>
                </div>
                
                <!-- Sync trực tiếp -->
                <div class="border rounded-lg p-4">
                    <h2 class="text-xl font-semibold mb-4">Sync Trực tiếp</h2>
                    <p class="text-gray-600 mb-4">Xử lý trực tiếp, không cần job</p>
                    
                    <button id="syncDirect" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Bắt đầu Sync Trực tiếp
                    </button>
                    
                    <div id="directStatus" class="mt-4 p-3 bg-gray-100 rounded hidden">
                        <p class="text-sm text-gray-700">Trạng thái: <span id="directStatusText">Đang xử lý...</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Kết quả -->
            <div id="syncResult" class="mt-8 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="text-lg font-semibold mb-3">Kết quả đồng bộ</h3>
                <div id="resultContent" class="text-sm text-gray-700"></div>
            </div>
            
            <!-- Logs -->
            <div id="syncLogs" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="text-lg font-semibold mb-3">Logs</h3>
                <div id="logsContent" class="text-sm text-gray-600 max-h-64 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const syncWithJobBtn = document.getElementById('syncWithJob');
    const syncDirectBtn = document.getElementById('syncDirect');
    const jobStatus = document.getElementById('jobStatus');
    const directStatus = document.getElementById('directStatus');
    const syncResult = document.getElementById('syncResult');
    const resultContent = document.getElementById('resultContent');
    const syncLogs = document.getElementById('syncLogs');
    const logsContent = document.getElementById('logsContent');
    
    // Sync với Job
    syncWithJobBtn.addEventListener('click', async function() {
        this.disabled = true;
        this.textContent = 'Đang xử lý...';
        jobStatus.classList.remove('hidden');
        
        try {
            const response = await fetch('/facebook/sync/ads', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('jobStatusText').textContent = 'Đã đưa vào queue';
                addLog('Job đã được đưa vào queue: ' + result.sync_id);
                
                // Poll status
                pollJobStatus();
            } else {
                document.getElementById('jobStatusText').textContent = 'Lỗi: ' + result.message;
                addLog('Lỗi khi tạo job: ' + result.message);
            }
            
        } catch (error) {
            document.getElementById('jobStatusText').textContent = 'Lỗi: ' + error.message;
            addLog('Lỗi network: ' + error.message);
        }
        
        this.disabled = false;
        this.textContent = 'Bắt đầu Sync với Job';
    });
    
    // Sync trực tiếp
    syncDirectBtn.addEventListener('click', async function() {
        this.disabled = true;
        this.textContent = 'Đang xử lý...';
        directStatus.classList.remove('hidden');
        document.getElementById('directStatusText').textContent = 'Đang đồng bộ...';
        
        try {
            const response = await fetch('/facebook/sync/ads-direct', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('directStatusText').textContent = 'Hoàn thành';
                showResult(result.result);
                addLog('Sync trực tiếp hoàn thành: ' + result.result.ads + ' ads');
            } else {
                document.getElementById('directStatusText').textContent = 'Lỗi: ' + result.message;
                addLog('Lỗi sync trực tiếp: ' + result.message);
            }
            
        } catch (error) {
            document.getElementById('directStatusText').textContent = 'Lỗi: ' + error.message;
            addLog('Lỗi network: ' + error.message);
        }
        
        this.disabled = false;
        this.textContent = 'Bắt đầu Sync Trực tiếp';
    });
    
    // Poll job status
    function pollJobStatus() {
        const interval = setInterval(async function() {
            try {
                const response = await fetch('/facebook/sync/status');
                const status = await response.json();
                
                if (status.status === 'completed' || status.status === 'error') {
                    clearInterval(interval);
                    
                    if (status.status === 'completed') {
                        document.getElementById('jobStatusText').textContent = 'Hoàn thành';
                        if (status.result) {
                            showResult(status.result);
                        }
                        addLog('Job hoàn thành');
                    } else {
                        document.getElementById('jobStatusText').textContent = 'Lỗi: ' + (status.error || 'Unknown error');
                        addLog('Job lỗi: ' + (status.error || 'Unknown error'));
                    }
                } else {
                    document.getElementById('jobStatusText').textContent = status.message || 'Đang xử lý...';
                }
                
            } catch (error) {
                addLog('Lỗi khi poll status: ' + error.message);
            }
        }, 2000);
    }
    
    // Hiển thị kết quả
    function showResult(result) {
        resultContent.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">${result.businesses || 0}</div>
                    <div class="text-sm text-gray-500">Businesses</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">${result.accounts || 0}</div>
                    <div class="text-sm text-gray-500">Ad Accounts</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">${result.campaigns || 0}</div>
                    <div class="text-sm text-gray-500">Campaigns</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">${result.ads || 0}</div>
                    <div class="text-sm text-gray-500">Ads</div>
                </div>
            </div>
            <div class="mt-4">
                <p><strong>Thời gian:</strong> ${result.duration || 0} giây</p>
                <p><strong>Lỗi:</strong> ${result.errors?.length || 0}</p>
            </div>
        `;
        
        syncResult.classList.remove('hidden');
    }
    
    // Thêm log
    function addLog(message) {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = 'mb-2 p-2 bg-white rounded border';
        logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;
        
        logsContent.appendChild(logEntry);
        logsContent.scrollTop = logsContent.scrollHeight;
        syncLogs.classList.remove('hidden');
    }
});
</script>
@endsection

