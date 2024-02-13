<?php

namespace Espo\Modules\Advanced\Controllers;

use Espo\Core\Controllers\Record;

class Workflow extends Record
{
    protected function checkAccess(): bool
    {
        return $this->user->isAdmin();
    }
}
