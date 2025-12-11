<?php

namespace App\Services;
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
        return Pipeline::with(['jobRole', 'candidate', 'stage', 'interview'])
            ->latest()
            ->get();
    }

    /**
     * Get pipeline by ID
     */
    public function getPipelineById(int $id): Pipeline
    {
        $pipeline = Pipeline::with(['jobRole', 'candidate', 'stage', 'interview'])
            ->find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }

        return $pipeline;
    }

    /**
     * Create a new pipeline entry
     */
    public function createPipeline(array $data): Pipeline
    {  
        
       
        $pipeline = Pipeline::create($data);
        $pipeline->load(['jobRole', 'candidate', 'stage', 'interview']);
        
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
        $pipeline->load(['jobRole', 'candidate', 'stage', 'interview']);

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
        return Pipeline::with(['jobRole', 'candidate', 'stage', 'interview'])
            ->where('job_role_id', $jobRoleId)
            ->latest()
            ->get();
    }

    /**
     * Get pipelines by candidate ID
     */
    public function getPipelinesByCandidate(int $candidateId): Collection
    {
        return Pipeline::with(['jobRole', 'candidate', 'stage', 'interview'])
            ->where('candidate_id', $candidateId)
            ->latest()
            ->get();
    }

    /**
     * Get pipelines by stage ID
     */
    public function getPipelinesByStage(int $stageId): Collection
    {
        return Pipeline::with(['jobRole', 'candidate', 'stage', 'interview'])
            ->where('stage_id', $stageId)
            ->latest()
            ->get();
    }

    /**
     * Move candidate to a different stage
     */
    public function moveCandidateToStage(int $id, int $stageId): Pipeline
    {
        $pipeline = Pipeline::find($id);

        if (!$pipeline) {
            throw new ModelNotFoundException('Pipeline entry not found');
        }
         

        $pipeline->update(['stage_id' => $stageId]);
        $pipeline->load(['jobRole', 'candidate', 'stage', 'interview']);

        return $pipeline;
    }

    /**
     * Get pipeline statistics for a job role (candidate counts per stage)
     */
    public function getPipelineStatistics(int $jobRoleId): array
    {
        $statistics = Pipeline::where('job_role_id', $jobRoleId)
            ->with('stage')
            ->get()
            ->groupBy('stage_id')
            ->map(function ($pipelines, $stageId) {
                $stage = $pipelines->first()->stage;
                return [
                    'stage_id' => $stageId,
                    'stage_name' => $stage ? $stage->name : 'Unknown',
                    'candidate_count' => $pipelines->count(),
                ];
            })
            ->values()
            ->toArray();

        return $statistics;
    }
    protected function validateStageForJobRole(int $jobRoleId, int $stageId): void
{
    $jobRole = \App\Models\JobRoles::find($jobRoleId);
    
    if (!$jobRole) {
        throw new ModelNotFoundException('Job role not found');
    }

    // Check if stage is assigned to this job role
    $stageExists = DB::table('job_role_stages')
        ->where('job_role_id', $jobRoleId)
        ->where('stage_id', $stageId)
        ->exists();

    if (!$stageExists) {
        throw new \InvalidArgumentException(
            "Stage (ID: {$stageId}) is not assigned to job role (ID: {$jobRoleId}). " .
            "Please assign the stage to this job role first."
        );
    }
}
}




