<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điện Tử Theo Dõi Ca Phẫu Thuật - Bệnh Viện Tâm Anh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .board-header {
            background: linear-gradient(135deg, #0369a1 0%, #0f766e 100%);
            padding: 1.25rem 2rem;
            border-bottom: 2px solid #38bdf8;
        }
        .table-dark-custom {
            --bs-table-bg: #1e293b;
            --bs-table-striped-bg: #1a2234;
            color: #f1f5f9;
        }
        .pulse-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ef4444;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body>
    <header class="board-header d-flex justify-content-between align-items-center shadow">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-hospital fs-2 text-warning"></i>
            <div>
                <h4 class="fw-bold mb-0 text-white">BỆNH VIỆN ĐA KHOA TÂM ANH</h4>
                <div class="small text-white-50">BẢNG ĐIỆN TỬ THEO DÕI TIẾN TRÌNH PHẪU THUẬT (SẢNH CHỜ THÂN NHÂN)</div>
            </div>
        </div>
        <div class="text-end">
            <div class="badge bg-dark border border-secondary px-3 py-2 fs-6" id="liveClock">
                {{ date('H:i:s • d/m/Y') }}
            </div>
            <small class="d-block text-white-50 mt-1"><i class="bi bi-shield-lock me-1"></i>Mã hóa bảo mật danh tính người bệnh</small>
        </div>
    </header>

    <main class="container-fluid p-4">
        <div class="card bg-dark border-secondary shadow-lg rounded-3">
            <div class="table-responsive">
                <table class="table table-dark-custom table-striped table-hover align-middle mb-0">
                    <thead class="table-dark text-uppercase small text-secondary">
                        <tr>
                            <th class="py-3">Mã Hồ Sơ</th>
                            <th>Bệnh Nhân (Mã Hóa)</th>
                            <th>Phòng Mổ</th>
                            <th>Bác Sĩ Mổ Chính</th>
                            <th>Phẫu Thuật Chỉ Định</th>
                            <th>Trạng Thái Tiến Trình</th>
                            <th class="text-end pe-3">Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6">
                        @forelse($todayCases as $c)
                        @php
                            // Mã hóa danh tính: "Nguyễn Văn Hưng" -> "Nguyễn V** H***"
                            $names = explode(' ', $c->patient->full_name ?? 'Bệnh nhân');
                            $maskedName = '';
                            foreach($names as $idx => $part) {
                                if ($idx === 0) {
                                    $maskedName .= $part . ' ';
                                } else {
                                    $maskedName .= mb_substr($part, 0, 1) . '** ';
                                }
                            }
                            $maskedName = trim($maskedName);

                            $phaseText = match($c->phase) {
                                'scheduled' => 'ĐANG CHỜ PHẪU THUẬT',
                                'pre_op' => 'ĐANG TIỀN PHẪU / TIỀN MÊ',
                                'anesthesia' => 'ĐANG GÂY MÊ HỒI SỨC',
                                'surgery' => 'ĐANG TIẾN HÀNH PHẪU THUẬT',
                                'hemostasis' => 'ĐANG KHÂU ĐÓNG VẾT MỔ',
                                'pacu' => 'ĐANG HỒI TỈNH TẠI PACU',
                                'discharged' => 'ĐÃ XUẤT VIỆN / VỀ BUỒNG NỘI TRÚ',
                                default => 'ĐANG XỬ LÝ'
                            };

                            $statusClass = match($c->phase) {
                                'surgery' => 'bg-danger text-white',
                                'pacu' => 'bg-warning text-dark',
                                'discharged' => 'bg-success text-white',
                                default => 'bg-secondary text-white'
                            };
                        @endphp
                        <tr>
                            <td class="font-monospace fw-bold text-info">{{ $c->case_code }}</td>
                            <td class="fw-bold">{{ $maskedName }}</td>
                            <td><span class="badge bg-primary px-2 py-1">{{ $c->room->room_number ?? 'P.Mổ' }}</span></td>
                            <td>{{ $c->leadSurgeon->user->name ?? 'Bác sĩ PTV' }}</td>
                            <td class="text-light">{{ $c->surgical_procedure }}</td>
                            <td>
                                <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill fw-semibold">
                                    @if($c->phase === 'surgery')
                                        <span class="pulse-indicator me-1"></span>
                                    @endif
                                    {{ $phaseText }}
                                </span>
                            </td>
                            <td class="text-end pe-3 text-secondary">
                                {{ $c->actual_start ? $c->actual_start->format('H:i') : $c->scheduled_start->format('H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                Hiện chưa có ca phẫu thuật nào đang diễn ra trong phòng mổ.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3 mt-3 text-secondary small">
            <div class="col-md-3 d-flex align-items-center gap-2">
                <span class="badge bg-secondary p-2 rounded-circle"></span> Chờ phẫu thuật / Tiền mê
            </div>
            <div class="col-md-3 d-flex align-items-center gap-2">
                <span class="badge bg-danger p-2 rounded-circle"></span> Đang tiến hành phẫu thuật
            </div>
            <div class="col-md-3 d-flex align-items-center gap-2">
                <span class="badge bg-warning p-2 rounded-circle"></span> Hồi tỉnh tại PACU (Theo dõi thuốc mê)
            </div>
            <div class="col-md-3 d-flex align-items-center gap-2">
                <span class="badge bg-success p-2 rounded-circle"></span> Hoàn tất, chuẩn bị đón người thân
            </div>
        </div>
    </main>

    <script>
        // Tự động cập nhật đồng hồ
        setInterval(() => {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('vi-VN') + ' • ' + now.toLocaleDateString('vi-VN');
            const el = document.getElementById('liveClock');
            if (el) el.innerText = timeStr;
        }, 1000);

        // Tự động reload trang mỗi 30 giây để cập nhật tiến trình mới nhất
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
