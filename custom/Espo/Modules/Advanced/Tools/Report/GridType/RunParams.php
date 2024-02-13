<?php

namespace Espo\Modules\Advanced\Tools\Report\GridType;

class RunParams
{
    private bool $skipRuntimeFiltersCheck;

    public function __construct(
        bool $skipRuntimeFiltersCheck = false
    ) {
        $this->skipRuntimeFiltersCheck = $skipRuntimeFiltersCheck;
    }

    public function skipRuntimeFiltersCheck(): bool
    {
        return $this->skipRuntimeFiltersCheck;
    }

    public function withSkipRuntimeFiltersCheck(bool $value = true): self
    {
        $obj = clone $this;
        $obj->skipRuntimeFiltersCheck = $value;

        return $obj;
    }

    public static function create(): self
    {
        return new self();
    }
}
