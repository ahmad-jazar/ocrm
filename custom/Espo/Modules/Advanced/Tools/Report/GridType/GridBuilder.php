<?php

namespace Espo\Modules\Advanced\Tools\Report\GridType;

use Espo\Modules\Advanced\Tools\Report\GridType\Data as GridData;
use stdClass;

class GridBuilder
{
    private const ROUND_PRECISION = 4;
    private const STUB_KEY = '__STUB__';

    private Util $util;
    private Helper $helper;

    public function __construct(
        Util $util,
        Helper $helper
    ) {
        $this->util = $util;
        $this->helper = $helper;
    }

    /**
     * @param string[] $groupList
     */
    public function build(
        Data $data,
        array $rows,
        array $groupList,
        array $columns,
        array &$sums,
        stdClass $cellValueMaps,
        array $groups = [],
        int $number = 0
    ): stdClass {

        $gridData = $this->buildInternal(
            $data,
            $rows,
            $groupList,
            $columns,
            $sums,
            $cellValueMaps,
            $groups,
            $number
        );

        foreach ($gridData as $k => $v) {
            $gridData[$k] = (object) $v;

            foreach ($v as $k1 => $v1) {
                if (is_array($v1)) {
                    $gridData[$k]->$k1 = (object) $v1;
                }
            }
        }

        return (object) $gridData;
    }

    /**
     * @param string[] $groupList
     */
    public function buildInternal(
        Data $data,
        array $rows,
        array $groupList,
        array $columns,
        array &$sums,
        stdClass $cellValueMaps,
        array $groups,
        int $number
    ): array {

        $entityType = $data->getEntityType();

        if (count($data->getGroupBy()) === 0) {
            $groupList = [self::STUB_KEY];
        }

        $k = count($groups);

        $gridData = [];

        if ($k <= count($groupList) - 1) {
            $groupColumn = $groupList[$k];

            $keys = [];

            foreach ($rows as $row) {
                foreach ($groups as $i => $g) {
                    $groupAlias = $this->util->sanitizeSelectAlias($groupList[$i]);

                    if ($row[$groupAlias] !== $g) {
                        continue 2;
                    }
                }

                $groupAlias = $this->util->sanitizeSelectAlias($groupColumn);

                $key = $row[$groupAlias];

                if (!in_array($key, $keys)) {
                    $keys[] = $key;
                }
            }

            foreach ($keys as $number => $key) {
                $gr = $groups;
                $gr[] = $key;

                $gridData[$key] = $this->buildInternal(
                    $data,
                    $rows,
                    $groupList,
                    $columns,
                    $sums,
                    $cellValueMaps,
                    $gr,
                    $number + 1
                );
            }

            return $gridData;
        }

        $s = &$sums;

        for ($i = 0; $i < count($groups) - 1; $i++) {
            $group = $groups[$i];

            if (!array_key_exists($group, $s)) {
                $s[$group] = [];
            }

            $s = &$s[$group];
        }

        foreach ($rows as $row) {
            foreach ($groups as $i => $g) {
                $groupAlias = $this->util->sanitizeSelectAlias($groupList[$i]);

                if ($row[$groupAlias] != $g) {
                    continue 2;
                }
            }

            foreach ($columns as $column) {
                $selectAlias = $this->util->sanitizeSelectAlias($column);

                if ($this->helper->isColumnNumeric($column, $entityType)) {
                    if (empty($s[$column])) {
                        $s[$column] = 0;

                        if (strpos($column, 'MIN:') === 0) {
                            $s[$column] = null;
                        }
                        else if (strpos($column, 'MAX:') === 0) {
                            $s[$column] = null;
                        }
                    }

                    $value = strpos($column, 'COUNT:') === 0 ?
                        intval($row[$selectAlias]) :
                        floatval($row[$selectAlias]);

                    if (strpos($column, 'MIN:') === 0) {
                        if (is_null($s[$column]) || $s[$column] >= $value) {
                            $s[$column] = $value;
                        }
                    }
                    else if (strpos($column, 'MAX:') === 0) {
                        if (is_null($s[$column]) || $s[$column] < $value) {
                            $s[$column] = $value;
                        }
                    }
                    else if (strpos($column, 'AVG:') === 0) {
                        $s[$column] = $s[$column] + ($value - $s[$column]) / floatval($number);
                    }
                    else {
                        $s[$column] = $s[$column] + $value;
                    }

                    if (is_float($s[$column])) {
                        $s[$column] = round($s[$column], self::ROUND_PRECISION);
                    }

                    $gridData[$column] = $value;

                    continue;
                }

                $columnData = $this->helper->getDataFromColumnName($entityType, $column);

                if (!property_exists($cellValueMaps, $column)) {
                    $cellValueMaps->$column = (object) [];
                }

                $fieldType = $columnData->fieldType;

                $value = null;

                if (array_key_exists($selectAlias, $row)) {
                    $value = $row[$selectAlias];
                }

                if ($fieldType === 'link') {
                    $selectAlias = $this->util->sanitizeSelectAlias($column . 'Id');

                    $value = $row[$selectAlias];
                }

                $gridData[$column] = $value;

                if (!is_null($value) && !property_exists($cellValueMaps->$column, $value)) {
                    $displayValue = $this->util->getCellDisplayValue($value, $columnData);

                    if (!is_null($displayValue)) {
                        $cellValueMaps->$column->$value = $displayValue;
                    }
                }
            }
        }

        return $gridData;
    }

    /**
     * @param string[] $groupList
     */
    public function buildNonSummary(
        array $columnList,
        array $summaryColumnList,
        GridData $data,
        array $rows,
        array $groupList,
        stdClass $cellValueMaps,
        stdClass $nonSummaryColumnGroupMap
    ): ?stdClass {

        if (count($data->getGroupBy()) !== 2) {
            return null;
        }

        if (count($columnList) <= count($summaryColumnList)) {
            return (object) [];
        }

        $nonSummaryData = (object) [];

        foreach ($data->getGroupBy() as $i => $groupColumn) {
            $nonSummaryData->$groupColumn = (object) [];

            $groupAlias = $this->util->sanitizeSelectAlias($groupList[$i]);

            foreach ($columnList as $column) {
                if (in_array($column, $summaryColumnList)) {
                    continue;
                }

                if (strpos($column, $groupColumn . '.') !== 0) {
                    continue;
                }

                $nonSummaryColumnGroupMap->$column = $groupColumn;

                $columnData = $this->helper->getDataFromColumnName($data->getEntityType(), $column);

                $columnKey = $column;

                if ($columnData->fieldType === 'link') {
                    $columnKey .= 'Id';
                }

                $columnAlias = $this->util->sanitizeSelectAlias($columnKey);

                foreach ($rows as $row) {
                    $groupValue = $row[$groupAlias];

                    if (!property_exists($nonSummaryData->$groupColumn, $groupValue)) {
                        $nonSummaryData->$groupColumn->$groupValue = (object) [];
                    }

                    $value = $row[$columnAlias] ?? null;

                    if (is_null($value)) {
                        continue;
                    }

                    $nonSummaryData->$groupColumn->$groupValue->$column = $value;

                    if (!property_exists($cellValueMaps, $column)) {
                        $cellValueMaps->$column = (object) [];
                    }

                    if (!property_exists($cellValueMaps->$column, $value)) {
                        $cellValueMaps->$column->$value = $this->util->getCellDisplayValue($value, $columnData);
                    }
                }
            }
        }

        return $nonSummaryData;
    }
}
