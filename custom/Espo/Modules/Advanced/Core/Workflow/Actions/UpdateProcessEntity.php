<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use stdClass;

class UpdateProcessEntity extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        $targetEntity = $this->bpmnProcess;

        $data = [];

        if (property_exists($actionData, 'fields')) {
            $data = $this->getDataToFill($targetEntity, $actionData->fields);
        }

        $targetEntity->set($data);

        if (!empty($actionData->formula)) {
            $this->getFormulaManager()->run(
                $actionData->formula,
                $targetEntity,
                $this->getFormulaVariables()
            );
        }

        $this->getEntityManager()->saveEntity($targetEntity, [
            'skipWorkflow' => true,
            'modifiedById' => 'system',
        ]);

        return true;
    }
}
