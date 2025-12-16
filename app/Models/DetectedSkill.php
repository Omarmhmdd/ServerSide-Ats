<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetectedSkill extends Model
{
    protected $table = 'detected_skills';

    protected $fillable = [
        'candidate_id',
        'skill'
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
