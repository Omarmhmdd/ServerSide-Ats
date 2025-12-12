<?php

namespace App\Services;
use App\Models\CustomStage;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PipelineService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAllPipelines(): Collection
    {
        return Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->latest()
            ->get();
    }

    /**
     * Get pipeline by ID
     */
    public function getPipelineById(int $id): Pipeline
    {
        $pipeline = Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        return $pipeline;
    }

    /**
     * Create a new pipeline entry
     * Prevents duplicates by checking for existing pipeline for candidate + job_role
     */
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
            if (isset($data['stage_id'])) {
                $existingPipeline->stage_id = $data['stage_id'];
            }
            if (isset($data['intreview_id'])) {
                $existingPipeline->intreview_id = $data['intreview_id'];
            }
            $existingPipeline->save();
            $existingPipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);
            return $existingPipeline;
        }
        
        // Ensure global_stages is set to 'applied' if not provided
        if (!isset($data['global_stages'])) {
            $data['global_stages'] = 'applied';
        }
        
        // Ensure stage_id is null when in global stage
        if (isset($data['global_stages']) && in_array($data['global_stages'], ['applied', 'screen', 'hired', 'rejected'])) {
            $data['stage_id'] = null;
        }
        
        $pipeline = Pipeline::create($data);
        $pipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);
        
        return $pipeline;
    }

    /**
     * Update a pipeline entry
     */
    public function updatePipeline(int $id, array $data): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }
      

        $pipeline->update($data);
        $pipeline->load(['jobRole', 'candidate', 'customStage', 'interview']);

        return $pipeline;
    }

    /**
     * Delete a pipeline entry
     */
    public function deletePipeline(int $id): bool
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        return $pipeline->delete();
    }

    /**
     * Get pipelines by job role ID
     */
    public function getPipelinesByJobRole(int $jobRoleId): Collection
    {
        return Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->where('job_role_id', $jobRoleId)
            ->latest()
            ->get();
    }

    /**
     * Get pipelines by candidate ID
     */
    public function getPipelinesByCandidate(int $candidateId): Collection
    {
        return Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->where('candidate_id', $candidateId)
            ->latest()
            ->get();
    }

    /**
     * Get pipelines by stage ID
     */
    public function getPipelinesByStage(int $stageId): Collection
    {
        return Pipeline::with(['jobRole', 'candidate', 'customStage', 'interview'])
            ->where('stage_id', $stageId)
            ->latest()
            ->get();
    }

    /**
     * Move candidate to next stage in order
     */
    public function moveToNextStage(int $id): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        $next = $pipeline->getNextStage();

        if (!$next) {
            throw new \RuntimeException('Cannot move forward. Already at final state.');
        }

        if ($next === 'screen') {
            $pipeline->update([
                'global_stages' => 'screen',
                'stage_id' => null,
            ]);
        } elseif ($next === 'hired') {
            $pipeline->update([
                'global_stages' => 'hired',
                'stage_id' => null,
            ]);
        } elseif ($next instanceof CustomStage) {
            $pipeline->update([
                'global_stages' => null, // In custom stage
                'stage_id' => $next->id,
            ]);
        }

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }

    /**
     * Reject candidate
     */
    public function rejectCandidate(int $id): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        if (!$pipeline->canReject()) {
            throw new \RuntimeException('Cannot reject candidate. Candidate must be in screen stage or above.');
        }

        $pipeline->update([
            'global_stages' => 'rejected',
            'stage_id' => null,
        ]);

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }

    /**
     * Hire candidate
     */
    public function hireCandidate(int $id): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        // Check if candidate has completed all custom stages
        if (!$pipeline->hasCompletedAllCustomStages() && $pipeline->stage_id) {
            throw new \RuntimeException('Cannot hire candidate. Must complete all custom stages first.');
        }

        $pipeline->update([
            'global_stages' => 'hired',
            'stage_id' => null,
        ]);

        return $pipeline->fresh(['jobRole', 'candidate', 'customStage', 'interview']);
    }

    /**
     * Get pipeline statistics for a job role (candidate counts per stage)
     */
    public function getPipelineStatistics(int $jobRoleId): array
    {
        // Get all pipelines for this job role
        $pipelines = Pipeline::where('job_role_id', $jobRoleId)->get();
        
        // Get custom stages for this job role (ordered)
        $customStages = CustomStage::where('job_role_id', $jobRoleId)
            ->orderBy('order')
            ->get();
        
        $statistics = [];
        
        // Add global stages
        $globalStages = ['applied', 'screen', 'hired', 'rejected'];
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
            $count = $pipelines->where('stage_id', $customStage->id)->count();
            $statistics[] = [
                'type' => 'custom',
                'stage_id' => $customStage->id,
                'stage_name' => $customStage->name,
                'candidate_count' => $count,
                'order' => $customStage->order,
            ];
        }
        
        return $statistics;
    }

    /**
     * Get Kanban board data for a job role
     */
    public function getKanbanBoard(int $jobRoleId): array
    {
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
                'stage_id' => $stage->id,
                'stage_name' => $stage->name,
                'order' => $stage->order,
                'candidates' => $pipelines->where('stage_id', $stage->id)
                    ->map(fn($p) => $this->formatKanbanCandidate($p))
                    ->values()
                    ->toArray(),
            ];
        }
        
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

    /**
     * Format candidate data for Kanban board
     */
    private function formatKanbanCandidate($pipeline): array
    {
        return [
            'pipeline_id' => $pipeline->id,
            'candidate_id' => $pipeline->candidate_id,
            'candidate_name' => ($pipeline->candidate->first_name ?? '') . ' ' . ($pipeline->candidate->last_name ?? ''),
            'interview_id' => $pipeline->intreview_id,
            'interview_scheduled' => $pipeline->interview ? $pipeline->interview->schedule : null,
        ];
    }
}

