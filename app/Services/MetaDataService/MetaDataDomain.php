<?php
namespace App\Services\MetaDataService;

interface MetaDataDomain{
    public function fetch(int $candidate_id);
}
