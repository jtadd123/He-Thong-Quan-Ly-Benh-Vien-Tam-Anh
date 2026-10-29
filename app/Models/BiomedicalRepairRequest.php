<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiomedicalRepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'reporter_name',
        'urgency',
        'fault_description',
        'status',
        'resolved_at',
        'engineer_name',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(BiomedicalEquipment::class, 'equipment_id');
    }
}
