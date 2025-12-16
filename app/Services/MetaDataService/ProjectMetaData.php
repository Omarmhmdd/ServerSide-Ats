<?php

namespace App\Services\MetaDataService;

use App\Services\MetaDataService\MetaDataDomain;
use App\Models\Project;

class ProjectMetaData implements MetaDataDomain{
    public function fetch($candidate_id){
        return Project::with('skills')
                       ->where('candidate_id' , $candidate_id)
                       ->get();
    }
}
