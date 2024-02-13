<?php

namespace Espo\Modules\Advanced\Tools\Report\GridType;

use Espo\Core\AclManager;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Advanced\Tools\Report\GridType\Result as GridResult;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\QueryComposer\Util as QueryComposerUtil;

class Helper
{
    private array $numericFieldTypeList = [
        'currency',
        'currencyConverted',
        'int',
        'float',
        'enumInt',
        'enumFloat',
        'duration',
    ];

    private Metadata $metadata;
    private AclManager $aclManager;
    private EntityManager $entityManager;

    public function __construct(
        Metadata $metadata,
        AclManager $aclManager,
        EntityManager $entityManager
    ) {
        $this->metadata = $metadata;
        $this->aclManager = $aclManager;
        $this->entityManager = $entityManager;
    }

    public function getDataFromColumnName(string $entityType, string $column, ?GridResult $result = null): ColumnData
    {
        if ($result && $result->isJoint()) {
            $entityType = $result->getColumnEntityTypeMap()[$column];
            $column = $result->getColumnOriginalMap()[$column];
        }

        $field = $column;
        $link = null;
        $function = null;

        if (strpos($field, ':') !== false) {
            [$function, $field] = explode(':', $field);
        }

        $fieldEntityType = $entityType;

        if (strpos($field, '.') !== false) {
            [$link, $field] = explode('.', $field);

            $fieldEntityType = $this->metadata->get(['entityDefs', $entityType, 'links', $link, 'entity']);
        }

        $fieldType = $this->metadata->get(['entityDefs', $fieldEntityType, 'fields', $field, 'type']);

        return new ColumnData(
            $function,
            $field,
            $fieldEntityType,
            $link,
            $fieldType
        );
    }

    public function isColumnNumeric(string $item, string $entityType): bool
    {
        $columnData = $this->getDataFromColumnName($entityType, $item);

        if (in_array($columnData->function, ['COUNT', 'SUM', 'AVG'])) {
            return true;
        }

        if (in_array($columnData->fieldType, $this->numericFieldTypeList)) {
            return true;
        }

        return false;
    }

    public function isColumnSubList(string $item, ?string $groupBy = null): bool
    {
        if (strpos($item, ':') !== false) {
            return false;
        }

        if (strpos($item, '.') === false) {
            return true;
        }

        if (!$groupBy) {
            return true;
        }

        if (explode('.', $item)[0] === $groupBy) {
            return false;
        }

        return true;
    }

    public function isColumnSummary(string $item): bool
    {
        $function = null;

        if (strpos($item, ':') > 0) {
            [$function] = explode(':', $item);
        }

        if ($function && in_array($function, ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX'])) {
            return true;
        }

        return false;
    }

    public function isColumnDateFunction(string $column): bool
    {
        $list = [
            'MONTH:',
            'YEAR:',
            'DAY:',
            'MONTH:',
            'YEAR:',
            'DAY:',
            'QUARTER:',
            'QUARTER_',
            'WEEK_0:',
            'WEEK_1:',
            'YEAR_',
            'QUARTER_FISCAL:',
            'YEAR_FISCAL:',
        ];

        foreach ($list as $item) {
            if (strpos($column, $item) === 0) {
                return true;
            }
        }

        return false;
    }

    public function isColumnSubListAggregated(string $item): bool
    {
        if (strpos($item, ':') === false) {
            return false;
        }

        if (strpos($item, ',') !== false) {
            return false;
        }

        if (strpos($item, '.') !== false) {
            return false;
        }

        if (strpos($item, '(') !== false) {
            return false;
        }

        $function = explode(':', $item)[0];

        if ($function === 'COUNT') {
            return false;
        }

        if (in_array($function, ['SUM', 'MAX', 'MIN', 'AVG'])) {
            return true;
        }

        return false;
    }

    /**
     * @param string[] $itemList
     */
    public function checkColumnsAvailability(string $entityType, array $itemList): void
    {
        foreach ($itemList as $item) {
            $this->checkColumnAvailability($entityType, $item);
        }
    }

    /**
     * @throws Forbidden
     */
    private function checkColumnAvailability(string $entityType, string $item): void
    {
        if (strpos($item, ':') !== false) {
            $argumentList = QueryComposerUtil::getAllAttributesFromComplexExpression($item);

            foreach ($argumentList as $argument) {
                $this->checkColumnAvailability($entityType, $argument);
            }

            return;
        }

        if (strpos($item, ':') !== false) {
            [, $field] = explode(':', $item);
        } else {
            $field = $item;
        }

        if (strpos($field, '.') !== false) {
            [$link, $field] = explode('.', $field);

            $entityType = $this->metadata->get(['entityDefs', $entityType, 'links', $link,  'entity']);

            if (!$entityType) {
                return;
            }
        }

        if (
            in_array($field, $this->aclManager->getScopeRestrictedFieldList($entityType, 'onlyAdmin')) ||
            in_array($field, $this->aclManager->getScopeRestrictedFieldList($entityType, 'internal')) ||
            in_array($field, $this->aclManager->getScopeRestrictedFieldList($entityType, 'forbidden'))
        ) {
            throw new Forbidden;
        }
    }

    /**
     * @todo Check whether it's working.
     * @return string[]
     */
    public function obtainLinkColumnList(Data $data): array
    {
        $list = [];

        foreach ($data->getGroupBy() as $item) {
            $columnData = $this->getDataFromColumnName($data->getEntityType(), $item);

            if ($columnData->function) {
                continue;
            }

            if (!$columnData->link) {
                if (in_array($columnData->fieldType, ['link', 'file', 'image'])) {
                    $list[] = $item;
                }

                continue;
            }

            $entityDefs = $this->entityManager
                ->getDefs()
                ->getEntity($data->getEntityType());

            if (!$entityDefs->hasRelation($columnData->link)) {
                continue;
            }

            $relationType = $entityDefs
                ->getRelation($columnData->link)
                ->getType();

            if (
                (
                    $relationType === Entity::BELONGS_TO ||
                    $relationType === Entity::HAS_ONE
                ) &&
                in_array($columnData->fieldType, ['link', 'file', 'image'])
            ) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * @param string[] $columns
     * @return string[]
     */
    public function obtainLinkColumnListFromColumns(Data $data, array $columns): array
    {
        $typeList = [
            'link',
            'file',
            'image',
            'linkOne',
            'linkParent',
        ];

        $list = [];

        foreach ($columns as $item) {
            $columnData = $this->getDataFromColumnName($data->getEntityType(), $item);

            if ($columnData->function || $columnData->link) {
                continue;
            }

            if (in_array($columnData->fieldType, $typeList)) {
                $list[] = $item;
            }
        }

        return $list;
    }
}