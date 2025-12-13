<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;
use App\Models\Project;

class ProjectMetaData implements MetaDataDomain{
    public function fetch($candidate_id){
        return Project::whith('skills')
                       ->where('candidate_id' , $candidate_id)
                       ->get();
    }
}
