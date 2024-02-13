<?php

namespace Espo\Modules\Advanced\Tools\Report\ListType;

class RunParams
{
    private bool $skipRuntimeFiltersCheck = false;
    private bool $returnSthCollection = false;
    private bool $isExport = false;
    private bool $fullSelect = false;
    /** @var ?string[] */
    private ?array $customColumnList = null;

    private function __construct() {}

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

    public function withReturnSthCollection(bool $value = true): self
    {
        $obj = clone $this;
        $obj->returnSthCollection = $value;

        return $obj;
    }

    public function withIsExport(bool $value = true): self
    {
        $obj = clone $this;
        $obj->isExport = $value;

        return $obj;
    }

    public function withFullSelect(bool $value = true): self
    {
        $obj = clone $this;
        $obj->fullSelect = $value;

        return $obj;
    }

    /**
     * @param ?string[] $value
     */
    public function withCustomColumnList(?array $value): self
    {
        $obj = clone $this;
        $obj->customColumnList = $value;

        return $obj;
    }

    public function returnSthCollection(): bool
    {
        return $this->returnSthCollection;
    }

    public function isExport(): bool
    {
        return $this->isExport;
    }

    public function isFullSelect(): bool
    {
        return $this->fullSelect;
    }

    /**
     * @return ?string[]
     */
    public function getCustomColumnList(): ?array
    {
        return $this->customColumnList;
    }
}
