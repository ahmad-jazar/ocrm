<?php

namespace Espo\Modules\Advanced\Tools\Report\ListType;

class SubReportParams
{
    private int $groupIndex;
    /** @var ?scalar */
    private $groupValue;
    private bool $hasGroupValue2;
    /** @var ?scalar */
    private $groupValue2;

    public function __construct(
        int $groupIndex,
        $groupValue,
        bool $hasGroupValue2 = false,
        $groupValue2 = null
    ) {
        $this->groupIndex = $groupIndex;
        $this->groupValue = $groupValue;
        $this->groupValue2 = $groupValue2;
        $this->hasGroupValue2 = $hasGroupValue2;
    }

    public function getGroupIndex(): int
    {
        return $this->groupIndex;
    }

    /**
     * @return ?scalar
     */
    public function getGroupValue()
    {
        return $this->groupValue;
    }

    public function hasGroupValue2(): bool
    {
        return $this->hasGroupValue2;
    }

    /**
     * @return ?scalar
     */
    public function getGroupValue2()
    {
        return $this->groupValue2;
    }
}
