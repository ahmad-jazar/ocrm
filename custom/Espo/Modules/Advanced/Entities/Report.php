<?php

namespace Espo\Modules\Advanced\Entities;

use Espo\Core\Field\LinkMultiple;

class Report extends \Espo\Core\ORM\Entity
{
    public const ENTITY_TYPE = 'Report';

    public const TYPE_LIST = 'List';
    public const TYPE_GRID = 'Grid';
    public const TYPE_JOINT_GRID = 'JointGrid';

    public function getName(): ?string
    {
        return $this->get('name');
    }

    public function getType(): string
    {
        return $this->get('type');
    }

    /**
     * @return string[]
     */
    public function getRuntimeFilters(): array
    {
        return $this->get('runtimeFilters') ?? [];
    }

    public function isInternal(): bool
    {
        return $this->get('isInternal');
    }

    public function getTargetEntityType(): ?string
    {
        return $this->get('entityType');
    }

    public function getPortals(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('portals');
    }

    protected function _hasJoinedReportsIds()
    {
        return $this->has('joinedReportDataList');
    }

    protected function _hasJoinedReportsNames()
    {
        return $this->has('joinedReportDataList');
    }

    protected function _hasJoinedReportsColumns()
    {
        return $this->has('joinedReportDataList');
    }

    protected function _getJoinedReportsIds()
    {
        $idList = [];
        $dataList = $this->get('joinedReportDataList');

        if (!is_array($dataList)) {
            return [];
        }

        foreach ($dataList as $item) {
            if (empty($item->id)) {
                continue;
            }

            $idList[] = $item->id;
        }

        return $idList;
    }

    protected function _getJoinedReportsNames()
    {
        $nameMap = (object) [];
        $dataList = $this->get('joinedReportDataList');

        if (!is_array($dataList)) {
            return $nameMap;
        }

        foreach ($dataList as $item) {
            if (empty($item->id)) {
                continue;
            }
            $report = $this->entityManager->getEntity('Report', $item->id);

            if (!$report) {
                continue;
            }

            $nameMap->{$item->id} = $report->get('name');
        }

        return $nameMap;
    }

    protected function _getJoinedReportsColumns()
    {
        $map = (object) [];
        $dataList = $this->get('joinedReportDataList');

        if (!is_array($dataList)) {
            return $map;
        }

        foreach ($dataList as $item) {
            if (empty($item->id)) {
                continue;
            }

            if (!isset($item->label)) {
                continue;
            }

            $map->{$item->id} = (object) [
                'label' => $item->label
            ];
        }

        return $map;
    }
}
