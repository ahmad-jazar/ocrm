<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class Has extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        if (is_array($subjectValue)) {
            // @todo Remove this?
            if ($subjectValue === $fieldValue) {
                return true;
            }

            return false;
        }

        return in_array($subjectValue, $fieldValue ?? []);
    }
}
