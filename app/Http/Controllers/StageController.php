<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStageRequest;
use App\Http\Requests\UpdateStageRequest;
use App\Services\StageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    protected StageService $stageService;

    public function __construct(StageService $stageService)
    {
        $this->stageService = $stageService;
    }

    /**
     * Display a listing of stages.
     */
    public function index(): JsonResponse
    {
        try {
            $stages = $this->stageService->getAllStages();
            return $this->successResponse(['stages' => $stages]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch stages', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created stage.
     */
    public function store(StoreStageRequest $request): JsonResponse
    {
        try {
            $stage = $this->stageService->createStage($request->validated());
            return $this->successResponse(
                ['stage' => $stage],
                'Stage created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified stage.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $stage = $this->stageService->getStageById($id);
            return $this->successResponse(['stage' => $stage]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Stage not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified stage.
     */
    public function update(UpdateStageRequest $request, int $id): JsonResponse
    {
        try {
            $stage = $this->stageService->updateStage($id, $request->validated());
            return $this->successResponse(
                ['stage' => $stage],
                'Stage updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Stage not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified stage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->stageService->deleteStage($id);
            return $this->successResponse([], 'Stage deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Stage not found', 404);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
 * Get stages for a specific job role
 */
/*public function getStagesForJobRole(int $jobRoleId): JsonResponse
{
    try {
        $stages = $this->stageService->getStagesForJobRole($jobRoleId);
        return $this->successResponse(['stages' => $stages]);
    } catch (ModelNotFoundException $e) {
        return $this->errorResponse('Job role not found', 404);
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to fetch stages', 500, ['error' => $e->getMessage()]);
    }
}*/

/**
 * Assign stages to a job role with custom ordering
 */
/*public function assignStagesToJobRole(Request $request, int $jobRoleId): JsonResponse
{
    try {
        $request->validate([
            'stage_ids' => 'required|array',
            'stage_ids.*' => 'required|exists:stages,id',
        ]);

        $this->stageService->assignStagesToJobRole($jobRoleId, $request->input('stage_ids'));
        
        return $this->successResponse(
            [],
            'Stages assigned to job role successfully'
        );
    } catch (ModelNotFoundException $e) {
        return $this->errorResponse('Job role not found', 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to assign stages', 500, ['error' => $e->getMessage()]);
    }
}
*/
/**
 * Update stage order for a job role
 */
/*public function updateStageOrderForJobRole(Request $request, int $jobRoleId): JsonResponse
{
    try {
        $request->validate([
            'stage_orders' => 'required|array',
            'stage_orders.*' => 'required|integer|min:0',
        ]);

        $this->stageService->updateStageOrderForJobRole($jobRoleId, $request->input('stage_orders'));
        
        return $this->successResponse(
            [],
            'Stage order updated successfully'
        );
    } catch (ModelNotFoundException $e) {
        return $this->errorResponse('Job role not found', 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to update stage order', 500, ['error' => $e->getMessage()]);
    }
}*/
}

