<?php

namespace App\Http\Controllers;

use App\Http\Requests\metaDataRequest;
use App\Services\CandidateService;
use Exception;

class CandidateController extends Controller
{
    public function saveMetaData(metaDataRequest $request){
        try{
            CandidateService::saveMetaData($request);
            return $this->successResponse(["message" => "Meta data saved"]);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to save meta data for candidate");
        }
    }


    public function getCandidateData(){// for n8n for meta data creation
        try{
            $candidateData = CandidateService::getCandidateData();
            $candidateData[] = [
                "linkedin_url" => "soijdfosjff",
                "github_url" => "siandflsfi",
                "portfolio" => "lsdfsjff",
                "attachments" => "n/A"
            ];
            return $this->successResponse($candidateData);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get candidates data" . $ex->getMessage());
        }
    }


}
