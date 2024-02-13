<?php

namespace Espo\Modules\Advanced\Core\Workflow;

use Espo\Core\Exceptions\Error;

use Exception;
use stdClass;

class ActionManager extends BaseManager
{
    protected string $dirName = 'Actions';
    protected array $requiredOptions = ['type'];

    /**
     * @param stdClass[] $actions
     * @throws Error
     */
    public function runActions(array $actions): void
    {
        if (!isset($actions)) {
            return;
        }

        $this->getLog()->debug('Workflow\ActionManager: Start workflow rule ID ['.$this->getWorkflowId().'].');

        $variables = (object) [];

        // Should be initialized before the loop.
        $processId = $this->getProcessId();

        foreach ($actions as $action) {
            $this->runAction($action, $processId, $variables);
        }

        $this->getLog()->debug('Workflow\ActionManager: End workflow rule ID ['.$this->getWorkflowId().'].');
    }

    /**
     * @throws Error
     */
    private function runAction(stdClass $actionData, ?string $processId, stdClass $variables): void
    {
        $entity = $this->getEntity($processId);

        $entityType = $entity->getEntityType();

        if (!$this->validate($actionData)) {
            $GLOBALS['log']->warning(
                'Workflow['.$this->getWorkflowId($processId).']: Action data is broken for the Entity ['.
                $entityType.'].');

            return;
        }

        $actionImpl = $this->getImpl($actionData->type, $processId);

        if (!isset($actionImpl)) {
            return;
        }

        try {
            $actionImpl->process($entity, $actionData, null, $variables);

            $this->copyVariables($actionImpl->getVariablesBack(), $variables);
        }
        catch (Exception $e) {
            $GLOBALS['log']->error(
                'Workflow[' . $this->getWorkflowId($processId) . ']: Action failed [' .
                $actionData->type . '] with cid [' .
                $actionData->cid . '], details: ' . $e->getMessage() . '.');
        }
    }

    private function copyVariables(object $source, object $destination)
    {
        foreach (get_object_vars($destination) as $k => $v) {
            unset($destination->$k);
        }

        foreach (get_object_vars($source) as $k => $v) {
            $destination->$k = $v;
        }
    }
}
