<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreInterviewRequest;
use App\Http\Requests\UpdateInterviewRequest;
use App\Services\InterviewService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;


class InterviewController extends Controller
{
     protected InterviewService $interviewService;

    public function __construct(InterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    /**
     * Display a listing of interviews.
     */
    public function index(): JsonResponse
    {
        try {
            $interviews = $this->interviewService->getAllInterviews();
            return $this->successResponse(['interviews' => $interviews]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch interviews', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created interview.
     */
    public function store(StoreInterviewRequest $request): JsonResponse
    {
        try {
            $interview = $this->interviewService->createInterview($request->validated());
            return $this->successResponse(
                ['interview' => $interview],
                'Interview created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create interview', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified interview.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $interview = $this->interviewService->getInterviewById($id);
            return $this->successResponse(['interview' => $interview]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch interview', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified interview.
     */
    public function update(UpdateInterviewRequest $request, int $id): JsonResponse
    {
        try {
            $interview = $this->interviewService->updateInterview($id, $request->validated());
            return $this->successResponse(
                ['interview' => $interview],
                'Interview updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update interview', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified interview.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->interviewService->deleteInterview($id);
            return $this->successResponse([], 'Interview deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete interview', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get interviews by candidate ID
     */
    public function getByCandidate(int $candidateId): JsonResponse
    {
        try {
            $interviews = $this->interviewService->getInterviewsByCandidate($candidateId);
            return $this->successResponse(['interviews' => $interviews]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch interviews', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get interviews by interviewer ID
     */
    public function getByInterviewer(int $interviewerId): JsonResponse
    {
        try {
            $interviews = $this->interviewService->getInterviewsByInterviewer($interviewerId);
            return $this->successResponse(['interviews' => $interviews]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch interviews', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update interview status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $status = $request->input('status');
            if (!$status) {
                return $this->errorResponse('Status is required', 400);
            }

            $interview = $this->interviewService->updateInterviewStatus($id, $status);
            return $this->successResponse(
                ['interview' => $interview],
                'Interview status updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update interview status', 500, ['error' => $e->getMessage()]);
        }
    }
}


