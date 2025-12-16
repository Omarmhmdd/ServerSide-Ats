<?php

namespace App\Services;

use App\Models\Pipeline;
use App\Models\Offer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
class OfferWorkflowService
{
    
   public static function handleOfferStage(Pipeline $pipeline): void
{
    try {
        // Load relationships
        $pipeline->load(['candidate', 'jobRole']);
        
        if (!$pipeline->candidate || !$pipeline->jobRole) {
            Log::error('Offer workflow: Missing candidate or job role', [
                'pipeline_id' => $pipeline->id
            ]);
            return;
        }

        // Get existing offer (should already exist, validated before moving to offer stage)
        $offer = Offer::where('candidate_id', $pipeline->candidate_id)
            ->where('role_id', $pipeline->job_role_id)
            ->first();
        
        if (!$offer) {
            Log::error('Offer workflow: Offer not found for pipeline', [
                'pipeline_id' => $pipeline->id,
                'candidate_id' => $pipeline->candidate_id,
                'job_role_id' => $pipeline->job_role_id
            ]);
            return;
        }
        
        // Trigger n8n workflow
        self::triggerN8nWorkflow($offer->id);
        
        Log::info('Offer workflow triggered', [
            'pipeline_id' => $pipeline->id,
            'candidate_id' => $pipeline->candidate_id,
            'offer_id' => $offer->id
        ]);
        
    } catch (Exception $e) {
        Log::error('Offer workflow failed', [
            'pipeline_id' => $pipeline->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}


  /*  private static function createOrUpdateOffer(Pipeline $pipeline): Offer
    {
        $candidate = $pipeline->candidate;
        $jobRole = $pipeline->jobRole;
        
        $offer = Offer::firstOrNew([
            'candidate_id' => $candidate->id,
            'role_id' => $jobRole->id,
        ]);
        
        // Set default values if creating new
        if (!$offer->exists) {
            $offer->created_by = $jobRole->recruiter_id;
            $offer->status = 'draft';
            $offer->expiry_date = now()->addDays(7); // Default 7 days expiry
            
            // Set default values for required fields
            $offer->base_salary = 0; // Default to 0, can be updated later
            $offer->equity = 0; // Default to 0, can be updated later
            $offer->bonus = ''; // Empty string default
            $offer->benifits = ''; // Empty string default
            $offer->start_date = now()->addDays(30)->format('Y-m-d'); // Default 30 days from now
            $offer->contract_type = 'full_time'; // Default contract type
        }
        
        $offer->save();
        
        return $offer;
    }*/

    private static function triggerN8nWorkflow(int $offerId): void
    {
        $n8nWebhookUrl = env('N8N_WEBHOOK_URL', 'http://localhost:5678/webhook/offer-workflow');
        
        try {
            $response = Http::timeout(10)->post($n8nWebhookUrl, [
                'offer_id' => $offerId
            ]);
            
            if ($response->successful()) {
                Log::info('n8n webhook triggered successfully', [
                    'offer_id' => $offerId,
                    'response' => $response->body()
                ]);
            } else {
                Log::warning('n8n webhook returned non-success status', [
                    'offer_id' => $offerId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to trigger n8n webhook', [
                'offer_id' => $offerId,
                'webhook_url' => $n8nWebhookUrl,
                'error' => $e->getMessage()
            ]);
        }
    }
}

