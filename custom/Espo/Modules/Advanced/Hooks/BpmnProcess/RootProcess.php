<?php

namespace Espo\Modules\Advanced\Hooks\BpmnProcess;

use Espo\Modules\Advanced\Entities\BpmnProcess;
use Espo\ORM\Entity;

class RootProcess
{
    /**
     * @param BpmnProcess $entity
     */
    public function beforeSave(Entity $entity): void
    {
        if (!$entity->isNew()) {
            return;
        }

        if ($entity->getRootProcessId()) {
            return;
        }

        $entity->set('rootProcessId', $entity->getId());
    }
}
