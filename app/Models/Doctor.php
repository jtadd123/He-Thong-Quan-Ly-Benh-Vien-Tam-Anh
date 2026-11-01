<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'qualification',
        'consultation_fee',
        'bio',
        'room_number',
        'avatar',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function getFullNameWithTitleAttribute(): string
    {
        $qualification = $this->qualification ? "{$this->qualification} " : '';
        $name = $this->user ? $this->user->name : 'Bác sĩ';
        return "{$qualification}{$name}";
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    /**
     * Check if doctor has any active shift today.
     */
    public function getIsOnDutyTodayAttribute(): bool
    {
        $today = strtolower(now()->format('l'));
        return $this->schedules->where('day_of_week', $today)->where('is_active', true)->isNotEmpty();
    }

    /**
     * Get active shifts for today.
     */
    public function getTodaySchedulesAttribute()
    {
        $today = strtolower(now()->format('l'));
        return $this->schedules->where('day_of_week', $today)->where('is_active', true);
    }
}
