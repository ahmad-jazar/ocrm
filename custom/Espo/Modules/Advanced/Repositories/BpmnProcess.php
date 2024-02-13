<?php

namespace Espo\Modules\Advanced\Repositories;

use \Espo\ORM\Entity;

class BpmnProcess extends \Espo\Core\ORM\Repositories\RDB
{
    protected function afterRemove(Entity $entity, array $options = array())
    {
        parent::afterRemove($entity, $options);

        $flowNodeList = $this->getEntityManager()->getRepository('BpmnFlowNode')->where([
            'processId' => $entity->id,
            'status!=' => ['Processed', 'Rejected', 'Failed']
        ])->find();

        foreach ($flowNodeList as $flowNode) {
            $flowNode->set('status', 'Rejected');
            $this->getEntityManager()->saveEntity($flowNode);
        }
    }
}
