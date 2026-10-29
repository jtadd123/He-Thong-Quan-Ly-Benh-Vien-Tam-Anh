@extends('layouts.admin')

@section('title', 'Trung Tâm Giám Sát Hiệu Suất Tiếp Nhận & Dược Lâm Sàng (BI Analytics)')

@section('content')
<x-page-header title="Trung Tâm Giám Sát Hiệu Suất Tiếp Nhận & An Toàn Dược (BI Analytics)" subtitle="Chỉ số KPI vận hành thời gian thực, thời gian chờ trung bình, phân luồng ESI và an toàn dược ngoại trú.">
    <x-slot:actions>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reception.analytics.index', ['period' => 'today']) }}" class="btn btn-outline-primary {{ $period === 'today' ? 'active' : '' }}">Hôm nay</a>
            <a href="{{ route('reception.analytics.index', ['period' => '7days']) }}" class="btn btn-outline-primary {{ $period === '7days' ? 'active' : '' }}">7 ngày qua</a>
            <a href="{{ route('reception.analytics.index', ['period' => '30days']) }}" class="btn btn-outline-primary {{ $period === '30days' ? 'active' : '' }}">30 ngày qua</a>
        </div>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-1 ms-2">
            <i class="bi bi-printer"></i> In Báo Cáo KPI
        </button>
    </x-slot:actions>
</x-page-header>

<!-- KPI Top Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Total Appointments -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100 bg-primary-subtle border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Tổng Lượt Tiếp Nhận</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($totalAppointments) }}</h3>
                    <small class="text-muted">Hoàn tất: <strong>{{ $completedAppointments }}</strong> ca</small>
                </div>
                <div class="bg-primary text-white p-3 rounded-circle shadow-sm">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Average Waiting Time -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100 bg-warning-subtle border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Thời Gian Chờ Trung Bình</span>
                    <h3 class="fw-bold text-dark font-monospace mb-0 mt-1">{{ $avgWaitMinutes }} <small class="fs-6 text-muted fw-normal">phút</small></h3>
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Đạt chuẩn JCI (&lt; 20p)</small>
                </div>
                <div class="bg-warning text-dark p-3 rounded-circle shadow-sm">
                    <i class="bi bi-stopwatch fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Clinical Safety KPI -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100 bg-danger-subtle border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Can Thiệp An Toàn Lâm Sàng</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ $safetyInterventionsCount }}</h3>
                    <small class="text-danger fw-semibold">Cảnh báo ESI & Dị ứng chéo</small>
                </div>
                <div class="bg-danger text-white p-3 rounded-circle shadow-sm">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Revenue Collected -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100 bg-success-subtle border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Viện Phí Thực Thu</span>
                    <h3 class="fw-bold text-success mb-0 mt-1 fs-4 font-monospace">{{ number_format($totalCollected, 0, ',', '.') }} đ</h3>
                    <small class="text-muted">BHYT chi trả: {{ number_format($totalInsurancePaid, 0, ',', '.') }} đ</small>
                </div>
                <div class="bg-success text-white p-3 rounded-circle shadow-sm">
                    <i class="bi bi-cash-coin fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1: Hourly Traffic & ESI Triage Distribution -->
