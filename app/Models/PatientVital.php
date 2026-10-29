<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientVital extends Model
{
    use HasFactory;

    protected $table = 'patient_vitals';

    protected $fillable = [
        'patient_profile_id',
        'appointment_id',
        'recorded_by_user_id',
        'recorded_at',
        'systolic_bp',
        'diastolic_bp',
        'heart_rate',
        'temperature',
        'spo2',
        'weight',
        'height',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'systolic_bp' => 'integer',
        'diastolic_bp' => 'integer',
        'heart_rate' => 'integer',
        'temperature' => 'float',
        'spo2' => 'integer',
        'weight' => 'float',
        'height' => 'float',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * AHA / VNHA Blood Pressure Category.
     */
    public function getBpClassificationAttribute(): array
    {
        if (!$this->systolic_bp || !$this->diastolic_bp) {
            return ['label' => 'Chưa đo HA', 'color' => 'secondary', 'alert' => false];
        }

        $sys = $this->systolic_bp;
        $dia = $this->diastolic_bp;

        if ($sys > 180 || $dia > 120) {
            return ['label' => 'Cơn Tăng Huyết Áp Nguy Cấp', 'color' => 'danger', 'alert' => true];
        }

        if ($sys >= 160 || $dia >= 100) {
            return ['label' => 'Tăng Huyết Áp Độ 2', 'color' => 'danger', 'alert' => true];
        }

        if ($sys >= 140 || $dia >= 90) {
            return ['label' => 'Tăng Huyết Áp Độ 1', 'color' => 'warning', 'alert' => true];
        }

        if ($sys >= 130 || $dia >= 85) {
            return ['label' => 'Bình Thường Cao (Tiền THA)', 'color' => 'info', 'alert' => false];
        }

        if ($sys < 90 || $dia < 60) {
            return ['label' => 'Huyết Áp Thấp', 'color' => 'warning', 'alert' => true];
        }

        return ['label' => 'Huyết Áp Tối Ưu', 'color' => 'success', 'alert' => false];
    }

    /**
     * Calculate Body Mass Index (BMI).
     */
    public function getBmiAttribute(): ?float
    {
        if ($this->weight && $this->height && $this->height > 0) {
            $heightMeters = $this->height / 100;
            return round($this->weight / ($heightMeters * $heightMeters), 1);
        }
        return null;
    }

    /**
     * WHO Asian BMI Classification.
     */
    public function getBmiClassificationAttribute(): ?string
    {
        $bmi = $this->bmi;
        if (!$bmi) {
            return null;
        }

        if ($bmi < 18.5) {
            return 'Thiếu cân';
        }
        if ($bmi < 23.0) {
            return 'Bình thường';
        }
        if ($bmi < 25.0) {
            return 'Tiền béo phì';
        }
        if ($bmi < 30.0) {
            return 'Béo phì Độ 1';
        }
        return 'Béo phì Độ 2';
    }

    public function isFever(): bool
    {
        return $this->temperature !== null && $this->temperature >= 37.5;
    }

    public function isHypoxia(): bool
    {
        return $this->spo2 !== null && $this->spo2 < 95;
    }

    public function isTachycardia(): bool
    {
        return $this->heart_rate !== null && $this->heart_rate > 100;
    }

    public function isBradycardia(): bool
    {
        return $this->heart_rate !== null && $this->heart_rate < 60;
    }

    /**
     * Modified Early Warning Score (MEWS) Calculation.
     * Standard clinical protocol for early detection of deteriorating patients.
     *
     * @return array{score: int, level: string, color: string, badge_class: string, action_text: string, is_emergency: bool}
     */
    public static function calculateMews(?int $sys, ?int $hr, ?float $temp, ?int $spo2): array
    {
        $score = 0;
        $hasData = false;

        // 1. Systolic Blood Pressure (mmHg)
        if ($sys !== null && $sys > 0) {
            $hasData = true;
            if ($sys <= 70) {
                $score += 3;
            } elseif ($sys <= 80) {
                $score += 2;
            } elseif ($sys <= 100) {
                $score += 1;
            } elseif ($sys >= 200) {
                $score += 2;
            }
        }

        // 2. Heart Rate (bpm)
        if ($hr !== null && $hr > 0) {
            $hasData = true;
            if ($hr < 40) {
                $score += 2;
            } elseif ($hr <= 50) {
                $score += 1;
            } elseif ($hr >= 130) {
                $score += 3;
            } elseif ($hr >= 111) {
                $score += 2;
            } elseif ($hr >= 101) {
                $score += 1;
            }
        }

        // 3. Temperature (°C)
        if ($temp !== null && $temp > 0) {
            $hasData = true;
            if ($temp < 35.0) {
                $score += 2;
            } elseif ($temp >= 38.5) {
                $score += 2;
            }
        }

        // 4. SpO2 Oxygen Saturation (%)
        if ($spo2 !== null && $spo2 > 0) {
            $hasData = true;
            if ($spo2 < 90) {
                $score += 3;
            } elseif ($spo2 <= 92) {
                $score += 2;
            } elseif ($spo2 <= 94) {
                $score += 1;
            }
        }

        if (!$hasData) {
            return [
                'score' => 0,
                'level' => 'Chưa đủ dữ liệu',
                'color' => 'secondary',
                'badge_class' => 'bg-secondary text-white',
                'action_text' => 'Chưa ghi nhận đủ các chỉ số sinh hiệu',
                'is_emergency' => false,
            ];
        }

        if ($score >= 5) {
            return [
                'score' => $score,
                'level' => 'Nguy Cơ Cao (Báo Động Đỏ)',
                'color' => 'danger',
                'badge_class' => 'bg-danger text-white animate-pulse',
                'action_text' => 'CẢNH BÁO NGUY CƠ CHUYỂN BIẾN NẶNG! Báo bác sĩ hồi sức/cấp cứu ngay lập tức, chuyển phòng xử trí cấp cứu',
                'is_emergency' => true,
            ];
        }

        if ($score >= 3) {
            return [
                'score' => $score,
                'level' => 'Nguy Cơ Trung Bình (Mức Vàng)',
                'color' => 'warning',
                'badge_class' => 'bg-warning text-dark',
                'action_text' => 'Ưu tiên khám sớm, báo điều dưỡng trưởng và bác sĩ phòng khám theo dõi sát',
                'is_emergency' => false,
            ];
        }

        return [
            'score' => $score,
            'level' => 'Nguy Cơ Thấp (Mức Xanh)',
            'color' => 'success',
            'badge_class' => 'bg-success text-white',
            'action_text' => 'Chỉ số sinh hiệu ổn định, khám bệnh theo thứ tự thường quy',
            'is_emergency' => false,
        ];
    }

    /**
     * Get MEWS Score attribute for this record.
     */
    public function getMewsScoreAttribute(): int
    {
        return $this->mews_classification['score'];
    }

    /**
     * Get MEWS Classification attribute for this record.
     */
    public function getMewsClassificationAttribute(): array
    {
        return self::calculateMews($this->systolic_bp, $this->heart_rate, $this->temperature, $this->spo2);
    }
}
