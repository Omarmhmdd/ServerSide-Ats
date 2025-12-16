<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetectedLanguage extends Model
{
    protected $table = 'detected_languages';
    protected $fillable = [
        'candidate_id',
        'langauage'
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
