<?php

namespace Espo\Modules\Advanced\Classes\Select\Report\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\Entities\User;
use Espo\Modules\Advanced\Entities\Report;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class ListUsers implements Filter
{
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'type' => Report::TYPE_LIST,
            'entityType' => User::ENTITY_TYPE,
        ]);
    }
}
