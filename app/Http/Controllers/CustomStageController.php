<?php

namespace App\Http\Controllers;

use App\CustomStageService as AppCustomStageService;
use Illuminate\Http\Request;
use App\Services\CustomStageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;


class CustomStageController extends Controller
{
    protected CustomStageService $customStageService;

    public function __construct(CustomStageService $customStageService)
    {
        $this->customStageService = $customStageService;
    }

    /**
     * Get all custom stages for a job role
     */
    public function getStagesForJobRole(int $jobRoleId): JsonResponse
    {
        try {
            $stages = $this->customStageService->getStagesForJobRole($jobRoleId);
            return $this->successResponse(['stages' => $stages]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Job role not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch stages', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a custom stage for a job role
     */
    public function store(Request $request, int $jobRoleId): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'order' => 'required|integer|min:1',
            ]);

            $stage = $this->customStageService->createStage(
                $jobRoleId,
                $request->input('name'),
                $request->input('order')
            );

            return $this->successResponse(
                ['stage' => $stage],
                'Custom stage created successfully',
                201
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Job role not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create custom stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a custom stage
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'order' => 'sometimes|integer|min:1',
            ]);

            $stage = $this->customStageService->updateStage($id, $request->only(['name', 'order']));

            return $this->successResponse(
                ['stage' => $stage],
                'Custom stage updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Custom stage not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update custom stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a custom stage
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->customStageService->deleteStage($id);
            return $this->successResponse([], 'Custom stage deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Custom stage not found', 404);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete custom stage', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Reorder stages for a job role
     */
    public function reorder(Request $request, int $jobRoleId): JsonResponse
    {
        try {
            $request->validate([
                'stage_orders' => 'required|array',
                'stage_orders.*' => 'required|integer|min:1',
            ]);

            $this->customStageService->reorderStages($jobRoleId, $request->input('stage_orders'));

            return $this->successResponse(
                [],
                'Stages reordered successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Job role not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reorder stages', 500, ['error' => $e->getMessage()]);
        }
    }
}


