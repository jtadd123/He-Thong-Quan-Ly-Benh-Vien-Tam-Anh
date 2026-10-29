<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khảo Sát Hài Lòng Người Bệnh - BV Đa Khoa Tâm Anh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .header-survey {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            padding: 2.5rem 1rem 3rem 1rem;
            text-align: center;
        }
        .survey-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.06);
            background: white;
        }
        .rating-group {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        .rating-group label {
            cursor: pointer;
            padding: 0.5rem 0.85rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-weight: bold;
        }
        .rating-group input[type="radio"] {
            display: none;
        }
        .rating-group input[type="radio"]:checked + label {
            background-color: #0284c7;
            border-color: #0284c7;
            color: white;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25);
        }
    </style>
</head>
<body>

    <header class="header-survey">
        <div class="container" style="max-width: 600px;">
            <div class="d-inline-flex p-2 bg-white text-primary rounded-circle mb-2">
                <i class="bi bi-hospital fs-4"></i>
            </div>
            <h5 class="fw-bold mb-1">KHẢO SÁT Ý KIẾN NGƯỜI BỆNH</h5>
            <p class="text-white-50 small mb-0">Theo Bộ 83 Tiêu chí Chất lượng Bệnh viện - Bộ Y Tế</p>
        </div>
    </header>

    <main class="container" style="max-width: 600px; margin-top: -2rem;">
        <div class="card survey-card p-4 mb-4">
            <div class="border-bottom pb-3 mb-3">
                <h6 class="fw-bold mb-1 text-slate-800">{{ $appointment->patientProfile->full_name }}</h6>
                <small class="text-muted">Mã khám: <strong>{{ $appointment->appointment_code }}</strong> • {{ $appointment->specialty?->name }} (BS: {{ $appointment->doctor?->user->name }})</small>
            </div>

            <p class="text-muted small mb-4">
                Ý kiến đóng góp quý báu của Quý khách giúp Bệnh viện Đa Khoa Tâm Anh không ngừng nâng cao chất lượng phục vụ và an toàn điều trị.
            </p>

            <form action="{{ route('public.survey.store') }}" method="POST">
                @csrf
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                <input type="hidden" name="patient_profile_id" value="{{ $appointment->patient_profile_id }}">

                <!-- 1. Tiếp đón -->
                <div class="mb-4 text-center">
                    <label class="form-label fw-bold d-block text-slate-800">
                        1. Thái độ & Thủ tục tiếp đón tại quầy Lễ tân
                    </label>
                    <div class="rating-group">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating_reception" id="rec-{{ $i }}" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }} required>
                            <label for="rec-{{ $i }}">{{ $i }} ⭐</label>
                        @endfor
                    </div>
                </div>

                <!-- 2. Bác sĩ -->
                <div class="mb-4 text-center">
                    <label class="form-label fw-bold d-block text-slate-800">
                        2. Kỹ năng tư vấn & Thái độ của Bác sĩ / Điều dưỡng
                    </label>
                    <div class="rating-group">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating_doctor" id="doc-{{ $i }}" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }} required>
                            <label for="doc-{{ $i }}">{{ $i }} ⭐</label>
                        @endfor
                    </div>
                </div>

                <!-- 3. Cơ sở vật chất -->
                <div class="mb-4 text-center">
                    <label class="form-label fw-bold d-block text-slate-800">
                        3. Cơ sở vật chất, buồng khám & Nhà vệ sinh sạch sẽ
                    </label>
                    <div class="rating-group">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating_facilities" id="fac-{{ $i }}" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }} required>
                            <label for="fac-{{ $i }}">{{ $i }} ⭐</label>
                        @endfor
                    </div>
                </div>

                <!-- 4. Thời gian chờ đợi -->
                <div class="mb-4 text-center">
                    <label class="form-label fw-bold d-block text-slate-800">
                        4. Thời gian chờ đợi được khám và nhận thuốc
                    </label>
                    <div class="rating-group">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating_waiting_time" id="wait-{{ $i }}" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }} required>
                            <label for="wait-{{ $i }}">{{ $i }} ⭐</label>
                        @endfor
                    </div>
                </div>

                <!-- 5. NPS Score -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-slate-800 d-block text-center">
                        5. Mức độ sẵn sàng giới thiệu Bệnh viện Tâm Anh cho người thân, bạn bè? (0 = Không bao giờ, 10 = Rất sẵn lòng)
                    </label>
                    <div class="d-flex flex-wrap gap-1 justify-content-center mt-2">
                        @for($n = 0; $n <= 10; $n++)
                            <input type="radio" name="nps_score" id="nps-{{ $n }}" value="{{ $n }}" class="btn-check" {{ $n == 10 ? 'checked' : '' }} required>
                            <label class="btn btn-sm btn-outline-primary" for="nps-{{ $n }}">{{ $n }}</label>
                        @endfor
                    </div>
                </div>

                <!-- Feedback text -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-slate-800">
                        Ý kiến đóng góp thêm hoặc điều Quý khách chưa hài lòng:
                    </label>
                    <textarea name="feedback_text" class="form-control" rows="3" placeholder="Nhập cảm nhận của Quý khách để chúng tôi phục vụ tốt hơn..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-send-fill me-1"></i> Gửi Đánh Giá
                </button>
            </form>
        </div>
    </main>

</body>
</html>
