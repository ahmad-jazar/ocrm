<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class TrueType extends Base
{
    protected array $permittedValues = [
        true,
        'true',
        1,
        '1',
    ];

    /**
     * @param mixed $fieldValue
     */
    protected function compare($fieldValue): bool
    {
        return in_array($fieldValue, $this->permittedValues, true);
    }
}
