<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = [
        'recruiter_id',
        'job_role_id',
        'meta_data_id',
        'portfolio',
        'linkedin_url',
        'github_url',
        'source',
        'first_name',
        'last_name',
        'location',
        'notes',
        'phone',
        'attachments',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
