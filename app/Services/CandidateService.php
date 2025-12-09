<?php

namespace App\Services;

use App\Models\Candidate;

class CandidateService
{
    public static function saveMetaData($metaDataForm){
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
