<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Modules\Advanced\Entities\BpmnFlowchart;
use Espo\ORM\Entity;
use Espo\Modules\Advanced\Core\Bpmn\BpmnManager;
use stdClass;

class StartBpmnProcess extends Base
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        if (!empty($actionData->target)) {
            $target = $actionData->target;
            $targetEntity = $this->getTargetEntityFromTargetItem($entity, $target);

            if (!$targetEntity) {
                $GLOBALS['log']->notice('Workflow StartBpmnProcess: Empty target.');

                return false;
            }
        }
        else {
            $targetEntity = $this->getEntityManager()->getEntityById($entity->getEntityType(), $entity->getId());
        }

        if (empty($actionData->flowchartId) || empty($actionData->elementId)) {
            throw new Error('StartBpmnProcess: Empty action data.');
        }

        $bpmnManager = new BpmnManager($this->getContainer());

        /** @var ?BpmnFlowchart $flowchart */
        $flowchart = $this->getEntityManager()->getEntityById(BpmnFlowchart::ENTITY_TYPE, $actionData->flowchartId);

        if (!$flowchart) {
            throw new Error('StartBpmnProcess: Could not find flowchart ' . $actionData->flowchartId . '.');
        }

        if ($flowchart->get('targetType') !== $targetEntity->getEntityType()) {
            throw new Error("Workflow StartBpmnProcess: Target entity type doesn't match flowchart target type.");
        }

        $bpmnManager->startProcess(
            $targetEntity,
            $flowchart,
            $actionData->elementId,
            null,
            $this->getWorkflowId()
        );

        return true;
    }
}
