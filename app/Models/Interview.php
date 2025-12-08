<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $fillable = [
        'intreveiwer_id',
        'job_role_id',
        'candidate_id',
        'type',
        'schedule',
        'duration',
        'meeting_link',
        'rubric',
        'notes',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
