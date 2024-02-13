<?php

namespace Espo\Modules\Advanced\Repositories;

use \Espo\ORM\Entity;

class BpmnUserTask extends \Espo\Core\ORM\Repositories\RDB
{
    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        if (!$entity->get('isResolved') && $entity->get('resolution') && $entity->isAttributeChanged('resolution')) {
            $entity->set('isResolved', true);
        }

        if (!$entity->get('isResolved') && $entity->get('isCanceled') && $entity->isAttributeChanged('isCanceled')) {
            $entity->set('resolution', 'Canceled');
        }
    }
}
