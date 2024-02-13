<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class Contains extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        return mb_strpos($fieldValue, $subjectValue) !== false;
    }
}
