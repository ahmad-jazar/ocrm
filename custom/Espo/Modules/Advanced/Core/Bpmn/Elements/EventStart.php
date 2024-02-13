<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventStart extends Base
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
