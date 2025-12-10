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
      public function jobRole()
    {
        return $this->belongsTo(JobRoles::class, 'job_role_id');
    }

    /**
     * Get the interview
     */
    public function interview()
    {
        return $this->belongsTo(Interview::class, 'intreview_id');
    }

    /**
     * Get the candidate
     */
    public function candidate()
    {
        return $this->belongsTo(Candidates::class, 'candidate_id');
    }

    /**
     * Get the stage
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

}
