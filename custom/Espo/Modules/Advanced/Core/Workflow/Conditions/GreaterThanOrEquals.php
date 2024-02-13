<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class GreaterThanOrEquals extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        return ($fieldValue >= $subjectValue);
    }
}
