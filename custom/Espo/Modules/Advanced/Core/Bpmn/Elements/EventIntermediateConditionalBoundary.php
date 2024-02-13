<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

class EventIntermediateConditionalBoundary extends EventIntermediateConditionalCatch
{
    public function process(): void
    {
        $result = $this->getConditionManager()->check(
            $this->getTarget(),
            $this->getAttributeValue('conditionsAll'),
            $this->getAttributeValue('conditionsAny'),
            $this->getAttributeValue('conditionsFormula'),
            $this->getVariablesForFormula()
        );

        if ($result) {
            if ($this->getAttributeValue('cancelActivity')) {
                $this->getManager()->cancelActivityByBoundaryEvent($this->getFlowNode());
            }

            if (!$this->getAttributeValue('cancelActivity')) {
                $this->createOppositeNode();
            }

            $this->processNextElement();

            return;
        }

        $flowNode = $this->getFlowNode();

        $flowNode->set([
            'status' => BpmnFlowNode::STATUS_PENDING,
        ]);

        $this->getEntityManager()->saveEntity($flowNode);
    }

    public function proceedPending(): void
    {
        $result = $this->getConditionManager()->check(
            $this->getTarget(),
            $this->getAttributeValue('conditionsAll'),
            $this->getAttributeValue('conditionsAny'),
            $this->getAttributeValue('conditionsFormula'),
            $this->getVariablesForFormula()
        );

        if ($this->getFlowNode()->getDataItemValue('isOpposite')) {
            if (!$result) {
                $this->setProcessed();
                $this->createOppositeNode(true);
            }

            return;
        }

        if ($result) {
            if ($this->getAttributeValue('cancelActivity')) {
                $this->getManager()->cancelActivityByBoundaryEvent($this->getFlowNode());
            }

            if (!$this->getAttributeValue('cancelActivity')) {
                $this->createOppositeNode();
            }

            $this->processNextElement();
        }
    }

    protected function createOppositeNode($isNegative = false)
    {
        $flowNode = $this->getEntityManager()->getEntity(BpmnFlowNode::ENTITY_TYPE);

        $flowNode->set([
            'status' => BpmnFlowNode::STATUS_PENDING,
            'elementId' => $this->getFlowNode()->get('elementId'),
            'elementType' => $this->getFlowNode()->get('elementType'),
            'elementData' => $this->getFlowNode()->get('elementData'),
            'data' => [
                'isOpposite' => !$isNegative,
            ],
            'flowchartId' => $this->getProcess()->get('flowchartId'),
            'processId' => $this->getProcess()->get('id'),
            'previousFlowNodeElementType' => $this->getFlowNode()->get('previousFlowNodeElementType'),
            'previousFlowNodeId' => $this->getFlowNode()->get('previousFlowNodeId'),
            'divergentFlowNodeId' => $this->getFlowNode()->get('divergentFlowNodeId'),
            'targetType' => $this->getFlowNode()->get('targetType'),
            'targetId' => $this->getFlowNode()->get('targetId'),
        ]);

        $this->getEntityManager()->saveEntity($flowNode);
    }
}
