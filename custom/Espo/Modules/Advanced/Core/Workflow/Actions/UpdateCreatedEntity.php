<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use stdClass;

class UpdateCreatedEntity extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        if (empty($actionData->target)) {
            return false;
        }

        $target = $actionData->target;

        $targetEntity = $this->getCreatedEntity($target);

        if (!$targetEntity) {
            return false;
        }

        if (property_exists($actionData, 'fields')) {
            $data = $this->getDataToFill($targetEntity, $actionData->fields);
            $targetEntity->set($data);
        }

        if (!empty($actionData->formula)) {
            $this->getFormulaManager()->run(
                $actionData->formula,
                $targetEntity,
                $this->getFormulaVariables()
            );
        }

        if (!$targetEntity->has('modifiedById')) {
            $targetEntity->set('modifiedByName', 'System');
        }

        $this->getEntityManager()->saveEntity($targetEntity, ['modifiedById' => 'system']);

        return true;
    }
}
