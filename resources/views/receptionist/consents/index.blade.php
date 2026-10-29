@extends('layouts.admin')

@section('title', 'Quản Lý Bản Cam Đoan Phẫu Thuật & Thủ Thuật Y Khoa - BV Tâm Anh')

@section('content')
<x-page-header title="Bản Cam Đoan Phẫu Thuật & Thủ Thuật Y Khoa (E-Consent)" subtitle="Quản lý chữ ký số và văn bản cam kết chấp nhận phẫu thuật/thủ thuật tuân thủ Thông tư 46/2018/TT-BYT.">
    <x-slot:actions>
        <a href="{{ route('reception.patients.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-people"></i> Danh sách bệnh nhân
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Filter & Search Toolbar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reception.consents.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm theo mã cam đoan, tên bệnh nhân, SĐT hoặc thủ thuật...">
                </div>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                @if(!empty($search) || !empty($date))
                    <a href="{{ route('reception.consents.index') }}" class="btn btn-outline-secondary btn-sm" title="Đặt lại">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table of Consents -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 140px;">Mã Cam Đoan</th>
                        <th>Bệnh Nhân & Người Ký</th>
                        <th>Thủ Thuật Y Khoa</th>
                        <th>Khoa / Bác Sĩ</th>
                        <th>Thời Điểm Ký Số</th>
                        <th class="text-center" style="width: 100px;">Chữ Ký</th>
                        <th class="text-end pe-3" style="width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consents as $c)
                        <tr>
                            <td class="ps-3 font-monospace fw-bold text-primary">
                                {{ $c->consent_code }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    <a href="{{ route('reception.patients.show', $c->patientProfile) }}" class="text-decoration-none text-dark">
                                        {{ $c->patientProfile->full_name }}
                                    </a>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    Người ký: <strong>{{ $c->signer_name }}</strong> ({{ $c->signer_relationship_text }}) &bull; SĐT: {{ $c->signer_phone }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                                    <i class="bi bi-bandaid me-1"></i> {{ $c->procedure_name }}
                                </span>
                                <div class="text-muted mt-1 text-truncate" style="max-width: 260px; font-size: 0.72rem;">
                                    {{ $c->diagnosis_description }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $c->department_name }}</div>
                                <small class="text-muted">BS: {{ $c->doctor ? $c->doctor->full_name_with_title : '--' }}</small>
                            </td>
                            <td>
                                <div class="font-monospace text-dark">{{ $c->signed_at->format('H:i d/m/Y') }}</div>
                                <small class="text-muted">IP: {{ $c->ip_address ?? 'Nội bộ' }}</small>
                            </td>
                            <td class="text-center">
                                @if($c->signature_data)
                                    <img src="{{ $c->signature_data }}" alt="Chữ ký" style="max-height: 35px; max-width: 80px;" class="border rounded bg-white p-1 shadow-sm">
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('reception.consents.print', $c) }}" target="_blank" class="btn btn-outline-primary" title="In bản cam đoan chuẩn Bộ Y Tế">
                                        <i class="bi bi-printer-fill"></i> In
                                    </a>
                                    <form action="{{ route('reception.consents.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản cam đoan này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-medical fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                <h6>Chưa có bản cam đoan phẫu thuật/thủ thuật nào</h6>
                                <p class="small mb-0">Vào hồ sơ bệnh nhân để thực hiện tạo bản cam đoan có chữ ký điện tử.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($consents->hasPages())
            <div class="card-footer bg-white border-top py-2">
                {{ $consents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
