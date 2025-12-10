<?php

namespace App\Services;

use App\Services\BuildCandidateMetaDataService;
use App\Models\Candidate;
use Exception;

class CandidateService{
    public static function saveMetaData(array $allMetaData){

        if(!$allMetaData || count($allMetaData) === 0){
            throw new Exception("Failed to extract user data, please make sure to enter valid urls");
        }

        $tablesInsertion = BuildCandidateMetaDataService::buildInsertStrings($allMetaData);
        foreach($tablesInsertion as $table => $data){
            BuildCandidateMetaDataService::bulkInsert($table , $data);
        }
        return true;
    }

    public static function getCandidateData(){
        return Candidate::where('processed', 0)
        ->select([
            'id',
            'linkedin_url',
            'github_url',
            'portfolio',
            'attachments'
        ])
        ->get();
    }
}
