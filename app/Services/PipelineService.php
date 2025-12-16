<?php

namespace App\Services;

use App\Models\CustomStage;
use App\Models\JobRole;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\OfferWorkflowService;
use RuntimeException;
use App\Models\Offer;
class PipelineService
{
    protected ?User $user = null;
    protected ?array $recruiterJobRoleIds = null;

    public function __construct(){ 
        $this->user = Auth::user();
        if ($this->user) {
            $this->user->load('role');
        }
    }
    
    private function canAccessPipeline(Pipeline $pipeline): bool{
        if (!$this->user) {
            return false;
        }
        
        if (!isset($this->user->role)) {
            $this->user->load('role');
        }
        if ($this->user->isAdmin()) {
            return true;
        }
        if ($this->user->isRecruiter()) {
            if (!isset($pipeline->jobRole)) {
                $pipeline->load('jobRole');
            }
            if (!$pipeline->jobRole) {
                return false;
            }
            return $pipeline->jobRole->recruiter_id === $this->user->id;
        }
            return true;
    }

    private function getRecruiterJobRoleIds(): array
    {

        
        if (!$this->user) {
            return [];
        }
        
        // Load role relationship if not already loaded
        if (!isset($this->user->role)) {
            $this->user->load('role');
        }
        
        if ($this->user->isRecruiter()) {
            return [];
        }
        
        return JobRole::where('recruiter_id', $this->user->id)
            ->pluck('id')
            ->toArray();
    }

    public function getAllPipelines(): Collection
    {

        
        $query = Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview']);
        
        // Filter by recruiter's job roles if not admin
        if ($this->user) {
            
            if (!isset($this->user->role)) {
                $this->user->load('role');
            }
            
            if ($this->user->isRecruiter()) {
                $jobRoleIds = $this->getRecruiterJobRoleIds();
                if (!empty($jobRoleIds)) {
                    $query->whereIn('job_role_id', $jobRoleIds);
                } else {
                    return $query->whereRaw('1 = 0')->get();
                }
            }
        }
        
        return $query->latest()->get();
    }


    public function getPipelineById(int $id): Pipeline
    {
        $pipeline = Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check access permission
        if (!$this->canAccessPipeline($pipeline)) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        return $pipeline;
    }


    public function createPipeline(array $data): Pipeline
    {
        // Check if pipeline already exists for this candidate and job role
        $existingPipeline = Pipeline::where('candidate_id', $data['candidate_id'])
            ->where('job_role_id', $data['job_role_id'])
            ->first();
        
        if ($existingPipeline) {
            // Update existing pipeline instead of creating duplicate
            if (isset($data['global_stages'])) {
                $existingPipeline->global_stages = $data['global_stages'];
            }
            if (isset($data['custom_stage_id'])) {
                $existingPipeline->custom_stage_id = $data['custom_stage_id'];
            }
            if (isset($data['interview_id'])) {
                $existingPipeline->interview_id = $data['interview_id'];
            }
            $existingPipeline->save();
            $existingPipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);
            return $existingPipeline;
        }
        
        // Ensure global_stages is set to 'applied' if not provided
        if (!isset($data['global_stages'])) {
            $data['global_stages'] = 'applied';
        }
        
        // Ensure custom_stage_id is null when in global stage
        if (isset($data['global_stages']) && in_array($data['global_stages'], ['applied', 'screen', 'offer', 'hired', 'rejected'])) {
            $data['custom_stage_id'] = null;
        }
        
