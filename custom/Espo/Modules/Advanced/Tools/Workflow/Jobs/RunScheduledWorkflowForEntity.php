<?php

namespace Espo\Modules\Advanced\Tools\Workflow\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Modules\Advanced\Tools\Workflow\Service;
use Espo\ORM\EntityManager;

use RuntimeException;

class RunScheduledWorkflowForEntity implements Job
{
    private EntityManager $entityManager;
    private Service $service;

    public function __construct(
        EntityManager $entityManager,
        Service $service
    ) {
        $this->entityManager = $entityManager;
        $this->service = $service;
    }

    public function run(Data $data): void
    {
        $data = $data->getRaw();

        $entityType = $data->entityType;
        $id = $data->entityId;
        $workflowId = $data->workflowId;

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            throw new RuntimeException(
                'Workflow['.$workflowId.'][runActions]: ' .
                'Entity['.$entityType.'] ['. $id . '] is not found.');
        }

        $this->service->triggerWorkflow($entity, $workflowId);
    }
}
