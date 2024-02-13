<?php

namespace Espo\Modules\Advanced\Tools\Report\ListType;

use Espo\ORM\Collection;
use stdClass;

class Result
{
    private Collection $collection;
    private int $total;
    /** @var ?string[] */
    private ?array $columns;
    private ?stdClass $columnsData;

    /**
     * @param ?string[] $columns
     */
    public function __construct(
        Collection $collection,
        int $total,
        ?array $columns = null,
        ?stdClass $columnsData = null
    ) {
        $this->collection = $collection;
        $this->total = $total;
        $this->columns = $columns;
        $this->columnsData = $columnsData;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return string[]
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    public function getColumnsData(): ?stdClass
    {
        return $this->columnsData;
    }
}