<?php

namespace Espo\Modules\Advanced\Hooks\Common;

use Espo\Modules\Advanced\Core\WorkflowManager;
use Espo\ORM\Entity;

class Workflow
{
    public static $order = 99;

    private WorkflowManager $workflowManager;

    public function __construct(WorkflowManager $workflowManager) {
        $this->workflowManager = $workflowManager;
    }

    public function afterSave(Entity $entity, array $options): void
    {
        if (!empty($options['skipWorkflow'])) {
            return;
        }

        if (!empty($options['silent'])) {
            return;
        }

        if ($entity->isNew()) {
            $this->workflowManager->process($entity, WorkflowManager::AFTER_RECORD_CREATED, $options);
        } else {
            $this->workflowManager->process($entity, WorkflowManager::AFTER_RECORD_UPDATED, $options);
        }

        $this->workflowManager->process($entity, WorkflowManager::AFTER_RECORD_SAVED, $options);
    }
}
