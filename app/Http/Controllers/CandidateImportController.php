<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateImportForm;
use App\Services\CandidateImportService;
use Exception;

class CandidateImportController extends Controller
{
    public function import(CandidateImportForm $request){
        try{
            $errors = CandidateImportService::import($request->validated());

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
