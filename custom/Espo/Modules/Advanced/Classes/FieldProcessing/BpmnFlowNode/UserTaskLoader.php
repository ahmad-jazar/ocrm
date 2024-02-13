<?php

namespace Espo\Modules\Advanced\Classes\FieldProcessing\BpmnFlowNode;

use Espo\Modules\Advanced\Entities\BpmnUserTask;
use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

class UserTaskLoader implements Loader
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(Entity $entity, Params $params): void
    {
        if ($entity->get('elementType') !== 'taskUser') {
            return;
        }

        $userTask = $this->entityManager
            ->getRDBRepository(BpmnUserTask::ENTITY_TYPE)
            ->where(['flowNodeId' => $entity->getId()])
            ->findOne();

        if ($userTask) {
            $entity->set('userTaskId', $userTask->getId());
        }
    }
}
