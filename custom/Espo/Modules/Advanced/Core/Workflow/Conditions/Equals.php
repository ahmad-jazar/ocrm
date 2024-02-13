<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\ORM\Entity;

class Equals extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $subjectValue = $this->getSubjectValue();

        return ($fieldValue == $subjectValue);
    }

    protected function compareComplex(Entity $entity, \stdClass $condition): bool
    {
        if (empty($condition->fieldValueMap)) {
            return false;
        }

        $fieldValueMap = $condition->fieldValueMap;

        foreach ($fieldValueMap as $field => $value) {
            $v = Utils::getFieldValue($entity, $field, false, $this->getEntityManager(), $this->createdEntitiesData);

            if ($v !== $value) {
                return false;
            }
        }

        return true;
    }
}
