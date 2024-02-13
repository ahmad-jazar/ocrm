<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class NotContains extends Contains
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        return !parent::compare($fieldValue);
    }
}
