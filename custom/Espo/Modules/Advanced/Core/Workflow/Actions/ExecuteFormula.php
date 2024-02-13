<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use stdClass;

class ExecuteFormula extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        if (empty($actionData->formula)) {
            return true;
        }

        $reloadedEntity = $this->getEntityManager()->getEntity($entity->getEntityType(), $entity->get('id'));

        $variables = $this->getFormulaVariables();

        $this->getFormulaManager()->run($actionData->formula, $reloadedEntity, $variables);

        $this->updateVariables($variables);

        $isChanged = false;

        $changedMap = (object) [];

        foreach ($reloadedEntity->getAttributeList() as $attribute) {
            if ($reloadedEntity->isAttributeChanged($attribute)) {
                $changedMap->$attribute = $reloadedEntity->get($attribute);

                $isChanged = true;
            }
        }

        if ($isChanged) {
            $this->getEntityManager()->saveEntity($reloadedEntity, [
                'modifiedById' => 'system',
                'skipWorkflow' => !$this->bpmnProcess,
                'workflowId' => $this->getWorkflowId(),
            ]);

            $entity->set($changedMap);
        }

        return true;
    }
}
