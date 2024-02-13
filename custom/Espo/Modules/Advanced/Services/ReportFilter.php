<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Modules\Advanced\Tools\ReportFilter\Service;
use Espo\ORM\Entity;
use Espo\Services\Record;

class ReportFilter extends Record
{
    protected $forceSelectAllAttributes = true;

    protected function afterCreateEntity(Entity $entity, $data)
    {
        $this->createFilterService()->rebuild($entity->get('entityType'));
    }

    protected function afterUpdateEntity(Entity $entity, $data)
    {
        $this->createFilterService()->rebuild($entity->get('entityType'));
    }

    protected function afterDeleteEntity(Entity $entity)
    {
        $this->createFilterService()->rebuild($entity->get('entityType'));
    }

    private function createFilterService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
