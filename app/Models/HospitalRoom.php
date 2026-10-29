<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'department',
        'room_type',
        'floor',
    ];

    public function beds(): HasMany
    {
        return $this->hasMany(HospitalBed::class, 'room_id');
    }
}
