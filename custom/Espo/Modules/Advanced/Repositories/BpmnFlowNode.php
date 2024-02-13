<?php

namespace Espo\Modules\Advanced\Repositories;

use \Espo\ORM\Entity;

class BpmnFlowNode extends \Espo\Core\ORM\Repositories\RDB
{
    protected $hooksDisabled = true;

    protected $processFieldsAfterSaveDisabled = true;

    protected $processFieldsBeforeSaveDisabled = true;

    protected $processFieldsAfterRemoveDisabled = true;
}