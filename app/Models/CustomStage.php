<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomStage extends Model
{
    protected $fillable = [
        'job_role_id',
        'name',
        'order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
