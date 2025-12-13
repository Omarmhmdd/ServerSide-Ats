<?php

namespace App\Http\Controllers;

use App\Candidate\Services\CandidateImportService;
use App\Http\Requests\CandidateImportForm;
use Auth;
use Exception;

class CandidateImportController extends Controller
{
    protected int $user_id;

    public function __construct(){
        $this->user_id = Auth::id();
    }


    public function import(CandidateImportForm $request){
        try{
            $form = [
                "file" => $request->file('file'),
                "recruiter_id" => $this->user_id,
                "job_role_id" => $request->validated()["job_role_id"]
            ];

            $errors = CandidateImportService::import($form);

            if(count($errors) > 0){
                return $this->successResponse([
                    'errors' => $errors
                ], "Import completed with some errors");
            }
            
            return $this->successResponse([] , "Import successfull");
        }catch(Exception $ex){
            return $this->errorResponse("Failed to import excel file");
        }
    }
}
