<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventStartEscalation extends Base
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
