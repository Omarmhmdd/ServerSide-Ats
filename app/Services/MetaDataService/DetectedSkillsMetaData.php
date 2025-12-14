<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;
use App\Models\DetectedSkill;

class DetectedSkillsMetaData implements MetaDataDomain
{
    public function fetch($candidate_id){
        return DetectedSkill::where('candidate_id' , $candidate_id)
                       ->get();
    }
}
