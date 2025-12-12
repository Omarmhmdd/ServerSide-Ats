<?php

namespace App\Services;
use App\Models\CustomStage;
use App\Models\JobRoles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class CustomStageService
{
     public function getStagesForJobRole(int $jobRoleId): Collection
    {
        $jobRole = JobRoles::find($jobRoleId);
        
        if (!$jobRole) {
            throw new ModelNotFoundException('Job role not found');
        }

        return $jobRole->customStages;
    }

    
    public function createStage(int $jobRoleId, string $name, int $order): CustomStage
    {
        $jobRole = JobRoles::find($jobRoleId);
        
        if (!$jobRole) {
            throw new ModelNotFoundException('Job role not found');
        }

        // Check if order already exists, shift others if needed
        $existingStage = CustomStage::where('job_role_id', $jobRoleId)
            ->where('order', $order)
            ->first();
        
        if ($existingStage) {
            // Shift existing stages with order >= new order
            CustomStage::where('job_role_id', $jobRoleId)
                ->where('order', '>=', $order)
                ->increment('order');
        }

        return CustomStage::create([
            'job_role_id' => $jobRoleId,
            'name' => $name,
            'order' => $order,
        ]);
    }

    
    public function updateStage(int $id, array $data): CustomStage
    {
        $stage = CustomStage::find($id);
        
        if (!$stage) {
            throw new ModelNotFoundException('Custom stage not found');
        }

        // If order is being changed, handle reordering
        if (isset($data['order']) && $data['order'] != $stage->order) {
            $this->reorderStage($stage, $data['order']);
        }

        $stage->update($data);
        return $stage->fresh();
    }

    
    public function deleteStage(int $id): bool
    {
        $stage = CustomStage::find($id);
        
        if (!$stage) {
            throw new ModelNotFoundException('Custom stage not found');
        }

        // Check if stage is being used in pipelines
        if ($stage->pipelines()->count() > 0) {
            throw new \RuntimeException('Cannot delete stage. It is being used in pipelines.');
        }

        $order = $stage->order;
        $jobRoleId = $stage->job_role_id;
        
        // Delete the stage
        $deleted = $stage->delete();
        
        // Shift remaining stages
        if ($deleted) {
            CustomStage::where('job_role_id', $jobRoleId)
                ->where('order', '>', $order)
                ->decrement('order');
        }

        return $deleted;
    }

   
    public function reorderStages(int $jobRoleId, array $stageOrders): void
    {
        $jobRole = JobRoles::find($jobRoleId);
        
        if (!$jobRole) {
            throw new ModelNotFoundException('Job role not found');
        }

        foreach ($stageOrders as $stageId => $order) {
            CustomStage::where('id', $stageId)
                ->where('job_role_id', $jobRoleId)
                ->update(['order' => $order]);
        }
    }

    
    private function reorderStage(CustomStage $stage, int $newOrder): void
    {
        $oldOrder = $stage->order;
        $jobRoleId = $stage->job_role_id;

        if ($newOrder > $oldOrder) {
            // Moving down: shift stages between old and new order up
            CustomStage::where('job_role_id', $jobRoleId)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->decrement('order');
        } else {
            // Moving up: shift stages between new and old order down
            CustomStage::where('job_role_id', $jobRoleId)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->increment('order');
        }
    }
}



