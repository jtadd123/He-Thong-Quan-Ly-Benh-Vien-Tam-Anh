<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kiosk Tự Phục Vụ Tiếp Nhận - BV Đa Khoa Tâm Anh')</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- html5-qrcode scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        :root {
            --tamanh-blue: #005baa;
            --tamanh-dark: #003a6c;
            --tamanh-cyan: #0284c7;
            --tamanh-accent: #f59e0b;
        }

        body {
            font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header Bar */
        .kiosk-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 2px solid rgba(2, 132, 199, 0.4);
            padding: 1rem 2rem;
        }

        .kiosk-logo-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #38bdf8;
        }

        .kiosk-clock {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.35rem;
            font-weight: 700;
            color: #f8fafc;
        }

        /* Big Touch Buttons */
        .kiosk-card-btn {
            background: #ffffff;
            color: #0f172a;
            border-radius: 20px;
            padding: 2.2rem 1.8rem;
            transition: all 0.2s ease;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            border: 3px solid transparent;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .kiosk-card-btn:active, .kiosk-card-btn:hover {
            transform: translateY(-4px) scale(1.02);
            border-color: #38bdf8;
            box-shadow: 0 20px 30px -10px rgba(56, 189, 248, 0.4);
        }

        .kiosk-icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
        }

        .touch-input {
            font-size: 1.4rem !important;
            padding: 1rem 1.25rem !important;
            border-radius: 14px !important;
            font-weight: 600;
        }

        /* Virtual Numpad */
        .numpad-btn {
            font-size: 1.6rem;
            font-weight: 700;
            padding: 1rem;
            border-radius: 12px;
            background: #ffffff;
            color: #0f172a;
            border: 2px solid #e2e8f0;
            transition: background-color 0.1s;
        }
        .numpad-btn:active {
            background-color: #38bdf8;
            color: #ffffff;
            transform: scale(0.96);
        }

        /* Footer Bar */
        .kiosk-footer {
            margin-top: auto;
            background: rgba(15, 23, 42, 0.9);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.85rem 2rem;
            font-size: 0.95rem;
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Kiosk Top Navigation -->
    <header class="kiosk-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-hospital fs-3"></i>
            </div>
            <div>
                <div class="kiosk-logo-title text-uppercase">BỆNH VIỆN ĐA KHOA TÂM ANH</div>
                <div class="text-white-50 small">HỆ THỐNG TIẾP NHẬN TỰ ĐỘNG THÔNG MINH (SMART KIOSK 24/7)</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <div class="text-end d-none d-md-block">
                <div id="kioskClock" class="kiosk-clock">00:00:00</div>
                <div id="kioskDate" class="text-white-50 small">--/--/----</div>
            </div>
            <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1 opacity-75">
                <i class="bi bi-person-badge"></i> Quầy Lễ Tân
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow-1 p-3 p-md-4 d-flex flex-column justify-content-center">
        @yield('content')
    </main>

    <!-- Kiosk Bottom Bar -->
    <footer class="kiosk-footer d-flex justify-content-between align-items-center text-white-50">
        <div>
            <i class="bi bi-shield-check text-success me-1"></i> Tiêu chuẩn chất lượng quốc tế JCI &bull; Bảo mật thông tin Bộ Y Tế
        </div>
        <div class="d-flex gap-3 align-items-center">
            <span>Cần trợ giúp? Vui lòng gặp điều dưỡng sảnh hoặc liên hệ Hotline: <strong class="text-warning">1800 6858</strong></span>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Realtime Clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('vi-VN', { hour12: false });
            const dateStr = now.toLocaleDateString('vi-VN', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' });
            const clockEl = document.getElementById('kioskClock');
            const dateEl = document.getElementById('kioskDate');
            if (clockEl) clockEl.textContent = timeStr;
            if (dateEl) dateEl.textContent = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @yield('scripts')
</body>
</html>
