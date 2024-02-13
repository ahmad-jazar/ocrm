<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

class EventStartError extends Base
{
    public function process(): void
    {
        $this->writeErrorData();
        $this->storeErrorVariables();
        $this->processNextElement();
    }

    private function writeErrorData(): void
    {
        $flowNode = $this->getFlowNode();

        $parentFlowNodeId = $this->getProcess()->getParentProcessFlowNodeId();

        if (!$parentFlowNodeId) {
            return;
        }

        /** @var ?BpmnFlowNode $parentFlowNode */
        $parentFlowNode = $this->getEntityManager()->getEntityById(BpmnFlowNode::ENTITY_TYPE, $parentFlowNodeId);

        if (!$parentFlowNode) {
            return;
        }

        $code = $parentFlowNode->getDataItemValue('caughtErrorCode');
        $message = $parentFlowNode->getDataItemValue('caughtErrorMessage');

        $flowNode->setDataItemValue('code', $code);
        $flowNode->setDataItemValue('message', $message);

        $this->getEntityManager()->saveEntity($flowNode);
    }

    private function storeErrorVariables(): void
    {
        $variables = $this->getVariables();

        $variables->__caughtErrorCode = $this->getFlowNode()->getDataItemValue('code');
        $variables->__caughtErrorMessage = $this->getFlowNode()->getDataItemValue('message');

        $this->getProcess()->setVariables($variables);
        $this->saveProcess();
    }
}
