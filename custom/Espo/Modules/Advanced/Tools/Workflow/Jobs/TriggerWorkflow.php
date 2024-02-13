<?php

namespace Espo\Modules\Advanced\Tools\Workflow\Jobs;

use Espo\Core\Exceptions\Error;
use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Modules\Advanced\Tools\Workflow\Service;
use Espo\ORM\EntityManager;

class TriggerWorkflow implements Job
{
    private Service $service;
    private EntityManager $entityManager;

    public function __construct(
        Service $service,
        EntityManager $entityManager
    ) {
        $this->service = $service;
        $this->entityManager = $entityManager;
    }

    public function run(Data $data): void
    {
        $data = $data->getRaw();

        if (
            empty($data->entityId) ||
            empty($data->entityType) ||
            empty($data->nextWorkflowId)
        ) {
            throw new Error('Workflow[' . $data->workflowId . '][triggerWorkflow]: Not sufficient job data.');
        }

        $entityId = $data->entityId;
        $entityType = $data->entityType;

        $entity = $this->entityManager->getEntityById($entityType, $entityId);

        if (!$entity) {
            throw new Error('Workflow[' . $data->workflowId . '][triggerWorkflow]: Entity not found.');
        }

        if (is_array($data->values)) {
            $data->values = (object) $data->values;
        }

        if (is_object($data->values)) {
            $values = get_object_vars($data->values);

            foreach ($values as $attribute => $value) {
                $entity->setFetched($attribute, $value);
            }
        }

        $this->service->triggerWorkflow($entity, $data->nextWorkflowId, true);
    }
}
