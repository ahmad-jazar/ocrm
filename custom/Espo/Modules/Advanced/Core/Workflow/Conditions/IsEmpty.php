<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class IsEmpty extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        if (empty($fieldValue)) {
            return true;
        }

        return false;
    }
}
