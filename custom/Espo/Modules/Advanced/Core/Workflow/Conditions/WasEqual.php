<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class WasEqual extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $entity = $this->getEntity();
        $fieldName = $this->getFieldName();

        $previousFieldValue = $entity->getFetched($fieldName);

        $subjectValue = $this->getSubjectValue();

        return ($subjectValue == $previousFieldValue);
    }

}
