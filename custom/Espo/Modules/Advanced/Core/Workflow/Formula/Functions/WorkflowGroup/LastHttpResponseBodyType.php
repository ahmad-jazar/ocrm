<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\WorkflowGroup;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;

class LastHttpResponseBodyType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $vars = $this->getVariables() ?? (object) [];

        return $vars->_lastHttpResponseBody ?? null;
    }
}
