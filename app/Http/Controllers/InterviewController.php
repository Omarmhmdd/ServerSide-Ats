<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterviewRequest;
use App\Http\Requests\UpdateInterviewRequest;
use App\Services\InterviewService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Log;

class InterviewController extends Controller
{
    protected InterviewService $interviewService;

    public function __construct(InterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    public function createScreening($candidate_id){
        try{
            $user_email = InterviewService::createScreening($candidate_id);
            return $this->successResponse(["email" => $user_email]);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to create screening");
        }
    }

    public function markAsComplete($interview_id){
        try{    
            InterviewService::MarkAsComplete($interview_id);
            return $this->successResponse("Completed");
        }catch(Exception $ex){
            return $this->errorResponse("Failed to create scorecard");
        }
    }

    public function createScoreCard(Request $req){
        try{
           InterviewService::createScoreCard($req);
           return $this->successResponse("Created Score card");
        }catch(Exception $ex){
            return $this->errorResponse("Failed to create scorecard" . $ex->getMessage());
        }
    }

    public function index(): JsonResponse{
        try {
            $interviews = $this->interviewService->getAllInterviews();  // ← Instance call
            return $this->successResponse(['interviews' => $interviews]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch interviews', 500, ['error' => $e->getMessage()]);
        }
    }

    public function store(StoreInterviewRequest $request): JsonResponse{
        try {
            $interview = $this->interviewService->createInterview($request->validated());  // ← Instance call
            return $this->successResponse(
                ['interview' => $interview],
                'Interview created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create interview', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show(int $id): JsonResponse{
        try {
            $interview = InterviewService::getInterviewById($id);  // ← Keep static (doesn't use $this->user)
            return $this->successResponse(['interview' => $interview]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch interview', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateInterviewRequest $request, int $id): JsonResponse
    {
        try {
            $interview = InterviewService::updateInterview($id, $request->validated());  // ← Keep static
            return $this->successResponse(
                ['interview' => $interview],
                'Interview updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update interview', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            InterviewService::deleteInterview($id);  // ← Keep static
            return $this->successResponse([], 'Interview deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete interview', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getByCandidate(int $candidateId): JsonResponse{
        try {
            $interviews = $this->interviewService->getInterviewsByCandidate($candidateId);  // ← Instance call
            return $this->successResponse(['interviews' => $interviews]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch interviews', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getByInterviewer(int $interviewerId): JsonResponse{
        try {
            $interviews = $this->interviewService->getInterviewsByInterviewer($interviewerId);  // ← Instance call
            return $this->successResponse(['interviews' => $interviews]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch interviews', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse{
        try {
            $status = $request->input('status');
            if (!$status) {
                return $this->errorResponse('Status is required', 400);
            }

            $interview = InterviewService::updateInterviewStatus($id, $status);  // ← Keep static
            return $this->successResponse(
                ['interview' => $interview],
                'Interview status updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Interview not found', 404);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update interview status', 500, ['error' => $e->getMessage()]);
        }
    }
}