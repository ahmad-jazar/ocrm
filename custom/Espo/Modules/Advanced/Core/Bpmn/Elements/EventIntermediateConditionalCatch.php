<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Core\Bpmn\Utils\ConditionManager;
use Espo\Modules\Advanced\Entities\BpmnFlowNode;
use Espo\ORM\Entity;

class EventIntermediateConditionalCatch extends Event
{
    protected $pendingStatus = BpmnFlowNode::STATUS_PENDING;

    public function process(): void
    {
        $target = $this->getConditionsTarget();

        if (!$target) {
            $this->fail();

            return;
        }

        $result = $this->getConditionManager()->check(
            $target,
            $this->getAttributeValue('conditionsAll'),
            $this->getAttributeValue('conditionsAny'),
            $this->getAttributeValue('conditionsFormula'),
            $this->getVariablesForFormula()
        );

        if ($result) {
            $this->rejectConcurrentPendingFlows();
            $this->processNextElement();

            return;
        }

        $flowNode = $this->getFlowNode();

        $flowNode->set([
            'status' => $this->pendingStatus,
        ]);

        $this->getEntityManager()->saveEntity($flowNode);
    }

    public function proceedPending(): void
    {
        $target = $this->getConditionsTarget();

        if (!$target) {
            $this->fail();

            return;
        }

        $result = $this->getConditionManager()->check(
            $target,
            $this->getAttributeValue('conditionsAll'),
            $this->getAttributeValue('conditionsAny'),
            $this->getAttributeValue('conditionsFormula'),
            $this->getVariablesForFormula()
        );

        if ($result) {
            $this->rejectConcurrentPendingFlows();
            $this->processNextElement();
        }
    }

    protected function getConditionsTarget(): ?Entity
    {
        return $this->getTarget();
    }

    protected function getConditionManager(): ConditionManager
    {
        $conditionManager = new ConditionManager($this->getContainer());

        $conditionManager->setCreatedEntitiesData($this->getCreatedEntitiesData());

        return $conditionManager;
    }
}
