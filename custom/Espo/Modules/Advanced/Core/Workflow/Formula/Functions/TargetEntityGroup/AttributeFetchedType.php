<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\TargetEntityGroup;

use Espo\Core\Exceptions\Error;

/**
 * @todo Rewrite to extend from BaseFunction. The same for other functions.
 */
class AttributeFetchedType extends \Espo\Core\Formula\Functions\EntityGroup\AttributeFetchedType
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
