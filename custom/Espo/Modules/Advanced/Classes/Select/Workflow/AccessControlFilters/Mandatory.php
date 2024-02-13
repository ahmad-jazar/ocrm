<?php

namespace Espo\Modules\Advanced\Classes\Select\Workflow\AccessControlFilters;

use Espo\Core\Select\AccessControl\Filter;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class Mandatory implements Filter
{
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where(['isInternal' => false]);
    }
}
