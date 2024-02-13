<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class WasNotEqual extends WasEqual
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        return !(parent::compare($fieldValue));
    }
}
