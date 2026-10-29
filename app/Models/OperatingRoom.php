<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_name',
        'zone',
        'cleanliness_class',
        'status',
        'has_laminar_flow',
    ];

    protected $casts = [
        'has_laminar_flow' => 'boolean',
    ];

    public function surgicalCases()
    {
        return $this->hasMany(SurgicalCase::class, 'room_id');
    }
}