        $pipeline = Pipeline::create($data);
        $pipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);
        
        return $pipeline;
    }


        public function updatePipeline(int $id, array $data): Pipeline
        {
            $pipeline = Pipeline::find($id);

            if (!$pipeline) {
                throw new ModelNotFoundException('Pipeline entry not found');
            }

            // Check access permission
            if (!$this->canAccessPipeline($pipeline)) {
                throw new ModelNotFoundException('Pipeline entry not found');
            }

            // If trying to set stage to 'offer', validate that offer exists
            if (isset($data['global_stages']) && $data['global_stages'] === 'offer') {
                $offer = Offer::where('candidate_id', $pipeline->candidate_id)
                    ->where('role_id', $pipeline->job_role_id)
                    ->first();
                
                if (!$offer) {
                    throw new RuntimeException('Cannot move to offer stage. Please create an offer first for this candidate and job role.');
                }
                
                // Trigger n8n workflow after updating to offer stage
                $pipeline->update($data);
                $pipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);
                OfferWorkflowService::handleOfferStage($pipeline);
                
                return $pipeline;
            }

            $pipeline->update($data);
            $pipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);

            return $pipeline;
        }


    public function deletePipeline(int $id): bool
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check access permission
        if (!$this->canAccessPipeline($pipeline)) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        return $pipeline->delete();
    }


    public function getPipelinesByJobRole(int $jobRoleId): Collection
    {
        
        
        // Check if job role belongs to recruiter
        if ($this->user) {
            if (!isset($this->user->role)) {
                $this->user->load('role');
            }
            
            if ($this->user->isRecruiter()) {
                $jobRole = JobRole::find($jobRoleId);
                if (!$jobRole || $jobRole->recruiter_id !== $this->user->id) {
                    throw new ModelNotFoundException('Job role not found');
                }
            }
        }
        
        return Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->where('job_role_id', $jobRoleId)
            ->latest()
            ->get();
    }


    public function getPipelinesByCandidate(int $candidateId): Collection
    {
        
        
        $query = Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->where('candidate_id', $candidateId);
        
        // Filter by recruiter's job roles if not admin
        if ($this->user) {
            if (!isset($this->user->role)) {
                $this->user->load('role');
            }
            
            if ($this->user->isRecruiter()) {
                $jobRoleIds = $this->getRecruiterJobRoleIds();
                if (!empty($jobRoleIds)) {
                    $query->whereIn('job_role_id', $jobRoleIds);
                } else {
                    return $query->whereRaw('1 = 0')->get();
                }
            }
        }
        
        return $query->latest()->get();
    }


    public function getPipelinesByStage(int $stageId)//: Collection
    {
        
        // Get the custom stage to check job role
        $customStage = CustomStage::find($stageId);
        if (!$customStage) {
            return [];
            //$query->whereRaw('1 = 0')->get();
        }
        
        $query = Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->where('custom_stage_id', $stageId);
        
        // Filter by recruiter's job roles if not admin
        if ($this->user) {
        
            if (!isset($this->user->role)) {
                $this->user->load('role');
            }
            
            if ($this->user->isRecruiter()) {
                $jobRoleIds = $this->getRecruiterJobRoleIds();
                if (!empty($jobRoleIds)) {
                    $query->whereIn('job_role_id', $jobRoleIds);
                } else {
                    return $query->whereRaw('1 = 0')->get();
                }
            }
        }
        
        return $query->latest()->get();
    }


    public function moveCandidateToStage(int $id, int $stageId): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check access permission
        if (!$this->canAccessPipeline($pipeline)) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Verify the stage exists and belongs to the same job role
        $customStage = CustomStage::where('id', $stageId)
            ->where('job_role_id', $pipeline->job_role_id)
            ->first();

        if (!$customStage) {
            throw new ModelNotFoundException('Custom stage not found for this job role');
        }

        $pipeline->update([
            'global_stages' => null, // In custom stage
            'custom_stage_id' => $stageId,
        ]);

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }


    public function moveToNextStage(int $id): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check access permission
        if (!$this->canAccessPipeline($pipeline)) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        $next = $pipeline->getNextStage();

        if (!$next) {
            throw new RuntimeException('Cannot move forward. Already at final state.');
        }

        if ($next === 'screen') {
            $pipeline->update([
                'global_stages' => 'screen',
                'custom_stage_id' => null,
            ]);
        }  elseif ($next === 'offer') {
                    // Check if offer exists before allowing move to offer stage
                    $offer = \App\Models\Offer::where('candidate_id', $pipeline->candidate_id)
                        ->where('role_id', $pipeline->job_role_id)
                        ->first();
                    
                    if (!$offer) {
                        throw new RuntimeException('Cannot move to offer stage. Please create an offer first for this candidate and job role.');
                    }
                    
                    $pipeline->update([
                        'global_stages' => 'offer',
                        'custom_stage_id' => null,
                    ]);
    
                //Call offer workflow for n8n to know stage
                OfferWorkflowService::handleOfferStage($pipeline->fresh());

        } elseif ($next === 'hired') {
            $pipeline->update([
                'global_stages' => 'hired',
                'custom_stage_id' => null,
            ]);
        } elseif ($next instanceof CustomStage) {
            $pipeline->update([
                'global_stages' => null, // In custom stage
                'custom_stage_id' => $next->id,
            ]);
        }

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }


    public function rejectCandidate(int $id): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check access permission
        if (!$this->canAccessPipeline($pipeline)) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        if (!$pipeline->canReject()) {
            throw new RuntimeException('Cannot reject candidate. Candidate must be in screen stage or above.');
        }

        $pipeline->update([
            'global_stages' => 'rejected',
            'custom_stage_id' => null,
        ]);

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }


    public function hireCandidate(int $id): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check access permission
        if (!$this->canAccessPipeline($pipeline)) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check if candidate has completed all custom stages
        if (!$pipeline->hasCompletedAllCustomStages() && $pipeline->custom_stage_id) {
            throw new RuntimeException('Cannot hire candidate. Must complete all custom stages first.');
        }

        $pipeline->update([
            'global_stages' => 'hired',
            'custom_stage_id' => null,
        ]);

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }


    public function getPipelineStatistics(int $jobRoleId): array
    {
        
        
        // Check if job role belongs to recruiter
        if ($this->user) {
    
            if (!isset($this->user->role)) {
                $this->user->load('role');
            }
            
            if ($this->user->isRecruiter()) {
                $jobRole = JobRole::find($jobRoleId);
                if (!$jobRole || $jobRole->recruiter_id !== $this->user->id) {
                    throw new ModelNotFoundException('Job role not found');
                }
            }
        }
        
        // Get all pipelines for this job role
        $pipelines = Pipeline::where('job_role_id', $jobRoleId)->get();
        
        // Get custom stages for this job role (ordered)
        $customStages = CustomStage::where('job_role_id', $jobRoleId)
            ->orderBy('order')
            ->get();
        
        $statistics = [];
        
        // Add global stages
        $globalStages = ['applied', 'screen', 'offer', 'hired', 'rejected'];
        foreach ($globalStages as $globalStage) {
            $count = $pipelines->where('global_stages', $globalStage)->count();
            $statistics[] = [
                'type' => 'global',
                'stage_name' => ucfirst($globalStage),
                'candidate_count' => $count,
            ];
        }
        
        // Add custom stages
        foreach ($customStages as $customStage) {
            $count = $pipelines->where('custom_stage_id', $customStage->id)->count();
            $statistics[] = [
                'type' => 'custom',
                'custom_stage_id' => $customStage->id,
                'stage_name' => $customStage->name,
                'candidate_count' => $count,
                'order' => $customStage->order,
            ];
        }
        
        return $statistics;
    }


    public function getKanbanBoard(int $jobRoleId): array
    {
        
        
        // Check if job role belongs to recruiter
        if ($this->user) {
        
            if (!isset($this->user->role)) {
                $this->user->load('role');
            }
            
            if ($this->user->isRecruiter()) {
                $jobRole = JobRole::find($jobRoleId);
                if (!$jobRole || $jobRole->recruiter_id !== $this->user->id) {
                    throw new ModelNotFoundException('Job role not found');
                }
            }
        }
        
        $pipelines = Pipeline::where('job_role_id', $jobRoleId)
            ->with(['candidate', 'interview', 'customStage'])
            ->get();
        
        $customStages = CustomStage::where('job_role_id', $jobRoleId)
            ->orderBy('order')
            ->get();
        
        $kanban = [];
        
        // Applied column
        $kanban[] = [
            'stage_type' => 'global',
            'stage_name' => 'Applied',
            'candidates' => $pipelines->where('global_stages', 'applied')
                ->map(fn($p) => $this->formatKanbanCandidate($p))
                ->values()
                ->toArray(),
        ];
        
        // Screen column
        $kanban[] = [
            'stage_type' => 'global',
            'stage_name' => 'Screen',
            'candidates' => $pipelines->where('global_stages', 'screen')
                ->map(fn($p) => $this->formatKanbanCandidate($p))
                ->values()
                ->toArray(),
        ];
        
        // Custom stages columns
        foreach ($customStages as $stage) {
            $kanban[] = [
                'stage_type' => 'custom',
                'custom_stage_id' => $stage->id,
                'stage_name' => $stage->name,
                'order' => $stage->order,
                'candidates' => $pipelines->where('custom_stage_id', $stage->id)
                    ->map(fn($p) => $this->formatKanbanCandidate($p))
                    ->values()
                    ->toArray(),
            ];
        }
        
        // Offer column
        $kanban[] = [
            'stage_type' => 'global',
            'stage_name' => 'Offer',
            'candidates' => $pipelines->where('global_stages', 'offer')
                ->map(fn($p) => $this->formatKanbanCandidate($p))
                ->values()
                ->toArray(),
        ];
        
        // Hired column
        $kanban[] = [
            'stage_type' => 'global',
            'stage_name' => 'Hired',
            'candidates' => $pipelines->where('global_stages', 'hired')
                ->map(fn($p) => $this->formatKanbanCandidate($p))
                ->values()
                ->toArray(),
        ];
        
        // Rejected column
        $kanban[] = [
            'stage_type' => 'global',
            'stage_name' => 'Rejected',
            'candidates' => $pipelines->where('global_stages', 'rejected')
                ->map(fn($p) => $this->formatKanbanCandidate($p))
                ->values()
                ->toArray(),
        ];
        
        return $kanban;
    }


    private function formatKanbanCandidate($pipeline): array
    {
        return [
            'pipeline_id' => $pipeline->id,
            'candidate_id' => $pipeline->candidate_id,
            'candidate_name' => ($pipeline->candidate->first_name ?? '') . ' ' . ($pipeline->candidate->last_name ?? ''),
            'interview_id' => $pipeline->interview_id,
            'interview_scheduled' => $pipeline->interview ? $pipeline->interview->schedule : null,
        ];
    }
}




