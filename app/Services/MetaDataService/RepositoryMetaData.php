<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;
use App\Models\Repository;

class RepositoryMetaData implements MetaDataDomain{
    public function fetch($candidate_id){
        return Repository::with('technologies')
                        ->where('candidate_id' , $candidate_id)
                        ->get();
    }
}
