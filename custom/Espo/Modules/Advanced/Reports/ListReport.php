<?php

namespace Espo\Modules\Advanced\Reports;

use Espo\Core\Select\SearchParams;
use Espo\Entities\User;
use Espo\Modules\Advanced\Tools\Report\ListType\Result;

interface ListReport
{
    public function run(?SearchParams $searchParams, ?User $user): Result;
}
