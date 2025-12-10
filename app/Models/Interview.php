<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
     protected $table = 'intreviews';
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
     public function interviewer()
    {
        return $this->belongsTo(User::class, 'intreveiwer_id');
    }

    /**
     * Get the job role
     */
    public function jobRole()
    {
        return $this->belongsTo(JobRoles::class, 'job_role_id');
    }

    /**
     * Get the candidate
     */
    public function candidate()
    {
        return $this->belongsTo(Candidates::class, 'candidate_id');
    }
}



