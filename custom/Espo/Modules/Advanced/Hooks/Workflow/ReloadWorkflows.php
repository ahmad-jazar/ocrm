<?php

namespace Espo\Modules\Advanced\Hooks\Workflow;

use Espo\Modules\Advanced\Core\WorkflowManager;
use Espo\ORM\Entity;

class ReloadWorkflows
{
    public static $order = 9;

    private WorkflowManager $workflowManager;

    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    public function afterSave(Entity $entity): void
    {
        $this->workflowManager->loadWorkflows(true);
    }

    public function afterRemove(Entity $entity): void
    {
        $this->workflowManager->loadWorkflows(true);
    }
}
