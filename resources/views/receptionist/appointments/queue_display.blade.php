<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điện Tử Gọi Khám Ngoại Trú - BV Đa Khoa Tâm Anh</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">

    <style>
        :root {
            --tam-bg: #0b1329;
            --tam-card: #152243;
            --tam-card-border: #233866;
            --tam-cyan: #06b6d4;
            --tam-accent: #10b981;
        }

        body {
            background-color: var(--tam-bg);
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .header-bar {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 2px solid var(--tam-cyan);
            padding: 1rem 2rem;
        }

        .queue-card {
            background-color: var(--tam-card);
            border: 1px solid var(--tam-card-border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            transition: transform 0.2s;
        }

        .queue-card:hover {
            transform: translateY(-2px);
            border-color: var(--tam-cyan);
        }

        .queue-num-badge {
            font-family: 'JetBrains Mono', monospace;
            background: linear-gradient(135deg, #0284c7 0%, #06b6d4 100%);
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: 800;
            padding: 0.25rem 0.85rem;
            border-radius: 8px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .room-badge {
            background-color: #f59e0b;
            color: #000000;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 1rem;
        }

        .live-clock {
            font-family: 'JetBrains Mono', monospace;
            color: var(--tam-cyan);
            font-size: 1.6rem;
            font-weight: 700;
        }

        @keyframes pulseBorder {
            0% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.8); border-color: #06b6d4; }
            70% { box-shadow: 0 0 0 16px rgba(6, 182, 212, 0); border-color: #38bdf8; }
            100% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0); border-color: #06b6d4; }
        }

        .queue-card.pulse-active {
            animation: pulseBorder 1.4s infinite;
            background-color: #1e3a8a !important;
            border-color: #38bdf8 !important;
            transform: scale(1.02);
        }
    </style>
</head>
<body>

    <!-- Header Bar -->
    <header class="header-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                <i class="bi bi-hospital fs-4 text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white text-uppercase" style="letter-spacing: 1px;">BỆNH VIỆN ĐA KHOA TÂM ANH</h4>
                <div class="small text-secondary">HỆ THỐNG ĐIỀU PHỐI & HÀNG ĐỢI TIẾP NHẬN NGOẠI TRÚ TỰ ĐỘNG</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="small text-secondary" id="currentDateString">Đang tải ngày...</div>
                <div class="live-clock" id="liveClock">00:00:00</div>
            </div>
            <button onclick="callTopPatient()" class="btn btn-warning btn-sm fw-bold shadow-sm" title="Phát loa gọi số tiếp theo">
                <i class="bi bi-megaphone-fill me-1"></i> Gọi Số Tiếp Theo
            </button>
            <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-left"></i> Bàn lễ tân
            </a>
            <button onclick="toggleFullScreen()" class="btn btn-primary btn-sm">
                <i class="bi bi-arrows-fullscreen"></i> Toàn màn hình
            </button>
        </div>
    </header>

    <!-- Real-time Broadcast Calling Banner -->
    <div id="liveCallingBanner" class="alert border-0 shadow-lg p-3 mx-4 mt-3 align-items-center justify-content-between" style="display: none; background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); color: #000; border-radius: 12px;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="bi bi-megaphone-fill fs-3"></i>
            </div>
            <div>
                <div class="fw-bold small text-uppercase text-dark opacity-75">Hệ thống loa tự động đang gọi người bệnh:</div>
                <h3 class="fw-black mb-0 font-monospace text-dark" id="callingPatientText">---</h3>
            </div>
        </div>
        <div class="badge bg-dark text-warning fs-5 px-3 py-2 font-monospace" id="callingRoomBadge">PHÒNG KHÁM</div>
    </div>

    <!-- Main Live Queue Container -->
    <main class="container-fluid px-4 py-4">
        <div class="row g-4">
            <!-- Left Column: Patients Waiting to Enter Consultation Room -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary">
                    <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                        <span class="spinner-grow spinner-grow-sm text-info" role="status"></span>
                        DANH SÁCH MỜI VÀO PHÒNG KHÁM (ĐÃ TIẾP NHẬN)
                    </h5>
                    <span class="badge bg-info text-dark fs-6 px-3 py-1">
                        {{ $waitingAppointments->count() }} bệnh nhân đang chờ
                    </span>
                </div>

                <div class="row g-3">
                    @forelse($waitingAppointments as $app)
                        <div class="col-md-6">
                            <div class="queue-card h-100 {{ $app->is_priority ? 'border-danger' : '' }}" id="card-stt-{{ $app->queue_number }}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($app->is_priority)
                                            <div class="queue-num-badge" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                                                <i class="bi bi-star-fill text-warning" style="font-size: 1.2rem;"></i> #{{ str_pad($app->queue_number, 2, '0', STR_PAD_LEFT) }}
                                            </div>
                                        @else
                                            <div class="queue-num-badge">
                                                #{{ str_pad($app->queue_number, 2, '0', STR_PAD_LEFT) }}
                                            </div>
                                        @endif
                                        @if($app->is_priority)
                                            <span class="badge bg-danger text-white border border-danger-subtle small">
                                                <i class="bi bi-star-fill text-warning me-1"></i> ƯU TIÊN
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="room-badge shadow-sm">
                                            <i class="bi bi-door-open-fill me-1"></i> {{ $app->doctor ? $app->doctor->room_number : 'Phòng khám' }}
                                        </div>
                                        <button onclick="announceQueue('{{ addslashes($app->patientProfile->full_name) }}', '{{ $app->queue_number }}', '{{ addslashes($app->doctor ? $app->doctor->room_number : '1') }}')" class="btn btn-sm btn-outline-info" title="Phát loa gọi vào phòng">
                                            <i class="bi bi-megaphone-fill"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="fw-bold fs-5 text-white mb-1">
                                    @php
                                        // Privacy masking: e.g. "Nguyen Van Hung" -> "Nguyễn V*** Hưng"
                                        $nameParts = explode(' ', trim($app->patientProfile->full_name));
                                        if (count($nameParts) > 2) {
                                            $maskedName = $nameParts[0] . ' ' . mb_substr($nameParts[1], 0, 1) . '*** ' . end($nameParts);
                                        } else {
                                            $maskedName = $app->patientProfile->full_name;
                                        }
                                    @endphp
                                    {{ $maskedName }}
                                </div>

                                <div class="small text-secondary mb-2">
                                    Mã BN: <strong class="text-info font-monospace">{{ $app->patientProfile->patient_code }}</strong>
                                </div>

                                <div class="pt-2 border-top border-secondary small d-flex justify-content-between text-secondary">
                                    <span>Khoa: <strong class="text-light">{{ $app->specialty ? $app->specialty->name : '--' }}</strong></span>
                                    <span>BS: <strong class="text-light">{{ $app->doctor ? $app->doctor->full_name_with_title : '--' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-check2-circle fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                            <h5 class="text-secondary">Hiện tại không có bệnh nhân nào trong hàng đợi chờ khám.</h5>
                            <p class="small text-muted">Bệnh nhân sau khi check-in tại quầy sẽ tự động hiển thị số thứ tự tại đây.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Recent Consultations & Hospital Info -->
            <div class="col-lg-4">
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary">
                        <h6 class="fw-bold text-white mb-0 text-uppercase">
                            <i class="bi bi-check-all text-success me-1"></i> Ca Vừa Hoàn Tất Khám
                        </h6>
                        <span class="badge bg-success-subtle text-success small">Hôm nay</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @forelse($recentCompleted as $c)
                            <div class="p-2 px-3 rounded bg-dark bg-opacity-50 border border-secondary d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold text-light small">{{ $c->patientProfile->full_name }}</div>
                                    <small class="text-secondary font-monospace">{{ $c->appointment_code }} &bull; {{ $c->doctor ? $c->doctor->room_number : '' }}</small>
                                </div>
                                <span class="badge bg-success">Đã khám</span>
                            </div>
                        @empty
                            <div class="text-secondary small p-3 text-center border border-secondary rounded">
                                Chưa có ca khám nào hoàn tất trong ngày hôm nay.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Hospital Announcements Card -->
                <div class="p-3 rounded border border-info bg-info bg-opacity-10 text-white">
                    <h6 class="fw-bold text-info mb-2">
                        <i class="bi bi-megaphone-fill me-1"></i> Hướng Dẫn Dành Cho Bệnh Nhân
                    </h6>
                    <ul class="small mb-0 ps-3 text-secondary d-flex flex-column gap-1">
                        <li>Quý khách vui lòng kiểm tra <strong>Số thứ tự (STT)</strong> trên phiếu tiếp nhận.</li>
                        <li>Ngồi chờ tại khu vực ghế trước cửa phòng khám ghi trên phiếu.</li>
                        <li>Khi màn hình và loa gọi đến số thứ tự, xin mời vào phòng khám.</li>
                        <li>Cần trợ giúp khẩn cấp? Vui lòng liên hệ bàn Lễ tân hoặc gọi Hotline <strong>1800 6858</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('vi-VN', { hour12: false });
            document.getElementById('liveClock').textContent = timeString;

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDateString').textContent = now.toLocaleDateString('vi-VN', options);
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Auto reload page every 20 seconds to fetch new check-in queue
        setTimeout(() => {
            window.location.reload();
        }, 20000);

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        // Web Audio API Hospital Chime
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
            } catch (e) {
                console.warn('Audio chime unavailable', e);
            }
        }

        // Announce single patient in queue
        function announceQueue(patientName, queueNumber, roomNumber) {
            playHospitalChime();

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();

                setTimeout(() => {
                    const speechText = `Kính mời bệnh nhân ${patientName}, số thứ tự ${queueNumber}, xin mời vào phòng khám số ${roomNumber}.`;
                    const utterance = new SpeechSynthesisUtterance(speechText);
                    utterance.lang = 'vi-VN';
                    utterance.rate = 0.92;
                    utterance.pitch = 1.05;

                    const voices = window.speechSynthesis.getVoices();
                    const viVoice = voices.find(v => v.lang.includes('vi') || v.lang.includes('VN'));
                    if (viVoice) {
                        utterance.voice = viVoice;
                    }

                    window.speechSynthesis.speak(utterance);
                }, 450);
            }
        }

        function highlightCalledCard(stt, name, room) {
            // Remove pulse from any existing cards
            document.querySelectorAll('.queue-card.pulse-active').forEach(c => c.classList.remove('pulse-active'));

            const card = document.getElementById(`card-stt-${stt}`);
            if (card) {
                card.classList.add('pulse-active');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Top banner
            const banner = document.getElementById('liveCallingBanner');
            const bannerText = document.getElementById('callingPatientText');
            const bannerRoom = document.getElementById('callingRoomBadge');
            if (banner && bannerText && bannerRoom) {
                bannerText.innerText = `BỆNH NHÂN: ${name.toUpperCase()} (STT #${String(stt).padStart(2, '0')})`;
                bannerRoom.innerText = `PHÒNG ${room.toUpperCase()}`;
                banner.style.display = 'flex';

                setTimeout(() => {
                    banner.style.display = 'none';
                    if (card) card.classList.remove('pulse-active');
                }, 15000);
            }
        }

        // Listen for Real-time Calling signals from Receptionist or Doctor desk
        window.addEventListener('storage', function (e) {
            if (e.key === 'tam_anh_queue_call' && e.newValue) {
                try {
                    const data = JSON.parse(e.newValue);
                    if (data && data.name) {
                        announceQueue(data.name, data.stt, data.room);
                        highlightCalledCard(data.stt, data.name, data.room);
                    }
                } catch (err) {}
            }
        });

        // Live waiting list for "Gọi Số Tiếp Theo" button
        const waitingPatients = @json($waitingAppointments->values()->map(fn($a) => [
            'name' => $a->patientProfile->full_name,
            'stt' => $a->queue_number,
            'room' => $a->doctor ? ($a->doctor->room_number ?? '1') : '1'
        ]));

        function callTopPatient() {
            if (waitingPatients.length === 0) {
                playHospitalChime();
                alert('Hiện tại không có bệnh nhân nào trong hàng đợi.');
                return;
            }
            const first = waitingPatients[0];
            announceQueue(first.name, first.stt, first.room);
            highlightCalledCard(first.stt, first.name, first.room);
        }
    </script>
</body>
</html>
