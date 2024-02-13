<?php

namespace Espo\Modules\Advanced\Tools\Workflow\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Entities\Job as JobEntity;
use Espo\Modules\Advanced\Entities\Workflow as WorkflowEntity;
use Espo\Modules\Advanced\Tools\Report\ListType\RunParams as ListRunParams;
use Espo\Modules\Advanced\Tools\Report\Service as ReportService;
use Espo\Modules\Advanced\Tools\Workflow\Service;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use Exception;
use RuntimeException;

class RunScheduledWorkflow implements Job
{
    private ReportService $reportService;
    private EntityManager $entityManager;
    private Service $service;

    public function __construct(
        ReportService $reportService,
        EntityManager $entityManager,
        Service $service
    ) {
        $this->reportService = $reportService;
        $this->entityManager = $entityManager;
        $this->service = $service;
    }

    public function run(Data $data): void
    {
        $data = $data->getRaw();

        if (empty($data->workflowId)) {
            throw new RuntimeException();
        }

        /** @var ?WorkflowEntity $workflow */
        $workflow = $this->entityManager->getEntityById(WorkflowEntity::ENTITY_TYPE, $data->workflowId);

        if (!$workflow instanceof Entity) {
            throw new RuntimeException('Workflow['.$data->workflowId.'][runScheduledWorkflow]: Entity is not found.');
        }

        if (!$workflow->isActive()) {
            return;
        }

        $targetReport = $this->entityManager
            ->getRDBRepository(WorkflowEntity::ENTITY_TYPE)
            ->getRelation($workflow, 'targetReport')
            ->findOne();

        if (!$targetReport instanceof Entity) {
            throw new RuntimeException(
                'Workflow['.$data->workflowId.'][runScheduledWorkflow]: ' .
                'TargetReport Entity is not found.');
        }

        $result = $this->reportService->runList(
            $targetReport->getId(),
            null,
            null,
            ListRunParams::create()->withReturnSthCollection()
        );

        foreach ($result->getCollection() as $entity) {
            try {
                $this->runScheduledWorkflowForEntity(
                    $workflow->getId(),
                    $entity->getEntityType(),
                    $entity->getId()
                );
            }
            catch (Exception $e) {
                // @todo Revise the need of catch.
                $job = $this->entityManager->getNewEntity(JobEntity::ENTITY_TYPE);

                $runData = (object) [
                    'workflowId' => $workflow->getId(),
                    'entityType' => $entity->getEntityType(),
                    'entityId' => $entity->getId(),
                ];

                $job->set([
                    'name' => RunScheduledWorkflowForEntity::class,
                    'className' => RunScheduledWorkflowForEntity::class,
                    'data' => $runData,
                    'executeTime' => date('Y-m-d H:i:s'),
                ]);

                $this->entityManager->saveEntity($job);
            }
        }
    }

    private function runScheduledWorkflowForEntity(string $workflowId, string $entityType, string $id): void
    {
        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity instanceof Entity) {
            throw new RuntimeException(
                'Workflow['.$workflowId.'][runActions]: ' .
                'Entity['.$entityType.'] ['. $id . '] is not found.');
        }

        $this->service->triggerWorkflow($entity, $workflowId);
    }
}
