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

        self::scheduleScreening($allMetaData);
    }

    private static function scheduleScreening($allMetaData){
        $listOfEmails = [];
        foreach($allMetaData as $candidate_id =>$meta){
            $candidate_email = Candidate::where('id' , $candidate_id)->select('email')->first();
            $listOfEmails = [
                $candidate_id => $candidate_email
            ];
        }
        InterviewService::scheduleInterviews($listOfEmails);
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
