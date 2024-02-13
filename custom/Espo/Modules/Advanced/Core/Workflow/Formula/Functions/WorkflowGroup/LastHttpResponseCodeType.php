<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\WorkflowGroup;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;

class LastHttpResponseCodeType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $vars = $this->getVariables() ?? (object) [];

        return $vars->_lastHttpResponseCode ?? null;
    }
}
