<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

class EventIntermediateSignalCatch extends EventSignal
{
    public function process(): void
    {
        $signal = $this->getSignal();

        if (!$signal) {
            $this->fail();

            $GLOBALS['log']->warning("BPM: No signal for EventIntermediateSignalCatch");

            return;
        }

        $flowNode = $this->getFlowNode();
        $flowNode->set([
            'status' => BpmnFlowNode::STATUS_PENDING,
        ]);
        $this->getEntityManager()->saveEntity($flowNode);

        $this->getSignalManager()->subscribe($signal, $flowNode->get('id'));
    }

    public function proceedPending(): void
    {
        $flowNode = $this->getFlowNode();
        $flowNode->set('status', BpmnFlowNode::STATUS_IN_PROCESS);
        $this->getEntityManager()->saveEntity($flowNode);

        $this->rejectConcurrentPendingFlows();
        $this->processNextElement();
    }
}
