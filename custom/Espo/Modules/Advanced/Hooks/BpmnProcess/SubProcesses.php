<?php

namespace Espo\Modules\Advanced\Hooks\BpmnProcess;

use Espo\Modules\Advanced\Entities\BpmnProcess;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class SubProcesses
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function afterRemove(Entity $entity, array $options): void
    {
        $subProcessList = $this->entityManager
            ->getRDBRepository(BpmnProcess::ENTITY_TYPE)
            ->where(['parentProcessId' => $entity->getId()])
            ->find();

        foreach ($subProcessList as $e) {
            $this->entityManager->removeEntity($e, $options);
        }
    }
}
