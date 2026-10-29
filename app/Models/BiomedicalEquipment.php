<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiomedicalEquipment extends Model
{
    use HasFactory;

    protected $table = 'biomedical_equipments';

    protected $fillable = [
        'equipment_code',
        'name',
        'model',
        'serial_number',
        'manufacturer',
        'risk_class',
        'department',
        'operational_status',
        'last_calibration_date',
        'next_calibration_due',
        'is_calibration_expired',
    ];

    protected $casts = [
        'last_calibration_date' => 'date',
        'next_calibration_due' => 'date',
        'is_calibration_expired' => 'boolean',
    ];

    public function repairRequests()
    {
        return $this->hasMany(BiomedicalRepairRequest::class, 'equipment_id');
    }
}
