<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgicalSpongeCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgical_case_id',
        'item_name',
        'initial_count',
        'added_count',
        'before_closure_count',
        'final_count',
        'is_matched',
        'scrub_nurse_name',
        'circulating_nurse_name',
        'discrepancy_reason',
    ];

    protected $casts = [
        'is_matched' => 'boolean',
    ];

    public function surgicalCase()
    {
        return $this->belongsTo(SurgicalCase::class, 'surgical_case_id');
    }
}
