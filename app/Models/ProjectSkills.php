<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSkills extends Model
{
    protected $table = 'project_skills';

    protected $fillable = [
        'project_temp_id',
        'project_id',
        'skill',
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
