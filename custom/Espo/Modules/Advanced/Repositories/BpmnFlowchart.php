<?php

namespace Espo\Modules\Advanced\Repositories;

use Espo\ORM\Entity;

use Espo\Modules\Advanced\Core\Bpmn\Utils\Helper;

class BpmnFlowchart extends \Espo\Core\ORM\Repositories\RDB
{
    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        $handleFlowchartData = false;

        if ($entity->isNew() || json_encode($entity->get('data')) !== json_encode($entity->getFetched('data'))) {
            $handleFlowchartData = true;
        }

        if ($handleFlowchartData) {
            $data = $entity->get('data') ?? (object) [];

            $eData = Helper::getElementsDataFromFlowchartData($data);

            $elementsDataHash = $eData['elementsDataHash'];
            $eventStartIdList = $eData['eventStartIdList'];
            $eventStartAllIdList = $eData['eventStartAllIdList'];

            $entity->set('elementsDataHash', $elementsDataHash);
            $entity->set('hasNoneStartEvent', count($eventStartIdList) > 0);
            $entity->set('eventStartIdList', $eventStartIdList);
            $entity->set('eventStartAllIdList', $eventStartAllIdList);
        }
    }

    protected function afterSave(Entity $entity, array $options = [])
    {
        parent::afterSave($entity, $options);

        $workflowList = $this->getEntityManager()
            ->getRepository('Workflow')
            ->where([
                'flowchartId' => $entity->get('id'),
                'isInternal' => true,
            ])
            ->find();

        $workflowsToRecreate = false;

        if (
            !$entity->isNew() &&
            json_encode($entity->get('data')) !== json_encode($entity->getFetched('data'))
        ) {
            $workflowsToRecreate = true;
        }

        if ($entity->isNew() || $workflowsToRecreate) {
            $this->removeWorkflows($entity);

            $data = $entity->get('data');

            if (isset($data->list) && is_array($data->list)) {
                foreach ($data->list as $item) {
                    if (!is_object($item)) {
                        continue;
                    }

                    if (
                        $item->type === 'eventStartConditional' &&
                        in_array($item->triggerType, ['afterRecordCreated', 'afterRecordSaved', 'afterRecordUpdated'])
                    ) {
                        $workflow = $this->getEntityManager()->getEntity('Workflow');

                        $conditionsAll = [];

                        if (isset($item->conditionsAll)) {
                            $conditionsAll = $item->conditionsAll;
                        }

                        $conditionsAny = [];

                        if (isset($item->conditionsAny)) {
                            $conditionsAny = $item->conditionsAny;
                        }

                        $conditionsFormula = null;

                        if (isset($item->conditionsFormula)) {
                            $conditionsFormula = $item->conditionsFormula;
                        }

                        $workflow->set([
                            'type' => $item->triggerType,
                            'entityType' => $entity->get('targetType'),
                            'isInternal' => true,
                            'flowchartId' => $entity->get('id'),
                            'isActive' => $entity->get('isActive'),
                            'conditionsAll' => $conditionsAll,
                            'conditionsAny' => $conditionsAny,
                            'conditionsFormula' => $conditionsFormula,
                            'actions' => [
                                (object) [
                                    'type' => 'startBpmnProcess',
                                    'flowchartId' => $entity->get('id'),
                                    'elementId' => $item->id,
                                    'cid' => 0,
                                ]
                            ]
                        ]);

                        $this->getEntityManager()->saveEntity($workflow);
                    }

                    if (
                        $item->type === 'eventStartSignal' &&
                        !empty($item->signal)
                    ) {
                        $workflow = $this->getEntityManager()->getEntity('Workflow');

                        $workflow->set([
                            'type' => 'signal',
                            'signalName' => $item->signal,
                            'entityType' => $entity->get('targetType'),
                            'isInternal' => true,
                            'flowchartId' => $entity->get('id'),
                            'isActive' => $entity->get('isActive'),
                            'actions' => [
                                (object) [
                                    'type' => 'startBpmnProcess',
                                    'flowchartId' => $entity->get('id'),
                                    'elementId' => $item->id,
                                    'cid' => 0,
                                ]
                            ]
                        ]);

                        $this->getEntityManager()->saveEntity($workflow);
                    }

                    if (
                        $item->type === 'eventStartTimer' &&
                        !empty($item->targetReportId) &&
                        !empty($item->scheduling)
                    ) {
                        $workflow = $this->getEntityManager()->getEntity('Workflow');

                        $workflow->set([
                            'type' => 'scheduled',
                            'entityType' => $entity->get('targetType'),
                            'isInternal' => true,
                            'flowchartId' => $entity->get('id'),
                            'isActive' => $entity->get('isActive'),
                            'scheduling' => $item->scheduling,
                            'schedulingApplyTimezone' => $item->schedulingApplyTimezone ?? false,
                            'targetReportId' => $item->targetReportId,
                            'targetReportName' => $item->targetReportId,
                            'actions' => [
                                (object) [
                                    'type' => 'startBpmnProcess',
                                    'flowchartId' => $entity->get('id'),
                                    'elementId' => $item->id,
                                    'cid' => 0,
                                ]
                            ]
                        ]);

                        $this->getEntityManager()->saveEntity($workflow);
                    }
                }
            }
        }

        if ($entity->isAttributeChanged('isActive') && !$entity->isNew() && !$workflowsToRecreate) {
            foreach ($workflowList as $workflow) {
                if ($workflow->get('isActive') !== $entity->get('isActive')) {
                    $workflow->set('isActive', $entity->get('isActive'));

                    $this->getEntityManager()->saveEntity($workflow);
                }
            }
        }
    }

    protected function removeWorkflows(Entity $entity)
    {
        $workflowList = $this->getEntityManager()
            ->getRepository('Workflow')
            ->where([
                'flowchartId' => $entity->get('id'),
                'isInternal' => true,
            ])
            ->find();

        foreach ($workflowList as $workflow) {
            $this->getEntityManager()->removeEntity($workflow);
            $this->getEntityManager()->getRepository('Workflow')->deleteFromDb($workflow->get('id'));
        }
    }

    protected function afterRemove(Entity $entity, array $options = [])
    {
        parent::afterRemove($entity, $options);

        $this->removeWorkflows($entity);
    }
}
