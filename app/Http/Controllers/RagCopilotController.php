<?php

namespace App\Http\Controllers;

use App\Http\Requests\CopilotAskRequest;
use App\Services\RAG\RagQueryService;
use Exception;

class RagCopilotController extends Controller
{
    public function ask(CopilotAskRequest $request, RagQueryService $rag){
        try{
            $candidate_id = $request->validated()["candidate_id"];
            $question = $request->validated()["question"];
            $results = $rag->answer($candidate_id, $question);
            return $this->successResponse($results);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get copilot answer" . $ex->getMessage());
        }
    }
}
