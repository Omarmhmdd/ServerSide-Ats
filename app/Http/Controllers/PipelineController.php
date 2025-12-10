<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Services\PipelineService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
       protected PipelineService $pipelineService;

    public function __construct(PipelineService $pipelineService)
    {
        $this->pipelineService = $pipelineService;
    }

    /**
     * Display a listing of pipelines.
     */
    public function index(): JsonResponse
    {
        try {
            $pipelines = $this->pipelineService->getAllPipelines();
            return $this->successResponse(['pipelines' => $pipelines]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pipelines', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created pipeline entry.
     */
    public function store(StorePipelineRequest $request): JsonResponse
    {
        try {
            $pipeline = $this->pipelineService->createPipeline($request->validated());
            return $this->successResponse(
                ['pipeline' => $pipeline],
                'Pipeline entry created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create pipeline entry', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified pipeline entry.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $pipeline = $this->pipelineService->getPipelineById($id);
            return $this->successResponse(['pipeline' => $pipeline]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pipeline entry not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pipeline entry', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified pipeline entry.
     */
    public function update(UpdatePipelineRequest $request, int $id): JsonResponse
    {
        try {
            $pipeline = $this->pipelineService->updatePipeline($id, $request->validated());
            return $this->successResponse(
                ['pipeline' => $pipeline],
                'Pipeline entry updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pipeline entry not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update pipeline entry', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified pipeline entry.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->pipelineService->deletePipeline($id);
            return $this->successResponse([], 'Pipeline entry deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pipeline entry not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete pipeline entry', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pipelines by job role ID
     */
    public function getByJobRole(int $jobRoleId): JsonResponse
    {
        try {
            $pipelines = $this->pipelineService->getPipelinesByJobRole($jobRoleId);
            return $this->successResponse(['pipelines' => $pipelines]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pipelines', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pipelines by candidate ID
     */
    public function getByCandidate(int $candidateId): JsonResponse
    {
        try {
            $pipelines = $this->pipelineService->getPipelinesByCandidate($candidateId);
            return $this->successResponse(['pipelines' => $pipelines]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pipelines', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pipelines by stage ID
     */
    public function getByStage(int $stageId): JsonResponse
    {
        try {
            $pipelines = $this->pipelineService->getPipelinesByStage($stageId);
            return $this->successResponse(['pipelines' => $pipelines]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pipelines', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Move candidate to a different stage
     */
    public function moveToStage(Request $request, int $id): JsonResponse
    {
        try {
            $stageId = $request->input('stage_id');
            
            if (!$stageId) {
                return $this->errorResponse('stage_id is required', 400);
            }

            $pipeline = $this->pipelineService->moveCandidateToStage($id, $stageId);
            return $this->successResponse(
                ['pipeline' => $pipeline],
                'Candidate moved to new stage successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pipeline entry not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to move candidate', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pipeline statistics for a job role (candidate counts per stage)
     */
    public function getStatistics(int $jobRoleId): JsonResponse
    {
        try {
            $statistics = $this->pipelineService->getPipelineStatistics($jobRoleId);
            return $this->successResponse(['statistics' => $statistics]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get statistics', 500, ['error' => $e->getMessage()]);
        }
    }
}


