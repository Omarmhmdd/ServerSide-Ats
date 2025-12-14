<?php

namespace App\Http\Controllers;

use App\Http\Requests\CopilotAskRequest;
use App\RAG\Services\RagQueryService;
use Exception;
use Illuminate\Http\Request;

class RagCopilotController extends Controller
{
    public function ask(CopilotAskRequest $request, RagQueryService $rag){
        try{
            $results = $rag->answer(candidateId : $request->candidate_id , question: $request->question);
            $this->successResponse($results);
        }catch(Exception $ex){
            $this->errorResponse("Failed to get copilot answer" . $ex->getMessage());
        }
    }
}
