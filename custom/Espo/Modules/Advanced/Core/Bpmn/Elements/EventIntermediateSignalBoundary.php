<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

class EventIntermediateSignalBoundary extends EventSignal
{
    public function process(): void
    {
        $signal = $this->getSignal();

        if (!$signal) {
            $this->fail();

            $GLOBALS['log']->warning("BPM: No signal for sub-process EventIntermediateSignalBoundary");

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

        if ($this->getAttributeValue('cancelActivity')) {
            $this->getManager()->cancelActivityByBoundaryEvent($this->getFlowNode());
        } else {
            $this->createCopy();
        }

        $this->processNextElement();
    }

    protected function createCopy(): void
    {
        $data = $this->getFlowNode()->get('data') ?? (object) [];

        $data = clone $data;

        $flowNode = $this->getEntityManager()->getEntity(BpmnFlowNode::ENTITY_TYPE);

        $flowNode->set([
            'status' => BpmnFlowNode::STATUS_PENDING,
            'elementId' => $this->getFlowNode()->get('elementId'),
            'elementType' => $this->getFlowNode()->get('elementType'),
            'elementData' => $this->getFlowNode()->get('elementData'),
            'data' => $data,
            'flowchartId' => $this->getProcess()->get('flowchartId'),
            'processId' => $this->getProcess()->get('id'),
            'previousFlowNodeElementType' => $this->getFlowNode()->get('previousFlowNodeElementType'),
            'previousFlowNodeId' => $this->getFlowNode()->get('previousFlowNodeId'),
            'divergentFlowNodeId' => $this->getFlowNode()->get('divergentFlowNodeId'),
            'targetType' => $this->getFlowNode()->get('targetType'),
            'targetId' => $this->getFlowNode()->get('targetId'),
        ]);

        $this->getEntityManager()->saveEntity($flowNode);

        $this->getSignalManager()->subscribe($this->getSignal(), $flowNode->get('id'));
    }
}
