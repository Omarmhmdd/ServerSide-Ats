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
        'stage_id', // nullable - points to custom_stages
        'global_stages', // 'applied', 'screen', 'offer', 'hired', 'rejected', or null
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'job_role_id');
    }

    public function interview()
    {
        return $this->belongsTo(Interview::class, 'intreview_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function customStage()
    {
        return $this->belongsTo(CustomStage::class, 'custom_stage_id');
    }

    /**
     * Get next stage in the pipeline order
     * REQUIRED: Called by PipelineService::moveToNextStage() on line 190
     */
    public function getNextStage()
    {
        if ($this->global_stages === 'applied') {
            return 'screen'; // Next is screen
        }
        
        if ($this->global_stages === 'screen') {
            // Get first custom stage for this job role
            $firstCustomStage = CustomStage::where('job_role_id', $this->job_role_id)
                ->orderBy('order')
                ->first();
            
            return $firstCustomStage ?: 'offer'; // If no custom stages, go to offer
        }
        
        if ($this->stage_id) {
            // Currently in custom stage, get next custom stage
            $currentStage = CustomStage::find($this->stage_id);
            if (!$currentStage) {
                return null;
            }
            
            $nextStage = CustomStage::where('job_role_id', $this->job_role_id)
                ->where('order', '>', $currentStage->order)
                ->orderBy('order')
                ->first();
            
            return $nextStage ?: 'offer'; // No more custom stages, next is offer
        }
        
        if ($this->global_stages === 'offer') {
            return 'hired'; // After offer, next is hired
        }
        
        return null; // Already at final state
    }

    public function canReject(): bool
    {
        return $this->global_stages !== 'applied' 
            && $this->global_stages !== 'hired' 
            && $this->global_stages !== 'rejected';
    }

    /**
     * Check if candidate can move to next stage
     */
    public function canMoveNext(): bool
    {
        $next = $this->getNextStage();
        return $next !== null && $next !== 'hired' && $next !== 'rejected';
    }

    /**
     * Check if candidate has completed all custom stages
     * REQUIRED: Called by PipelineService::hireCandidate()
     */
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