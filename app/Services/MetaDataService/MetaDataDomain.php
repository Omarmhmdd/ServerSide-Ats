<?php
namespace App\MetaDataService\Services;

interface MetaDataDomain{
    public function fetch(int $candidate_id);
}
