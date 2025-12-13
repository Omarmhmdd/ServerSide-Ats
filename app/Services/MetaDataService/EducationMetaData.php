<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;
use App\Models\Education;

class EducationMetaData implements MetaDataDomain{
    public function fetch($candidate_id){
        return Education::where('candidate_id' , $candidate_id)
                       ->get();
    }
}
