<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespositoryTechnologies extends Model
{
    protected $table = 'repository_technologies';

    protected $fillable = [
        'repository_id',
        'technology',
        'repo_temp_id'
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
