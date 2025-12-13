<?php

namespace App\Services;

use App\Models\CustomStage;
use App\Models\JobRole;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CustomStageService
{
    
    private function canAccessJobRole(JobRole $jobRole): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Load role relationship if not already loaded
        if (!isset($user->role)) {
            $user->load('role');
        }
        
        // Admin can access everything
        if ($user->isAdmin()) {
            return true;
        }
        
        // Recruiter can only access their own job roles
        if ($user->isRecruiter()) {
            return $jobRole->recruiter_id === $user->id;
        }
        
        // Interviewer can view custom stages (read-only)
        return true;
    }

    
    private function getRecruiterJobRoleIds(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }
        
        // Load role relationship if not already loaded
        if (!isset($user->role)) {
            $user->load('role');
        }
        
        if (!$user->isRecruiter()) {
            return [];
        }
        
        return JobRole::where('recruiter_id', $user->id)
            ->pluck('id')
            ->toArray();
    }

    
    public function getStagesForJobRole(int $jobRoleId): Collection
    {
        $jobRole = JobRole::find($jobRoleId);
        
        if (!$jobRole) {
            throw new ModelNotFoundException('Job role not found');
        }

        // Check access permission
        if (!$this->canAccessJobRole($jobRole)) {
            throw new ModelNotFoundException('Job role not found');
        }

        return $jobRole->customStages;
    }

    
    public function createStage(int $jobRoleId, string $name, int $order): CustomStage
    {
        $jobRole = JobRole::find($jobRoleId);
        
        if (!$jobRole) {
            throw new ModelNotFoundException('Job role not found');
        }

        // Check access permission
        if (!$this->canAccessJobRole($jobRole)) {
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

        // Load job role relationship
        if (!isset($stage->jobRole)) {
            $stage->load('jobRole');
        }

        // Check access permission
        if (!$stage->jobRole || !$this->canAccessJobRole($stage->jobRole)) {
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

        // Load job role relationship
        if (!isset($stage->jobRole)) {
            $stage->load('jobRole');
        }

        // Check access permission
        if (!$stage->jobRole || !$this->canAccessJobRole($stage->jobRole)) {
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
        $jobRole = JobRole::find($jobRoleId);
        
        if (!$jobRole) {
            throw new ModelNotFoundException('Job role not found');
        }

        // Check access permission
        if (!$this->canAccessJobRole($jobRole)) {
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