<?php

namespace Espo\Modules\Advanced\Reports;

use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Entities\User;
use Espo\Modules\Advanced\Tools\Report\GridType\Result;
use Espo\Modules\Advanced\Tools\Report\ListType\Result as ListResult;
use Espo\Modules\Advanced\Tools\Report\ListType\SubReportParams;

interface GridReport
{
    public function run(?WhereItem $where, ?User $user): Result;

    public function runSubReport(SearchParams $searchParams, SubReportParams $subReportParams, ?User $user): ListResult;
}
