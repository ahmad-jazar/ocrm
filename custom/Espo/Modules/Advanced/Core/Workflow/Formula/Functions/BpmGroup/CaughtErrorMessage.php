<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\BpmGroup;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;

class CaughtErrorMessage extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $variables = $this->getVariables();

        return $variables->__caughtErrorMessage ?? null;
    }
}
