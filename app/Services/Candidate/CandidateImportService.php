<?php

namespace App\Services\Candidate;

use App\Imports\CandidatesImport;
use Maatwebsite\Excel\Facades\Excel;

class CandidateImportService{
    public static function import($candidateForm){
        // import excel file
        $import = new CandidatesImport($candidateForm["recruiter_id"] , $candidateForm["job_role_id"]);
        Excel::import($import, $candidateForm["file"]);// creates instance
        return $import->errors();
    }
}