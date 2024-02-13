<?php

namespace Espo\Modules\Advanced\Hooks\Workflow;

use Espo\Entities\Job;
use Espo\Modules\Advanced\Entities\Workflow;
use Espo\Modules\Advanced\Tools\Workflow\Jobs\RunScheduledWorkflow as RunScheduledWorkflowJob;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class RemoveJobs
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Workflow $entity
     */
    public function afterSave(Entity $entity): void
    {
        $toRemove = $entity->getType() === Workflow::TYPE_SCHEDULED &&
            (
                $entity->isAttributeChanged('scheduling') ||
                $entity->isAttributeChanged('schedulingApplyTimezone')
            );

        if (!$toRemove) {
            return;
        }

        $pendingJobList = $this->entityManager
            ->getRDBRepository(Job::ENTITY_TYPE)
            ->where([
                'className' => RunScheduledWorkflowJob::class,
                'status' => 'Pending',
                'targetType' => Workflow::ENTITY_TYPE,
                'targetId' => $entity->getId(),
            ])
            ->find();

        foreach ($pendingJobList as $pendingJob) {
            $this->entityManager->removeEntity($pendingJob);
        }
    }
}
