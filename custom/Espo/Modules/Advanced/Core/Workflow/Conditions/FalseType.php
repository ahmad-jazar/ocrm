<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class FalseType extends TrueType
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        return !(parent::compare($fieldValue));
    }
}
