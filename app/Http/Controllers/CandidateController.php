<?php

namespace App\Http\Controllers;

use App\Services\Candidate\CandidateService;
use App\Http\Requests\metaDataRequest;
use Auth;
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

    public function getMetaData(int $candidate_id){
        try{
            $meta_data = CandidateService::getMetaData($candidate_id);
            return $this->successResponse($meta_data);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get candidates data", 500 , ["info" => $ex->getMessage()]);
        }
    }

    public function getCandidatesAllJobRoles($recruiter_id){
        try{
            $candidatesByRole = CandidateService::getCandidateByRole($recruiter_id);
            return $this->successResponse($candidatesByRole);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to save meta data for candidate" , 500 , ["1" => $ex->getMessage()]);
        }
    }

    public function getCandidateProgress($candidate_id){
        try{
            $candidateProgress = CandidateService::getCandidateProgress($candidate_id);
            return $this->successResponse($candidateProgress);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get candidate's progress" , 500 , ["1" => $ex->getMessage()]);
        }
    }

    public function getCandidateInterview($candidate_id){
        try{
            $candidateInterviews = CandidateService::getInterviews($candidate_id); 
            return $this->successResponse($candidateInterviews);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get candidate's interviews" , 500 , ["1" => $ex->getMessage()]);
        }
    }

    public function getStatistics(){
        try{

            $statistics = CandidateService::getStatistic(Auth::id());
            return $this->successResponse($statistics);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to get candidate's statistics" , 500 , ["1" => $ex->getMessage()]);
        }
    }



}
