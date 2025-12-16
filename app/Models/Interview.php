<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
     protected $table = 'intreviews';
    protected $fillable = [
        'interveiwer_id',
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
    public function interviewer(){
        return $this->belongsTo(User::class, 'intreveiwer_id');
    }

    /**
     * Get the job role
     */
    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'job_role_id');
    }

    /**
     * Get the candidate
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function scoreCard(){
        return $this->hasOne(ScoreCard::class , "interview_id" , 'id');
    }
}



