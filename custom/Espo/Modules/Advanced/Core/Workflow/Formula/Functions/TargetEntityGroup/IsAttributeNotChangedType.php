<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\TargetEntityGroup;

use Espo\Core\Exceptions\Error;

class IsAttributeNotChangedType extends \Espo\Core\Formula\Functions\EntityGroup\IsAttributeNotChangedType
{
    protected function getEntity()
    {
        $variables = $this->getVariables();

        if (!isset($variables->__targetEntity)) {
            throw new Error();
        }

        return $variables->__targetEntity;
    }
}