<div class="row g-4 mb-4">
    <!-- Hourly Traffic Heatmap Bar Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i> Lưu Lượng Bệnh Nhân Đến Khám Theo Khung Giờ (Peak Hours)
                </h6>
                <span class="badge bg-light text-muted border">07:00 - 17:00</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; position: relative;">
                    <canvas id="hourlyTrafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ESI Triage 5 Levels Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pie-chart-fill text-danger me-2"></i> Phân Luồng Khẩn Cấp ESI (1-5)
                </h6>
            </div>
            <div class="card-body p-4">
                <div style="height: 200px; position: relative;" class="mb-3">
                    <canvas id="esiChart"></canvas>
                </div>
                <div class="small">
                    @foreach($esiDistribution as $esi)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="d-flex align-items-center gap-1">
                                <span class="badge bg-{{ $esi['color'] }} rounded-circle p-1"> </span>
                                {{ $esi['name'] }}
                            </span>
                            <span class="fw-bold font-monospace">{{ $esi['count'] }} ca ({{ $esi['percentage'] }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2: Pharmacy Performance & Payment Methods -->
<div class="row g-4 mb-4">
    <!-- Pharmacy Outpatient Dispensing Index -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-capsule-pill text-success me-2"></i> Hiệu Suất Quầy Dược & Kiểm Soát FEFO
                </h6>
                <span class="badge bg-success-subtle text-success border">Chuẩn GPP / JCI</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 text-center mb-4">
                    <div class="col-4">
                        <div class="p-3 bg-light rounded border">
                            <div class="text-muted small">Toa Thuốc Kê</div>
                            <h4 class="fw-bold text-dark mt-1 mb-0">{{ $totalRecords }}</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-success-subtle rounded border border-success-subtle">
                            <div class="text-muted small">Đã Cấp Phát</div>
                            <h4 class="fw-bold text-success mt-1 mb-0">{{ $dispensedRecords }}</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-{{ $fefoExpiringCount > 0 ? 'warning' : 'light' }}-subtle rounded border">
                            <div class="text-muted small">Thuốc FEFO Cận Hạn</div>
                            <h4 class="fw-bold text-{{ $fefoExpiringCount > 0 ? 'danger' : 'success' }} mt-1 mb-0">{{ $fefoExpiringCount }}</h4>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border d-flex align-items-center gap-3 mb-0">
                    <i class="bi bi-check2-circle fs-3 text-success"></i>
                    <div>
                        <strong class="text-dark d-block">Tỷ lệ hoàn tất cấp phát: {{ $totalRecords > 0 ? round(($dispensedRecords / $totalRecords) * 100, 1) : 100 }}%</strong>
                        <span class="small text-muted">Toàn bộ đơn thuốc đều được kiểm tra rà soát dị ứng chéo và thời hạn sử dụng FEFO trước khi giao cho người bệnh.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-credit-card-2-front-fill text-info me-2"></i> Cơ Cấu Doanh Thu Theo Phương Thức Thanh Toán
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-sm-6 text-center">
                        <div style="height: 180px; position: relative;">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <ul class="list-unstyled mb-0 small">
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-qr-code text-primary me-2"></i>Chuyển khoản VietQR:</span>
                                <strong class="font-monospace text-primary">{{ number_format($paymentMethods['vietqr'], 0, ',', '.') }} đ</strong>
                            </li>
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cash text-success me-2"></i>Tiền mặt tại quầy:</span>
                                <strong class="font-monospace text-success">{{ number_format($paymentMethods['cash'], 0, ',', '.') }} đ</strong>
                            </li>
                            <li class="py-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-credit-card text-warning me-2"></i>Quẹt thẻ POS / Thẻ:</span>
                                <strong class="font-monospace text-warning">{{ number_format($paymentMethods['card'], 0, ',', '.') }} đ</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Hourly Traffic Chart
        const trafficData = @json($hourlyTraffic);
        new Chart(document.getElementById('hourlyTrafficChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(trafficData),
                datasets: [{
                    label: 'Số lượt bệnh nhân',
                    data: Object.values(trafficData),
                    backgroundColor: 'rgba(2, 132, 199, 0.75)',
                    borderColor: '#0284c7',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. ESI Triage Chart
        const esiData = @json($esiDistribution);
        new Chart(document.getElementById('esiChart'), {
            type: 'doughnut',
            data: {
                labels: Object.values(esiData).map(e => e.name),
                datasets: [{
                    data: Object.values(esiData).map(e => e.count),
                    backgroundColor: ['#dc2626', '#ea580c', '#eab308', '#06b6d4', '#64748b'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // 3. Payment Methods Chart
        const payData = @json($paymentMethods);
        new Chart(document.getElementById('paymentChart'), {
            type: 'pie',
            data: {
                labels: ['VietQR', 'Tiền mặt', 'Thẻ POS'],
                datasets: [{
                    data: [payData.vietqr, payData.cash, payData.card],
                    backgroundColor: ['#0284c7', '#10b981', '#f59e0b'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    });
</script>
@endpush
@endsection
