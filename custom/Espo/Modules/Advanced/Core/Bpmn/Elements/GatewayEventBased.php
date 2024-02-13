<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

class GatewayEventBased extends Gateway
{
    protected function processDivergent(): void
    {
        $flowNode = $this->getFlowNode();

        $item = $flowNode->get('elementData');
        $nextElementIdList = $item->nextElementIdList ?? [];

        $flowNode->set('status', BpmnFlowNode::STATUS_IN_PROCESS);

        $this->getEntityManager()->saveEntity($flowNode);

        foreach ($nextElementIdList as $nextElementId) {
            $nextFlowNode = $this->processNextElement($nextElementId, false, true);

            if ($nextFlowNode->getStatus() === BpmnFlowNode::STATUS_PROCESSED) {
                break;
            }
        }

        $this->setProcessed();
        $this->getManager()->tryToEndProcess($this->getProcess());
    }

    protected function processConvergent(): void
    {
        $this->processNextElement();
    }
}
