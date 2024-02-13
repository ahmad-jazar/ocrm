<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class NotChanged extends Changed
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        return !parent::compare($fieldValue);
    }
}
