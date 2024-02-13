<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\BpmGroup\ProcessEntityGroup;

use Espo\Core\Exceptions\Error;

/**
 * @todo Rewrite to inherit from BaseFunction.
 */
class AttributeType extends \Espo\Core\Formula\Functions\EntityGroup\AttributeType
{
    protected function getEntity()
    {
        $variables = $this->getVariables();

        if (!isset($variables->__processEntity)) {
            throw new Error("Formula processEntity\\attribute can't be used out of process");
        }

        return $variables->__processEntity;
    }
}
