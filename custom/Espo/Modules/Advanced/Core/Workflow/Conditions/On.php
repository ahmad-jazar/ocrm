<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use DateTime;
use Espo\Core\Utils\DateTime as DateTimeUtil;

class On extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        $fieldDate = new DateTime($fieldValue);
        $subjectDate = new DateTime($subjectValue);

        return (
            $fieldDate->format(DateTimeUtil::SYSTEM_DATE_FORMAT) ===
            $subjectDate->format(DateTimeUtil::SYSTEM_DATE_FORMAT)
        );
    }
}
