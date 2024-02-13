<?php

namespace Espo\Modules\Advanced\Entities;

use Espo\Core\ORM\Entity;

class Workflow extends Entity
{
    public const ENTITY_TYPE = 'Workflow';

    public const TYPE_MANUAL = 'manual';
    public const TYPE_SCHEDULED = 'scheduled';

    public function getType(): string
    {
        return $this->get('type');
    }

    public function getTargetEntityType(): string
    {
        return $this->get('entityType');
    }

    public function isActive(): bool
    {
        return $this->get('isActive');
    }
}
