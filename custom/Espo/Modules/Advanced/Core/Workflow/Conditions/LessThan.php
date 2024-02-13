<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class LessThan extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        return ($fieldValue < $subjectValue);
    }
}
