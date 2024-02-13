<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class Before extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        $fieldDate = new \DateTime($fieldValue);
        $subjectDate = new \DateTime($subjectValue);

        return ($fieldDate < $subjectDate);
    }
}
