<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

class EventIntermediateTimerBoundary extends EventIntermediateTimerCatch
{
    public function proceedPending(): void
    {
        $flowNode = $this->getFlowNode();

        $flowNode->set('status', BpmnFlowNode::STATUS_IN_PROCESS);

        $this->getEntityManager()->saveEntity($flowNode);

        if ($this->getAttributeValue('cancelActivity')) {
            $this->getManager()->cancelActivityByBoundaryEvent($this->getFlowNode());
        }

        $this->processNextElement();
    }
}
