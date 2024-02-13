<?php

namespace Espo\Modules\Advanced\Services;

use \Espo\ORM\Entity;
use \Espo\Core\Exceptions\Forbidden;

class BpmnUserTask extends \Espo\Services\Record
{
    public function createEntity($data)
    {
        throw new Forbidden();
    }

    protected $readOnlyAttributeList = [
        'actionType',
        'targetId',
        'targetType',
        'processId',
        'flowNodeId',
        'isResolved',
        'isCanceled',
        'instructions',
    ];

    protected function beforeUpdateEntity(Entity $entity, $data)
    {
        if (!$this->getUser()->isAdmin()) {
            if ($entity->getFetched('isResolved')) {
                if ($entity->isAttributeChanged('resolution')) {
                    throw new Forbidden();
                }
            }
            if ($entity->getFetched('isCanceled')) {
                if ($entity->isAttributeChanged('resolution')) {
                    throw new Forbidden();
                }
            }
        }
    }
}
