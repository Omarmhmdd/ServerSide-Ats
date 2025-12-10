<?php

namespace App\Http\Controllers;

use App\Services\InterviewService;
use Exception;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function createScreening($candidate_id){
        try{
            $user_email = InterviewService::createScreening($candidate_id);
            return $this->successResponse(["email" => $user_email]);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to create screening");
        }
    }
}
