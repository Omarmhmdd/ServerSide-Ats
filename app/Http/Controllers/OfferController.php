<?php

namespace App\Http\Controllers;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use App\Models\Pipeline;
use Exception;
use App\Http\Requests\OfferForm;
use App\Services\OfferService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller{

    public function createOffer(OfferForm $offer){
        try {
            OfferService::createOffer($offer->validated() , Auth::id());
            return $this->successResponse("Offer created");
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create offer', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getWorkflowData(int $offerId): JsonResponse
    {
        try {
            $offer = Offer::with([
                'candidate',
                'jobRole.recruiter',
                'createdBy'
            ])->findOrFail($offerId);

            // Find pipeline for this offer
            $pipeline =Pipeline::where('candidate_id', $offer->candidate_id)
                ->where('job_role_id', $offer->role_id)
                ->with('interview')
                ->first();

            // Prepare data for n8n
            $data = [
                'offer_id' => $offer->id,
                'pipeline_id' => $pipeline?->id,
                'candidate' => [
                    'id' => $offer->candidate->id,
                    'first_name' => $offer->candidate->first_name,
                    'last_name' => $offer->candidate->last_name,
                    'name' => trim(($offer->candidate->first_name ?? '') . ' ' . ($offer->candidate->last_name ?? '')),
                    'email' => $offer->candidate->email,
                    'phone' => $offer->candidate->phone,
                    'location' => $offer->candidate->location,
                ],
                'job_role' => [
                    'id' => $offer->jobRole->id,
                    'title' => $offer->jobRole->title,
                    'description' => $offer->jobRole->description,
                    'location' => $offer->jobRole->location,
                    'is_remote' => $offer->jobRole->is_remote,
                ],
                'offer' => [
                    'id' => $offer->id,
                    'base_salary' => $offer->base_salary,
                    'equity' => $offer->equity,
                    'bonus' => $offer->bonus,
                    'benefits' => $offer->benifits,
                    'start_date' => $offer->start_date ? Carbon::parse($offer->start_date)->format('Y-m-d') : null,
                    'contract_type' => $offer->contract_type,
                    'status' => $offer->status,
                    'expiry_date' => $offer->expiry_date ? Carbon::parse($offer->expiry_date)->format('Y-m-d') : null,
                    'sent_at' => $offer->sent_at ? Carbon::parse($offer->sent_at)->format('Y-m-d H:i:s') : null,
                ],
                'recruiter' => $offer->jobRole->recruiter ? [
                    'id' => $offer->jobRole->recruiter->id,
                    'name' => $offer->jobRole->recruiter->name,
                    'email' => $offer->jobRole->recruiter->email,
                ] : null,
                'created_by' => $offer->createdBy ? [
                    'id' => $offer->createdBy->id,
                    'name' => $offer->createdBy->name,
                    'email' => $offer->createdBy->email,
                ] : null,
                'interview' => $pipeline && $pipeline->interview ? [
                    'id' => $pipeline->interview->id,
                    'type' => $pipeline->interview->type,
                    'schedule' => $pipeline->interview->schedule,
                    'status' => $pipeline->interview->status,
                ] : null,
            ];

            return $this->successResponse($data);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Offer not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch offer workflow data', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


    public function getOfferByPipeline(int $pipelineId): JsonResponse
    {
        try {
            $pipeline = Pipeline::with(['candidate', 'jobRole'])->findOrFail($pipelineId);

            $offer = Offer::where('candidate_id', $pipeline->candidate_id)
                ->where('role_id', $pipeline->job_role_id)
                ->first();

            if (!$offer) {
                return $this->errorResponse('No offer found for this pipeline', 404);
            }

            return $this->successResponse([
                'pipeline_id' => $pipelineId,
                'offer_id' => $offer->id,
                'offer_status' => $offer->status
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pipeline not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch offer', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

