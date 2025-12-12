<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomStage;
class Pipeline extends Model
{
    protected $fillable = [
        'job_role_id',
        'intreview_id',
        'candidate_id',
        'stage_id',
        'global_stages',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];
      public function jobRole()
    {
        return $this->belongsTo(JobRoles::class, 'job_role_id');
    }

    
    public function interview()
    {
        return $this->belongsTo(Interview::class, 'intreview_id');
    }

    /**
     * Get the candidate
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function customStage()
{
    return $this->belongsTo(CustomStage::class, 'stage_id');
}
  public function canMoveNext(): bool
    {
        $next = $this->getNextStage();
        return $next !== null && $next !== 'hired' && $next !== 'rejected';
    }

    
    public function hasCompletedAllCustomStages(): bool
    {
        if ($this->global_stages === 'hired' || $this->global_stages === 'rejected') {
            return true;
        }
        
        if ($this->stage_id) {
            // Check if this is the last custom stage
            $currentStage = CustomStage::find($this->stage_id);
            if (!$currentStage) {
                return false;
            }
            
            $lastCustomStage = CustomStage::where('job_role_id', $this->job_role_id)
                ->orderBy('order', 'desc')
                ->first();
            
            if (!$lastCustomStage) {
                return false;
            }
            
            return $currentStage->order >= $lastCustomStage->order;
        }
        
        return false;
    }
}



