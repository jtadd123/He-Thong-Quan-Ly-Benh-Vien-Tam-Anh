<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorLeave extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'substitute_doctor_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function substituteDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'substitute_doctor_id');
    }

    /**
     * Check if this leave is active on a specific date.
     */
    public function isActiveOn(?string $date = null): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $checkDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $start = $this->start_date->toDateString();
        $end = $this->end_date->toDateString();

        return $checkDate >= $start && $checkDate <= $end;
    }
}
