<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;
use stdClass;

class UnrelateFromEntity extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        if (empty($actionData->entityId) || empty($actionData->link)) {
            throw new Error('Workflow['.$this->getWorkflowId().']: Bad params defined for UnrelateFromEntity');
        }

        $foreignEntityType = $entity->getRelationParam($actionData->link, 'entity');

        if (!$foreignEntityType) {
            throw new Error('Workflow['.$this->getWorkflowId().
                ']: Could not find foreign entity type for UnrelateFromEntity');
        }

        $foreignEntity = $this->getEntityManager()->getEntity($foreignEntityType, $actionData->entityId);

        if (!$foreignEntity) {
            throw new Error('Workflow['.$this->getWorkflowId().
                ']: Could not find foreign entity for UnrelateFromEntity');
        }

        $this->getEntityManager()
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, $actionData->link)
            ->unrelate($foreignEntity);

        return true;
    }
}
