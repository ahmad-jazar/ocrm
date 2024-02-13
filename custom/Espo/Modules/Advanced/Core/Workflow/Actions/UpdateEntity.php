<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use stdClass;

class UpdateEntity extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        $entityManager = $this->getEntityManager();

        $reloadedEntity = $entityManager->getEntity($entity->getEntityType(), $entity->getId());

        $data = $this->getDataToFill($reloadedEntity, $actionData->fields);

        $reloadedEntity->set($data);
        $entity->set($data);

        if (!empty($actionData->formula)) {
            $this->getFormulaManager()->run($actionData->formula, $reloadedEntity, $this->getFormulaVariables());

            $clonedVariables = clone $this->getFormulaVariables();

            $this->getFormulaManager()->run($actionData->formula, $entity, $clonedVariables);
        }

        $entityManager->saveEntity($reloadedEntity, [
            'modifiedById' => 'system',
            'skipWorkflow' => !$this->bpmnProcess,
            'workflowId' => $this->getWorkflowId(),
        ]);

        return true;
    }
}
