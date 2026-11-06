@if (session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <div class="app-toast-wrapper position-fixed" style="top: 80px; right: 24px; z-index: 99999; max-width: 420px; min-width: 300px; pointer-events: none;">
        @if (session('success'))
            <div class="app-toast-item shadow bg-white rounded-3 p-3 mb-2 d-flex align-items-center gap-3 border position-relative" style="pointer-events: auto; border-bottom: 3px solid #16a34a !important; animation: toastSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <i class="bi bi-check-circle-fill flex-shrink-0" style="font-size: 1.35rem; color: #16a34a; line-height: 1;"></i>
                <div class="flex-grow-1 text-dark" style="font-size: 0.92rem; font-weight: 500; line-height: 1.45;">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close flex-shrink-0 ms-2" onclick="this.closest('.app-toast-item').remove()" aria-label="Close" style="font-size: 0.75rem; opacity: 0.55; cursor: pointer;"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="app-toast-item shadow bg-white rounded-3 p-3 mb-2 d-flex align-items-center gap-3 border position-relative" style="pointer-events: auto; border-bottom: 3px solid #dc2626 !important; animation: toastSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <i class="bi bi-x-circle-fill flex-shrink-0" style="font-size: 1.35rem; color: #dc2626; line-height: 1;"></i>
                <div class="flex-grow-1 text-dark" style="font-size: 0.92rem; font-weight: 500; line-height: 1.45;">
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close flex-shrink-0 ms-2" onclick="this.closest('.app-toast-item').remove()" aria-label="Close" style="font-size: 0.75rem; opacity: 0.55; cursor: pointer;"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="app-toast-item shadow bg-white rounded-3 p-3 mb-2 d-flex align-items-center gap-3 border position-relative" style="pointer-events: auto; border-bottom: 3px solid #f59e0b !important; animation: toastSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0" style="font-size: 1.35rem; color: #f59e0b; line-height: 1;"></i>
                <div class="flex-grow-1 text-dark" style="font-size: 0.92rem; font-weight: 500; line-height: 1.45;">
                    {{ session('warning') }}
                </div>
                <button type="button" class="btn-close flex-shrink-0 ms-2" onclick="this.closest('.app-toast-item').remove()" aria-label="Close" style="font-size: 0.75rem; opacity: 0.55; cursor: pointer;"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="app-toast-item shadow bg-white rounded-3 p-3 mb-2 d-flex align-items-center gap-3 border position-relative" style="pointer-events: auto; border-bottom: 3px solid #0284c7 !important; animation: toastSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <i class="bi bi-info-circle-fill flex-shrink-0" style="font-size: 1.35rem; color: #0284c7; line-height: 1;"></i>
                <div class="flex-grow-1 text-dark" style="font-size: 0.92rem; font-weight: 500; line-height: 1.45;">
                    {{ session('info') }}
                </div>
                <button type="button" class="btn-close flex-shrink-0 ms-2" onclick="this.closest('.app-toast-item').remove()" aria-label="Close" style="font-size: 0.75rem; opacity: 0.55; cursor: pointer;"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="app-toast-item shadow bg-white rounded-3 p-3 mb-2 border position-relative" style="pointer-events: auto; border-bottom: 3px solid #dc2626 !important; animation: toastSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-octagon-fill text-danger flex-shrink-0" style="font-size: 1.2rem;"></i>
                    <strong class="text-danger flex-grow-1" style="font-size: 0.92rem;">Đã có lỗi xảy ra:</strong>
                    <button type="button" class="btn-close" onclick="this.closest('.app-toast-item').remove()" aria-label="Close" style="font-size: 0.75rem; opacity: 0.55; cursor: pointer;"></button>
                </div>
                <ul class="mb-0 ps-3 small text-secondary">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <style>
        @keyframes toastSlideDown {
            from {
                opacity: 0;
                transform: translateY(-16px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .toast-fade-out {
            opacity: 0 !important;
            transform: translateY(-10px) scale(0.96) !important;
            transition: all 0.4s ease-in-out !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                document.querySelectorAll('.app-toast-item').forEach(function (el) {
                    el.classList.add('toast-fade-out');
                    setTimeout(function () { el.remove(); }, 400);
                });
            }, 4500);
        });
    </script>
@endif
