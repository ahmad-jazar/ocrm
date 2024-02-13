<?php

namespace Espo\Modules\Advanced\Repositories;

use Espo\ORM\Entity;

class Workflow extends \Espo\Core\ORM\Repositories\RDB
{
    protected function afterSave(Entity $entity, array $options = [])
    {
        parent::afterSave($entity, $options);

        if (
            $entity->get('type') == 'scheduled' &&
            (
                $entity->isAttributeChanged('scheduling') ||
                $entity->isAttributeChanged('schedulingApplyTimezone')
            )
        ) {
            $this->removePendingJobsForScheduledWorkflow($entity);
        }
    }

    protected function removePendingJobsForScheduledWorkflow(Entity $entity)
    {
        $pendingJobList = $this->getEntityManager()
            ->getRepository('Job')
            ->where([
                'methodName' => 'runScheduledWorkflow',
                'status' => 'Pending',
                'targetType' => 'Workflow',
                'targetId' => $entity->get('id'),
            ])
            ->find();

        foreach ($pendingJobList as $pendingJob) {
            $this->getEntityManager()->removeEntity($pendingJob);
        }
    }
}
