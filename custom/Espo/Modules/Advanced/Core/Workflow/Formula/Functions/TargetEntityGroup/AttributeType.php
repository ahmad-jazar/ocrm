<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\TargetEntityGroup;

use Espo\Core\Exceptions\Error;

class AttributeType extends \Espo\Core\Formula\Functions\EntityGroup\AttributeType
{
    protected function getEntity()
    {
        $variables = $this->getVariables();

        if (!isset($variables->__targetEntity)) {
            throw new Error("No target entity.");
        }

        return $variables->__targetEntity;
    }
}