<?php

namespace Espo\Modules\Advanced\Classes\Select\BpmnUserTask\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class Actual implements Filter
{
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'isResolved' => false,
            'isCanceled' => false,
        ]);
    }
}
