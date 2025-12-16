<?php

namespace App\Services\MetaDataService;

use App\Services\MetaDataService\MetaDataDomain;


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
