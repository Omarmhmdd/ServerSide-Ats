<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaData extends Model
{
    protected $fillable = [
        'candidate_id',
        'parsed_CV_text',
        'git_hub_repos_json',
        'skills_detected',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
