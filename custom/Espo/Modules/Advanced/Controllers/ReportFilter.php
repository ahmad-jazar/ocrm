<?php

namespace Espo\Modules\Advanced\Controllers;

use Espo\Core\Controllers\Record;
use Espo\Modules\Advanced\Tools\ReportFilter\Service;

class ReportFilter extends Record
{
    protected function checkAccess(): bool
    {
        return $this->user->isAdmin();
    }

    public function postActionRebuild(): bool
    {
        $this->getFilterService()->rebuild();

        return true;
    }

    public function getFilterService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
