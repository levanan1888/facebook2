<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facebook Insgin - Quản lý Facebook chuyên nghiệp</title>
    <meta name="description" content="Facebook Insgin - Nền tảng quản lý Facebook hiệu quả, tối ưu hóa marketing và tăng tương tác khách hàng">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background: #ffffff;
            font-weight: 300;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.6rem;
            font-weight: 600;
            color: #0ea5e9;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-weight: 400;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }
        
        .nav-links a:hover {
            color: #0ea5e9;
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #0ea5e9;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0284c7;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            color: #0ea5e9;
            border: 1px solid #0ea5e9;
        }
        
        .btn-outline:hover {
            background: #0ea5e9;
            color: white;
        }
        
        /* Hero Section */
        .hero {
            padding: 7rem 0 4rem;
            text-align: center;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
        
        .hero h1 {
            font-size: 2.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.2;
            color: #0c4a6e;
        }
        
        .hero p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #475569;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-lg {
            padding: 0.8rem 1.8rem;
            font-size: 1rem;
        }
        
        /* Features Section */
        .features {
            padding: 4rem 0;
            background: white;
        }
        
        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2.5rem;
            color: #0c4a6e;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2.5rem;
        }
        
        .feature-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: #0c4a6e;
        }
        
        .feature-card p {
            color: #64748b;
            line-height: 1.5;
            font-size: 0.9rem;
            font-weight: 400;
        }
        
        /* CTA Section */
        .cta {
            padding: 4rem 0;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: white;
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        /* Footer */
        .footer {
            background: #0f172a;
            color: white;
            padding: 2rem 0 1.5rem;
            text-align: center;
        }
        
        .footer p {
            opacity: 0.7;
            font-weight: 400;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="#" class="logo">
                    <i class="fab fa-facebook"></i> Facebook Insgin
                </a>
                <ul class="nav-links">
                    <li><a href="#features">Tính năng</a></li>
                    <li><a href="#pricing">Giá cả</a></li>
                    <li><a href="#contact">Liên hệ</a></li>
                </ul>
                <div class="auth-buttons">
                    <a href="{{ route('login') }}" class="btn btn-outline">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Đăng ký</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1>Quản lý Facebook hiệu quả</h1>
                <p>Công cụ mạnh mẽ để quản lý trang Facebook, tối ưu hóa nội dung và tăng tương tác khách hàng.</p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Bắt đầu miễn phí</a>
                    <a href="#features" class="btn btn-outline btn-lg">Tìm hiểu thêm</a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features" id="features">
            <div class="container">
                <h2 class="section-title">Tính năng chính</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Phân tích hiệu suất</h3>
                        <p>Theo dõi và phân tích hiệu suất bài đăng, tối ưu hóa chiến lược marketing.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3>Lên lịch bài đăng</h3>
                        <p>Lên lịch trước các bài đăng để duy trì hoạt động liên tục trên Facebook.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Quản lý tương tác</h3>
                        <p>Quản lý hiệu quả bình luận, tin nhắn và tương tác với khách hàng.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3>Quảng cáo thông minh</h3>
                        <p>Tối ưu hóa chiến dịch quảng cáo Facebook với công cụ phân tích thông minh.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Responsive Design</h3>
                        <p>Giao diện thân thiện với mọi thiết bị, quản lý Facebook mọi lúc, mọi nơi.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Bảo mật cao</h3>
                        <p>Đảm bảo an toàn thông tin và dữ liệu với biện pháp bảo mật tiên tiến.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <div class="container">
                <h2>Sẵn sàng nâng tầm Facebook?</h2>
                <p>Tham gia cùng hàng nghìn doanh nghiệp đã thành công</p>
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Đăng ký ngay</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Facebook Insgin. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>
</body>
</html> 