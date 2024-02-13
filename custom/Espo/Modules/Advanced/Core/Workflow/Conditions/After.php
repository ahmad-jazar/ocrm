<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use DateTime;

class After extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        $fieldDate = new DateTime($fieldValue);
        $subjectDate = new DateTime($subjectValue);

        return ($fieldDate > $subjectDate);
    }
}
