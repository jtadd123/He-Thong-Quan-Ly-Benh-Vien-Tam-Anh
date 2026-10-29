@extends('layouts.admin')

@section('title', 'Trung Tâm Thông Báo Zalo ZNS & SMS')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-chat-quote-fill text-primary me-2"></i>Trung Tâm Thông Báo Đa Kênh (Notification Hub)
            </h4>
            <p class="text-muted small mb-0">Giám sát và gửi tin nhắn Zalo ZNS / SMS Brandname nhắc lượt khám, thông báo đơn thuốc theo thời gian thực.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-envelope-paper-fill fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Tổng tin đã phát</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-chat-dots-fill fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Zalo ZNS Template</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['zalo_zns']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-phone-vibrate-fill fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">SMS Brandname</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['sms']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Tin nhắn hôm nay</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['today']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <form action="{{ route('reception.notifications.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm theo SĐT, Tên BN hoặc Mã BN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="channel" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả kênh gửi --</option>
                        <option value="zalo_zns" {{ request('channel') == 'zalo_zns' ? 'selected' : '' }}>Zalo ZNS</option>
                        <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS Brandname</option>
                        <option value="in_app" {{ request('channel') == 'in_app' ? 'selected' : '' }}>In-App Notice</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Loại thông báo --</option>
                        <option value="queue_alert" {{ request('type') == 'queue_alert' ? 'selected' : '' }}>Cảnh báo sắp đến lượt khám</option>
                        <option value="prescription_ready" {{ request('type') == 'prescription_ready' ? 'selected' : '' }}>Thông báo nhận thuốc</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Lọc</button>
                    @if(request()->anyFilled(['search', 'channel', 'type']))
                        <a href="{{ route('reception.notifications.index') }}" class="btn btn-sm btn-outline-secondary">Xóa</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Bệnh Nhân / SĐT</th>
                        <th>Kênh / Mẫu Tin</th>
                        <th>Nội Dung Chi Tiết</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian Gửi</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notif)
                        <tr>
                            <td>{{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $notif->patientProfile->full_name }}</strong>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $notif->recipient_phone }}</small>
                            </td>
                            <td>
                                <div>{!! $notif->channel_badge !!}</div>
                                <small class="text-muted d-block mt-1">
                                    {{ $notif->notification_type === 'queue_alert' ? 'Nhắc lượt khám' : 'Nhận đơn thuốc' }}
                                </small>
                            </td>
                            <td style="max-width: 320px;">
                                <strong class="text-dark small d-block mb-1">{{ $notif->title }}</strong>
                                <p class="text-muted small mb-0 text-truncate" title="{{ $notif->message_content }}">{{ $notif->message_content }}</p>
                            </td>
                            <td>
                                {!! $notif->status_badge !!}
                            </td>
                            <td>
                                <small class="text-dark fw-semibold d-block">{{ $notif->created_at->format('H:i') }}</small>
                                <small class="text-muted">{{ $notif->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('public.journey.show', $notif->appointment->appointment_code) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Xem hành trình khám">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Hành trình
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-secondary"></i>
                                Chưa có tin nhắn thông báo nào được gửi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
