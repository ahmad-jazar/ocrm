<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use Espo\ORM\Entity;
use stdClass;

class NotEquals extends Equals
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        return !(parent::compare($fieldValue));
    }

    protected function compareComplex(Entity $entity, stdClass $condition): bool
    {
        if (empty($condition->fieldValueMap)) {
            return false;
        }

        $fieldValueMap = $condition->fieldValueMap;

        $isEqual = true;

        foreach ($fieldValueMap as $field => $value) {
            if ($entity->get($field) !== $value) {
                $isEqual = false;

                break;
            }
        }

        return !$isEqual;
    }
}
