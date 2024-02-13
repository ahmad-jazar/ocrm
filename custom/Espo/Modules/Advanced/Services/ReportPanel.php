<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Modules\Advanced\Entities\Report as ReportEntity;
use Espo\Modules\Advanced\Entities\ReportPanel as ReportPanelEntity;
use Espo\Modules\Advanced\Tools\Report\GridType\Helper;
use Espo\Modules\Advanced\Tools\ReportPanel\Service as PanelService;
use Espo\ORM\Entity;
use Espo\Services\Record;

/**
 * @extends Record<ReportPanelEntity>
 */
class ReportPanel extends Record
{
    protected $forceSelectAllAttributes = true;

    protected function afterCreateEntity(Entity $entity, $data)
    {
        $this->rebuild($entity->get('entityType'));
    }

    protected function afterUpdateEntity(Entity $entity, $data)
    {
        $this->rebuild($entity->get('entityType'));
    }

    protected function afterDeleteEntity(Entity $entity)
    {
        $this->rebuild($entity->get('entityType'));
    }

    private function rebuild(?string $entityType = null): void
    {
        $this->injectableFactory
            ->create(PanelService::class)
            ->rebuild($entityType);
    }

    /**
     * @param ReportPanelEntity $entity
     */
    public function loadAdditionalFields(Entity $entity)
    {
        parent::loadAdditionalFields($entity);

        $helper = $this->injectableFactory->create(Helper::class);

        if (
            $entity->get('reportType') === ReportEntity::TYPE_GRID &&
            $entity->get('reportId')
        ) {
            /** @var ?ReportEntity $report */
            $report = $this->entityManager->getEntityById(ReportEntity::ENTITY_TYPE, $entity->get('reportId'));

            if ($report) {
                $columnList = $report->get('columns');

                $numericColumnList = [];

                foreach ($columnList as $column) {
                    if ($helper->isColumnNumeric($column, $report->getTargetEntityType())) {
                        $numericColumnList[] = $column;
                    }
                }

                if (
                    is_array($report->get('groupBy')) &&
                    (
                        count($report->get('groupBy')) === 1 ||
                        count($report->get('groupBy')) === 0
                    ) &&
                    count($numericColumnList) > 1
                ) {
                    array_unshift($numericColumnList, '');
                }

                $entity->set('columnList', $numericColumnList);
            }
        }

        $displayType = $entity->get('displayType');
        $reportType = $entity->get('reportType');
        $displayTotal = $entity->get('displayTotal');
        $displayOnlyTotal = $entity->get('displayOnlyTotal');

        if (!$displayType) {
            if (
                $reportType === ReportEntity::TYPE_GRID ||
                $reportType === ReportEntity::TYPE_JOINT_GRID
            ) {
                if ($displayOnlyTotal) {
                    $displayType = 'Total';
                }
                else if ($displayTotal) {
                    $displayType = 'Chart-Total';
                }
                else {
                    $displayType = 'Chart';
                }
            }
            else if ($reportType === ReportEntity::TYPE_LIST) {
                if ($displayOnlyTotal) {
                    $displayType = 'Total';
                }
                else {
                    $displayType = 'List';
                }
            }

            $entity->set('displayType', $displayType);
        }
    }
}
