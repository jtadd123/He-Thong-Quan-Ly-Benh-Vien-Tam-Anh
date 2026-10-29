<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PatientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_code',
        'full_name',
        'dob',
        'gender',
        'phone',
        'email',
        'address',
        'identity_card',
        'insurance_code',
        'blood_group',
        'allergies',
        'medical_history',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function sickLeaveCertificates(): HasMany
    {
        return $this->hasMany(SickLeaveCertificate::class);
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(PatientVital::class)->orderBy('recorded_at', 'desc');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PatientNotification::class);
    }

    public function dayBedAdmissions(): HasMany
    {
        return $this->hasMany(DayBedAdmission::class);
    }

    public function clinicalConsultations(): HasMany
    {
        return $this->hasMany(ClinicalConsultation::class);
    }

    public function vaccinationRecords(): HasMany
    {
        return $this->hasMany(VaccinationRecord::class)->orderBy('administered_at', 'desc');
    }

    public function medicationDeliveries(): HasMany
    {
        return $this->hasMany(MedicationDelivery::class)->orderBy('created_at', 'desc');
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(PatientSurvey::class)->orderBy('created_at', 'desc');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ClinicalAuditLog::class)->orderBy('created_at', 'desc');
    }

    public function informedConsents(): HasMany
    {
        return $this->hasMany(InformedConsent::class)->orderBy('created_at', 'desc');
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class)->orderBy('created_at', 'desc');
    }

    public function bloodTransfusions(): HasMany
    {
        return $this->hasMany(BloodTransfusion::class)->orderBy('created_at', 'desc');
    }

    public function equipmentLoans(): HasMany
    {
        return $this->hasMany(EquipmentLoan::class)->orderBy('borrowed_at', 'desc');
    }

    public function nutritionAssessments(): HasMany
    {
        return $this->hasMany(NutritionAssessment::class)->orderBy('assessed_at', 'desc');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(PatientWallet::class);
    }

    public function insuranceTransfers(): HasMany
    {
        return $this->hasMany(InsuranceTransfer::class)->orderBy('created_at', 'desc');
    }

    public function privateInsuranceGuarantees(): HasMany
    {
        return $this->hasMany(PrivateInsuranceGuarantee::class)->orderBy('created_at', 'desc');
    }

    public function medicalRecordRequests(): HasMany
    {
        return $this->hasMany(MedicalRecordRequest::class)->orderBy('created_at', 'desc');
    }

    public function emergencyCustodies(): HasMany
    {
        return $this->hasMany(EmergencyCustody::class)->orderBy('created_at', 'desc');
    }

    public function visitorPasses(): HasMany
    {
        return $this->hasMany(VisitorPass::class)->orderBy('created_at', 'desc');
    }

    public function socialWorkCases(): HasMany
    {
        return $this->hasMany(SocialWorkCase::class)->orderBy('created_at', 'desc');
    }

    public function daySurgeries(): HasMany
    {
        return $this->hasMany(DaySurgery::class)->orderBy('scheduled_at', 'desc');
    }

    public function chronicDiseaseRecalls(): HasMany
    {
        return $this->hasMany(ChronicDiseaseRecall::class)->orderBy('due_recall_date', 'asc');
    }

    public function latestVital()
    {
        return $this->hasOne(PatientVital::class)->latestOfMany('recorded_at');
    }

    public function hasAllergies(): bool
    {
        $al = trim($this->allergies ?? '');
        return !empty($al) && !in_array(mb_strtolower($al), ['không', 'không có', 'chưa ghi nhận', 'none', 'k', 'ko', 'k có']);
    }

    public function hasMedicalHistory(): bool
    {
        $mh = trim($this->medical_history ?? '');
        return !empty($mh) && !in_array(mb_strtolower($mh), ['không', 'không có', 'khỏe mạnh', 'bình thường', 'none']);
    }

    public function getGenderNameAttribute(): string
    {
        return match ($this->gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Khác',
            default => 'Chưa xác định',
        };
    }

    public function getGenderBadgeClassAttribute(): string
    {
        return match ($this->gender) {
            'male' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'female' => 'bg-danger-subtle text-danger border border-danger-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }

    public function getAgeTextAttribute(): string
    {
        if (!$this->dob) {
            return 'N/A';
        }

        $years = $this->dob->age;
        if ($years < 1) {
            $months = (int) $this->dob->diffInMonths(Carbon::now());
            return $months > 0 ? "{$months} tháng tuổi" : "Dưới 1 tháng tuổi";
        }

        return "{$years} tuổi";
    }

    public function getFormattedDobAttribute(): string
    {
        return $this->dob ? $this->dob->format('d/m/Y') : 'Chưa cập nhật';
    }

    public static function generatePatientCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "BN-{$today}-";

        $latest = self::where('patient_code', 'LIKE', "{$prefix}____")
            ->orderBy('patient_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->patient_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('patient_code', $candidate)->exists());

        return $candidate;
    }
}
