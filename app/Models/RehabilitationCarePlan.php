<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RehabilitationCarePlan extends Model
{
    use HasFactory;

    protected $table = 'rehabilitation_care_plans';

    protected $fillable = [
        'patient_id',
        'diagnosis',
        'fim_score_total',
        'barthel_index',
        'functional_status',
        'assigned_therapy_type',
        'weekly_goals',
        'pt_specialist',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Diễn giải mức độ độc lập chức năng theo thang điểm FIM (18 - 126 điểm)
     */
    public static function interpretFimScore(int $score): string
    {
        if ($score >= 108) return 'complete_independence'; // Độc lập hoàn toàn
        if ($score >= 90) return 'minimal_assistance'; // Cần trợ giúp tối thiểu
        if ($score >= 54) return 'moderate_assistance'; // Cần trợ giúp mức độ vừa
        if ($score >= 36) return 'maximal_assistance'; // Cần trợ giúp mức độ nhiều
        return 'total_assistance'; // Phụ thuộc hoàn toàn
    }
}
