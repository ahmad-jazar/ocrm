<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventStartCompensation extends Base
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
