<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class Changed extends Base
{
    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        $entity = $this->getEntity();
        $attribute = $this->getAttributeName();

        if (!isset($attribute)) {
            return false;
        }

        if (
            !$entity->isNew() &&
            !$entity->hasFetched($attribute) &&
            $entity->getAttributeParam($attribute, 'isLinkMultipleIdList')
        ) {
            return false;
        }

        if ($entity->isNew()) {
            $value = $entity->get($attribute);

            if (empty($value)) {
                return false;
            }
        }

        return $entity->isAttributeChanged($attribute);
    }
}
