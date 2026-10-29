<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdrAdverseDrugReport extends Model
{
    use HasFactory;

    protected $table = 'adr_adverse_drug_reports';

    protected $fillable = [
        'patient_id',
        'suspected_medication',
        'reaction_description',
        'severity_level',
        'causality_naranjo_score',
        'causality_category',
        'outcome',
        'reporter_name',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Phân loại mối liên quan thuốc - biến cố theo thang Naranjo Quốc tế
     */
    public static function calculateCausality(int $score): string
    {
        if ($score >= 9) return 'definite'; // Chắc chắn do thuốc
        if ($score >= 5) return 'probable'; // Có khả năng cao
        if ($score >= 1) return 'possible'; // Có thể
        return 'doubtful'; // Hoài nghi / không chắc
    }
}
