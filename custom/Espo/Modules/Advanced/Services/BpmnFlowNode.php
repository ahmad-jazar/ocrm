<?php

namespace Espo\Modules\Advanced\Services;

use Espo\ORM\Entity;

class BpmnFlowNode extends \Espo\Services\Record
{
    protected $forceSelectAllAttributes = true;

    public function loadAdditionalFieldsForList(Entity $entity)
    {
        if ($entity->get('elementType') === 'taskUser') {
            $userTask = $this->getEntityManager()
                ->getRepository('BpmnUserTask')
                ->where([
                    'flowNodeId' => $entity->get('id'),
                ])
                ->findOne();

            if ($userTask) {
                $entity->set('userTaskId', $userTask->get('id'));
            }
        }
    }
}
