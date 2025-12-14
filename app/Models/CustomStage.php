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

    /**
     * Get the job role this stage belongs to
     */
    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'job_role_id');
    }

    /**
     * Get all pipelines in this stage
     */
    public function pipelines()
    {
        return $this->hasMany(Pipeline::class, 'custom_stage_id');
    }
}