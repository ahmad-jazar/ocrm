<?php

namespace Espo\Modules\Advanced\Tools\Report\ListType;

class ExportParams
{
    /** @var ?string[] */
    private ?array $attributeList;
    /** @var ?string[] */
    private ?array $fieldList;
    private ?string $format;
    /** @var ?string[] */
    private ?array $ids;
    /** @var ?array<string, mixed> */
    private ?array $params;

    /**
     * @param ?string[] $attributeList
     * @param ?string[] $fieldList
     * @param ?string[] $ids
     * @params ?array<string, mixed> $params
     */
    public function __construct(
        ?array $attributeList,
        ?array $fieldList,
        ?string $format,
        ?array $ids,
        ?array $params
    ) {
        $this->attributeList = $attributeList;
        $this->fieldList = $fieldList;
        $this->format = $format;
        $this->ids = $ids;
        $this->params = $params;
    }

    /**
     * @return ?string[]
     */
    public function getAttributeList(): ?array
    {
        return $this->attributeList;
    }

    /**
     * @return ?string[]
     */
    public function getFieldList(): ?array
    {
        return $this->fieldList;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return ?string[]
     */
    public function getIds(): ?array
    {
        return $this->ids;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function getParams(): ?array
    {
        return $this->params;
    }
}