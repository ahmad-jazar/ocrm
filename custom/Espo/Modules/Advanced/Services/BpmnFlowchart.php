<?php

namespace Espo\Modules\Advanced\Services;

use Espo\ORM\Entity;

use Espo\Core\Exceptions\Forbidden;

class BpmnFlowchart extends \Espo\Services\Record
{
    protected $readOnlyAttributeList = [
        'elementsDataHash',
        'isManuallyStartable',
        'eventStartIdList',
    ];

    /**
     * @todo Remove.
     */
    protected $exportSkipAttributeList = [
        'hasNoneStartEvent',
        'elementsDataHash',
    ];

    protected $forceSelectAllAttributes = true;

    protected function beforeUpdateEntity(Entity $entity, $data)
    {
        if (!$entity->isNew() && $entity->isAttributeChanged('targetType')) {
            throw new Forbidden("BpmnFlowchart: Can't change targetType.");
        }
    }
}
