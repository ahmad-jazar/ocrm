<?php

namespace Espo\Modules\Advanced\Tools\Report\GridType;

class ColumnData
{
    public ?string $function;
    public string $field;
    public ?string $entityType;
    public ?string $link;
    public ?string $fieldType;

    public function __construct(
        ?string $function,
        string $field,
        ?string $entityType,
        ?string $link,
        ?string $fieldType
    ) {
        $this->function = $function;
        $this->field = $field;
        $this->entityType = $entityType;
        $this->link = $link;
        $this->fieldType = $fieldType;
    }
}
