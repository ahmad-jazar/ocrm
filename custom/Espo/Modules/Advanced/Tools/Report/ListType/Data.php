<?php

namespace Espo\Modules\Advanced\Tools\Report\ListType;

use Espo\Core\Select\Where\Item as WhereItem;

use stdClass;

class Data
{
    private string $entityType;
    /** @var string[] */
    private array $columns;
    /** @var ?string */
    private ?string $orderBy;
    private ?stdClass $columnsData;
    private ?WhereItem $filtersWhere;

    /**
     * @param string[] $columns
     */
    public function __construct(
        string $entityType,
        array $columns,
        ?string $orderBy,
        ?stdClass $columnsData,
        ?WhereItem $filtersWhere
    ) {
        $this->entityType = $entityType;
        $this->columns = $columns;
        $this->orderBy = $orderBy;
        $this->columnsData = $columnsData;
        $this->filtersWhere = $filtersWhere;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getColumnsData(): ?stdClass
    {
        return $this->columnsData;
    }

    /**
     * @param string[] $columns
     */
    public function withColumns(array $columns): self
    {
        $obj = clone $this;
        $obj->columns = $columns;

        return $obj;
    }

    public function getFiltersWhere(): ?WhereItem
    {
        return $this->filtersWhere;
    }
}
