<?php

namespace App\Services;
use App\Models\Interview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class InterviewService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
      public function getAllInterviews(): Collection
    {
        return Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->latest()
            ->get();
    }

    /**
     * Get interview by ID
     */
    public function getInterviewById(int $id): Interview
    {
        $interview = Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        return $interview;
    }

    /**
     * Create a new interview
     */
    public function createInterview(array $data): Interview
    {
        $interview = Interview::create($data);
        $interview->load(['interviewer', 'jobRole', 'candidate']);
        
        return $interview;
    }

    /**
     * Update an interview
     */
    public function updateInterview(int $id, array $data): Interview
    {
        $interview = Interview::find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        $interview->update($data);
        $interview->load(['interviewer', 'jobRole', 'candidate']);

        return $interview;
    }

    /**
     * Delete an interview
     */
    public function deleteInterview(int $id): bool
    {
        $interview = Interview::find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        return $interview->delete();
    }

    /**
     * Get interviews by candidate ID
     */
    public function getInterviewsByCandidate(int $candidateId): Collection
    {
        return Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->where('candidate_id', $candidateId)
            ->latest()
            ->get();
    }

    /**
     * Get interviews by interviewer ID
     */
    public function getInterviewsByInterviewer(int $interviewerId): Collection
    {
        return Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->where('intreveiwer_id', $interviewerId)
            ->latest()
            ->get();
    }

    /**
     * Update interview status
     */
    public function updateInterviewStatus(int $id, string $status): Interview
    {
        $validStatuses = ['no show', 'completed', 'canceled', 'posptponed', 'pending'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid status provided');
        }

        $interview = Interview::find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        $interview->update(['status' => $status]);
        $interview->load(['interviewer', 'jobRole', 'candidate']);

        return $interview;
    }
}



