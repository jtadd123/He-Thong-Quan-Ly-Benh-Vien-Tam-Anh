<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Bệnh Viện Đa Khoa Tâm Anh - Đặt Lịch Khám & Chăm Sóc Sức Khỏe')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --tam-primary: #0284c7;
            --tam-primary-rgb: 2, 132, 199;
            --tam-primary-hover: #0369a1;
            --tam-accent: #0d9488;
            --tam-dark: #1e293b;
            --tam-bg: #f8fafc;
            --bs-primary: #0284c7;
            --bs-primary-rgb: 2, 132, 199;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--tam-bg);
            color: var(--tam-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .btn-primary {
            background-color: var(--tam-primary);
            border-color: var(--tam-primary);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--tam-primary-hover);
            border-color: var(--tam-primary-hover);
        }

        .text-primary {
            color: var(--tam-primary) !important;
        }

        .bg-primary {
            background-color: rgba(var(--bs-primary-rgb, 2, 132, 199), var(--bs-bg-opacity, 1)) !important;
        }

        .bg-primary.bg-opacity-10 {
            background-color: rgba(2, 132, 199, 0.1) !important;
        }

        .bg-primary.bg-opacity-25 {
            background-color: rgba(2, 132, 199, 0.25) !important;
        }

        .card {
            border-color: #e2e8f0;
            border-radius: 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.15);
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.25rem rgba(15, 23, 42, 0.08) !important;
        }

        .symptom-chip {
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .symptom-chip:hover {
            background-color: var(--tam-primary) !important;
            color: #fff !important;
            border-color: var(--tam-primary) !important;
        }

        .navbar .nav-link {
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center !important;
            font-size: 0.92rem;
            transition: color 0.15s ease;
        }
        .navbar .nav-link:hover {
            color: var(--tam-primary) !important;
        }
        .navbar-brand {
            white-space: nowrap !important;
        }
        .navbar-nav {
            flex-wrap: nowrap;
        }

        @media print {
            .no-print, nav, footer, .topbar-medical {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    @include('layouts.partials.header')

    <x-alert />

    <main class="flex-grow-1 py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Global Copy Toast Feedback Container -->
    <div class="position-fixed bottom-0 end-0 p-3 no-print" style="z-index: 1080;">
        <div id="globalCopyToast" class="toast align-items-center text-bg-dark border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span id="globalCopyToastMsg">Đã sao chép vào bộ nhớ tạm!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @include('layouts.partials.footer')

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        window.copyWithFeedback = function(text, btnEl, successMessage = 'Đã sao chép thành công!') {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            }

            if (btnEl) {
                const originalHtml = btnEl.getAttribute('data-orig-html') || btnEl.innerHTML;
                btnEl.setAttribute('data-orig-html', originalHtml);
                btnEl.innerHTML = '<i class="bi bi-check2-all"></i> Đã chép';
                btnEl.classList.add('btn-success', 'text-white');
                setTimeout(() => {
                    btnEl.innerHTML = originalHtml;
                    btnEl.classList.remove('btn-success', 'text-white');
                }, 2000);
            }

            const toastEl = document.getElementById('globalCopyToast');
            const msgEl = document.getElementById('globalCopyToastMsg');
            if (toastEl && msgEl && window.bootstrap) {
                msgEl.textContent = successMessage;
                const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2500 });
                toast.show();
            }
        };
    </script>

    @stack('scripts')
</body>
</html>
