<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;
use App\Models\DetectedLanguage;

class DetectedLanguagesMetaData implements MetaDataDomain{
   public function fetch($candidate_id){
        return DetectedLanguage::where('candidate_id' , $candidate_id)
                       ->get();
    }
}
