@extends('layouts.admin')

@section('title', 'Hàng Đợi Khám Bệnh - Bác Sĩ')

@section('content')
<x-page-header title="Hàng Đợi Khám Bệnh Hôm Nay" subtitle="Danh sách bệnh nhân tiếp nhận cần khám trong ca trực của Bác sĩ" />

@if(!$doctor)
    <div class="alert alert-warning p-4 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-2"></i>
        Tài khoản của bạn chưa được liên kết với hồ sơ Bác sĩ trong hệ thống. Vui lòng liên hệ Quản trị viên (Admin).
    </div>
@else
    <!-- Doctor Summary Banner -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fs-2" style="width: 58px; height: 58px;">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1 text-dark">{{ $doctor->full_name_with_title }}</h5>
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span>Chuyên khoa: <strong class="text-primary">{{ $doctor->specialty->name ?? '—' }}</strong></span>
                        <span>Phòng khám: <strong>{{ $doctor->room_number }}</strong></span>
                        <span>Ngày trực: <strong>{{ date('d/m/Y') }}</strong></span>
                        <span>Tổng bệnh nhân hôm nay: <strong class="badge bg-primary text-white">{{ $appointments->count() }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Danh sách bệnh nhân ca trực</h5>
            <span class="small text-muted">Ưu tiên thứ tự: Đã Check-in &rarr; Chờ tiếp nhận &rarr; Đã khám</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">STT</th>
                            <th>Mã hẹn</th>
                            <th>Bệnh nhân</th>
                            <th>Ca & Giờ hẹn</th>
                            <th>Triệu chứng bệnh nhân mô tả</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Bàn khám</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $idx => $app)
                            <tr class="{{ $app->status === 'confirmed' ? 'table-primary bg-opacity-25' : '' }}">
                                <td class="ps-3 text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td class="fw-bold font-monospace text-primary">{{ $app->appointment_code }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $app->patientProfile->full_name }}</div>
                                    <small class="text-muted">Mã: <code>{{ $app->patientProfile->patient_code }}</code> &bull; SĐT: {{ $app->patientProfile->phone }}</small>
                                </td>
                                <td>
                                    <div>{{ $app->shift_name }}</div>
                                    <small class="text-muted">{{ $app->appointment_time ? substr($app->appointment_time, 0, 5) : 'Trong ca' }}</small>
                                </td>
                                <td class="small" style="max-width: 250px;">
                                    {{ $app->symptom_description }}
                                </td>
                                <td>
                                    <x-status-badge :status="$app->status" />
                                </td>
                                <td class="text-end pe-3">
                                    @if($app->status === 'confirmed')
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark" title="Phát loa gọi bệnh nhân vào phòng khám"
                                                onclick="callPatientVoice('{{ addslashes($app->patientProfile->full_name) }}', '{{ $app->queue_number }}', '{{ addslashes($doctor->room_number ?? '1') }}', '{{ addslashes($doctor->full_name_with_title) }}')">
                                                <i class="bi bi-megaphone-fill text-danger"></i> Gọi loa
                                            </button>
                                            <a href="{{ route('doctor.examine', $app) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                                                <i class="bi bi-stethoscope"></i> Vào khám
                                            </a>
                                        </div>
                                    @elseif($app->status === 'completed')
                                        <div class="btn-group btn-group-sm">
                                            @if($app->medicalRecord)
                                                <a href="{{ route('doctor.records.show', $app->medicalRecord) }}" class="btn btn-outline-info" title="Xem hồ sơ bệnh án">
                                                    <i class="bi bi-file-earmark-medical"></i> Xem bệnh án
                                                </a>
                                                <a href="{{ route('doctor.records.print', $app->medicalRecord) }}" target="_blank" class="btn btn-outline-dark" title="In đơn thuốc">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            @else
                                                <span class="badge bg-success text-white py-2 px-2">Đã khám xong</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-warning text-dark py-2 px-2">Chờ tiếp nhận</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Hôm nay chưa có lượt khám nào được phân bổ cho bác sĩ.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function playHospitalChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, ctx.currentTime);
            gain1.gain.setValueAtTime(0.3, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.5);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.22);
            gain2.gain.setValueAtTime(0.35, ctx.currentTime + 0.22);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.85);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.22);
            osc2.stop(ctx.currentTime + 0.85);
        } catch (e) {}
    }

    function callPatientVoice(patientName, queueNumber, roomNumber, doctorName) {
        try {
            localStorage.setItem('tam_anh_queue_call', JSON.stringify({
                name: patientName,
                stt: queueNumber,
                room: roomNumber,
                doctor: doctorName,
                time: Date.now()
            }));
        } catch (e) {}

        playHospitalChime();

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            setTimeout(() => {
                const speechText = `Kính mời bệnh nhân ${patientName}, số thứ tự ${queueNumber}, vào phòng khám ${roomNumber} gặp ${doctorName || 'bác sĩ'}.`;
                const utterance = new SpeechSynthesisUtterance(speechText);
                utterance.lang = 'vi-VN';
                utterance.rate = 0.92;
                utterance.pitch = 1.05;

                const voices = window.speechSynthesis.getVoices();
                const viVoice = voices.find(v => v.lang.includes('vi') || v.lang.includes('VN'));
                if (viVoice) utterance.voice = viVoice;

                window.speechSynthesis.speak(utterance);
            }, 450);
        }

        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show align-items-center text-bg-primary border-0 shadow-lg" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-megaphone-fill me-2 fs-5"></i>
                        <strong>Đang phát loa:</strong> Mời STT <b>#${queueNumber}</b> (${patientName}) &rarr; Phòng <b>${roomNumber}</b>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.position-fixed').remove()"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
</script>
@endpush
