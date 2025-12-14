<?php

namespace App\Services\MetaDataService;

use App\MetaDataService\Services\MetaDataDomain;

class MetaDataService
{
    public function __construct(
        protected MetaDataDomain $domain
    ) {}

    public function get(int $candidateId)
    {
        return $this->domain->fetch($candidateId);
    }
}
