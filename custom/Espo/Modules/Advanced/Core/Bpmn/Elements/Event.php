<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;

abstract class Event extends Base
{
    protected function rejectConcurrentPendingFlows()
    {
        $flowNode = $this->getFlowNode();

        if ($flowNode->get('previousFlowNodeElementType') === 'gatewayEventBased') {
            $concurrentFlowNodeList = $this->getEntityManager()
                ->getRepository(BpmnFlowNode::ENTITY_TYPE)
                ->where([
                    'previousFlowNodeElementType' => 'gatewayEventBased',
                    'previousFlowNodeId' => $flowNode->get('previousFlowNodeId'),
                    'processId' => $flowNode->getProcessId(),
                    'id!=' => $flowNode->get('id'),
                    'status' => BpmnFlowNode::STATUS_PENDING,
                ])
                ->find();

            foreach ($concurrentFlowNodeList as $concurrentFlowNode) {
                $concurrentFlowNode->set('status', BpmnFlowNode::STATUS_REJECTED);

                $this->getEntityManager()->saveEntity($concurrentFlowNode);
            }
        }
    }
}
