<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    protected $fillable = [
        'job_role_id',
        'intreview_id',
        'candidate_id',
        'stage_id',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

}
