<?php

namespace App\Services\MetaDataService;

use App\Services\MetaDataService\MetaDataDomain;
use App\Models\Education;

class EducationMetaData implements MetaDataDomain{
    public function fetch($candidate_id){
        return Education::where('candidate_id' , $candidate_id)
                       ->get();
    }
}
