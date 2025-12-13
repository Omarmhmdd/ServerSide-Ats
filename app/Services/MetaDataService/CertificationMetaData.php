<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;
use App\Models\Certification;

class CertificationMetaData implements MetaDataDomain{
    public function fetch($candidate_id){
        return Certification::where('candidate_id' , $candidate_id)
                       ->get();
    }
}
