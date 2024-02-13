<?php

namespace Espo\Modules\Advanced\Entities;

use Espo\Core\ORM\Entity;

class ReportPanel extends Entity
{
    public const ENTITY_TYPE = 'ReportPanel';

    public function getReportId(): string
    {
        return $this->get('reportId');
    }

    public function getTargetEntityType(): string
    {
        return $this->get('entityType');
    }
}
