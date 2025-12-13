<?php

namespace App\Http\Controllers;

use App\Candidate\Services\CandidateService;
use App\Http\Requests\metaDataRequest;
use Exception;
use Log;

class CandidateController extends Controller
{
    public function saveMetaData(metaDataRequest $request){
        try{
            CandidateService::saveMetaData($request->validated());
            return $this->successResponse(["message" => "Meta data saved"]);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to save meta data for candidate" , 500 , ["1" => $ex->getMessage()]);
        }
    }

    public function getCandidateData(){// for n8n for meta data creation
        try{
            $candidateData = CandidateService::getCandidateData();
            return $this->successResponse($candidateData);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get candidates data" . $ex->getMessage());
        }
    }


    


}
