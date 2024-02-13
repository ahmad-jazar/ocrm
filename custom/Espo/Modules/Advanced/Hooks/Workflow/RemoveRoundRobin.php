<?php

namespace Espo\Modules\Advanced\Hooks\Workflow;

use Espo\Modules\Advanced\Entities\WorkflowRoundRobin;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class RemoveRoundRobin
{
    public static $order = 9;

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function afterRemove(Entity $entity): void
    {
        $roundRobinList = $this->entityManager
            ->getRDBRepository(WorkflowRoundRobin::ENTITY_TYPE)
            ->where(['workflowId' => $entity->getId()])
            ->find();

        foreach ($roundRobinList as $item) {
            $this->entityManager->removeEntity($item);
        }
    }
}
