<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreCard extends Model
{
    // Fillable
    protected $fillable = [
        'interview_id',
        'candidate_id',
        'overall_recommnedation_id',
        'summary',
        'written_evidence',
        'ctieria',
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
