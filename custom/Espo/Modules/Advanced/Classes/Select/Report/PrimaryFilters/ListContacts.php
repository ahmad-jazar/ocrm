<?php

namespace Espo\Modules\Advanced\Classes\Select\Report\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\Modules\Advanced\Entities\Report;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class ListContacts implements Filter
{
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'type' => Report::TYPE_LIST,
            'entityType' => Contact::ENTITY_TYPE,
        ]);
    }
}
