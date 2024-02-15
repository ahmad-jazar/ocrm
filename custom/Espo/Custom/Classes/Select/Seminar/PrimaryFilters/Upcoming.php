<?php

namespace Espo\Custom\Classes\Select\Seminar\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Upcoming implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'seminarStartTime >=' => date('Y-m-d')
        ]);
    }
}
