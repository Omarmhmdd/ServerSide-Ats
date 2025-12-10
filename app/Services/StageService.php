<?php

namespace App\Services;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Models\JobRoles;
class StageService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
      public function getAllStages(): Collection
    {
        return Stage::withCount('pipelines')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get stage by ID
     */
    public function getStageById(int $id): Stage
    {
        $stage = Stage::with(['pipelines.jobRole', 'pipelines.candidate'])
            ->find($id);

        if (!$stage) {
            throw new ModelNotFoundException('Stage not found');
        }

        return $stage;
    }

    /**
     * Create a new stage
     */
    public function createStage(array $data): Stage
    {
        return Stage::create($data);
    }

    /**
     * Update a stage
     */
    public function updateStage(int $id, array $data): Stage
    {
        $stage = Stage::find($id);

        if (!$stage) {
            throw new ModelNotFoundException('Stage not found');
        }

        $stage->update($data);

        return $stage;
    }

    /**
     * Delete a stage
     */
    public function deleteStage(int $id): bool
    {
        $stage = Stage::find($id);

        if (!$stage) {
            throw new ModelNotFoundException('Stage not found');
        }

        // Check if stage is being used in pipelines
        if ($stage->pipelines()->count() > 0) {
            throw new \RuntimeException('Cannot delete stage. It is being used in pipelines.');
        }

        return $stage->delete();
    }
 /*public function getStagesForJobRole(int $jobRoleId): Collection
{
    $jobRole = JobRoles::find($jobRoleId);
    
    if (!$jobRole) {
        throw new ModelNotFoundException('Job role not found');
    }

    return $jobRole->stages()->withCount('pipelines')->get();
}*/

/**
 * Assign stages to a job role with custom ordering
 */
/*public function assignStagesToJobRole(int $jobRoleId, array $stageIds): void
{
    $jobRole = JobRoles::find($jobRoleId);
    
    if (!$jobRole) {
        throw new ModelNotFoundException('Job role not found');
    }

    // Sync stages with order
    $syncData = [];
    foreach ($stageIds as $index => $stageId) {
        $syncData[$stageId] = ['order' => $index + 1];
    }

    $jobRole->stages()->sync($syncData);
}
*/
/**
 * Update stage order for a job role
 */
/*public function updateStageOrderForJobRole(int $jobRoleId, array $stageOrders): void
{
    $jobRole = JobRoles::find($jobRoleId);
    
    if (!$jobRole) {
        throw new ModelNotFoundException('Job role not found');
    }

    foreach ($stageOrders as $stageId => $order) {
        DB::table('job_role_stages')
            ->where('job_role_id', $jobRoleId)
            ->where('stage_id', $stageId)
            ->update(['order' => $order]);
    }
}

}


*/

}