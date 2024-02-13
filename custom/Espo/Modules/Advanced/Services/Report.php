<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Modules\Advanced\Entities\Report as ReportEntity;
use Espo\ORM\Entity;
use Espo\Services\Record;
use stdClass;

class Report extends Record
{
    protected $forceSelectAllAttributes = true;

    public function filterCreateInput(stdClass $data): void
    {
        parent::filterCreateInput($data);

        $this->filterForbiddenFields($data);
    }

    public function filterUpdateInput(stdClass $data): void
    {
        parent::filterUpdateInput($data);

        $this->filterForbiddenFields($data);
    }

    private function filterForbiddenFields(stdClass $data): void
    {
        if ($this->acl->getPermissionLevel('portalPermission') === Table::LEVEL_NO) {
            unset($data->portalsIds);
        }
    }

    public function prepareEntityForOutput(Entity $entity)
    {
        parent::prepareEntityForOutput($entity);

        if ($this->acl->getPermissionLevel('portalPermission') === Table::LEVEL_NO) {
            $entity->clear('portalsIds');
            $entity->clear('portalsNames');
        }
    }

    /**
     * @param ReportEntity $entity
     */
    public function loadAdditionalFields(Entity $entity)
    {
        parent::loadAdditionalFields($entity);

        if ($entity->getType() === ReportEntity::TYPE_GRID) {
            $chartDataList = $entity->get('chartDataList');

            if ($chartDataList && count($chartDataList)) {
                $y = $chartDataList[0]->columnList ?? null;
                $y2 = $chartDataList[0]->y2ColumnList ?? null;

                $entity->set('chartOneColumns', $y);
                $entity->set('chartOneY2Columns', $y2);
            }
        }
    }

    /**
     * @param ReportEntity $entity
     * @throws Forbidden
     * @throws Error
     */
    protected function beforeCreateEntity(Entity $entity, $data)
    {
        $this->processJointGridBeforeSave($entity);

        if (
            in_array(
                'applyAcl',
                $this->acl->getScopeForbiddenFieldList(ReportEntity::ENTITY_TYPE, Table::ACTION_EDIT)
            )
        ) {
            $entity->set('applyAcl', true);
        }

        if (!$this->acl->check($entity->getTargetEntityType(), Table::ACTION_READ)) {
            throw new Forbidden();
        }
    }

    /**
     * @param ReportEntity $entity
     * @throws Forbidden
     * @throws Error
     */
    protected function beforeUpdateEntity(Entity $entity, $data)
    {
        if ($entity->isAttributeChanged('type')) {
            $entity->set('type', $entity->getFetched('type'));
        }

        if (
            $entity->getType() !== ReportEntity::TYPE_JOINT_GRID &&
            $entity->isAttributeChanged('entityType')
        ) {
            $entity->set('entityType', $entity->getFetched('entityType'));
        }

        $this->processJointGridBeforeSave($entity);
    }

    /**
     * @param ReportEntity $entity
     * @throws Forbidden
     * @throws Error
     */
    protected function processJointGridBeforeSave(Entity $entity): void
    {
        if ($entity->getType() === ReportEntity::TYPE_JOINT_GRID) {
            $joinedReportDataList = $entity->get('joinedReportDataList');

            if (is_array($joinedReportDataList) && count($joinedReportDataList)) {
                foreach ($joinedReportDataList as $i => $item) {
                    if (empty($item->id)) {
                        throw new Error();
                    }

                    /** @var ?ReportEntity $report */
                    $report = $this->entityManager->getEntity(ReportEntity::ENTITY_TYPE, $item->id);

                    if (!$report) {
                        throw new Error('Report not found.');
                    }

                    if (!$this->acl->check($report->getTargetEntityType(), Table::ACTION_READ)) {
                        throw new Forbidden();
                    }

                    $groupBy = $report->get('groupBy');

                    if (
                        !is_array($groupBy) ||
                        count($groupBy) > 1 ||
                        $report->getType() !== ReportEntity::TYPE_GRID
                    ) {
                        throw new Error("Sub-report {$item->id} is not supported in joint report.");
                    }

                    if ($i == 0) {
                        $groupCount = count($groupBy);

                        $entityType = $report->getTargetEntityType();
                        $entity->set('entityType', $entityType);
                    }
                    else {
                        if ($groupCount !== count($groupBy)) {
                            throw new Error("Sub-reports must have the same Group By number.");
                        }
                    }
                }
            }
        }
    }
}
