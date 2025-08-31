<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="text-center text-white">
        <!-- Logo Website -->
        <div class="mb-8 floating">
            @php
                $setting = \App\Models\Setting::first();
                $siteName = $setting ? $setting->getSiteName() : config('app.name');
                $logoUrl = $setting ? $setting->getLogoUrl() : null;
            @endphp
            
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="mx-auto w-24 h-24 mb-4 rounded-lg shadow-2xl">
            @else
                <div class="mx-auto w-24 h-24 mb-4 bg-white/20 rounded-lg shadow-2xl flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            @endif
            
            <h1 class="text-4xl font-bold mb-2">{{ $siteName }}</h1>
        </div>

        <!-- 403 Content -->
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 shadow-2xl border border-white/20">
            <div class="text-8xl font-bold text-white/80 mb-4">403</div>
            <h2 class="text-2xl font-semibold mb-4">Không có quyền truy cập</h2>
            <p class="text-lg text-white/80 mb-8 max-w-md mx-auto">
                Bạn không có quyền truy cập vào trang này. Vui lòng liên hệ quản trị viên nếu bạn cần quyền truy cập.
            </p>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('dashboard') }}" 
                   class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Về Trang Chủ
                </a>
                
                <button onclick="history.back()" 
                        class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-red-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Quay Lại
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-white/60 text-sm">
            <p>Nếu bạn tin rằng đây là lỗi, vui lòng liên hệ quản trị viên</p>
        </div>
    </div>
</body>
</html>
