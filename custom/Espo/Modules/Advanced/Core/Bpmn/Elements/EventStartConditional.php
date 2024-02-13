<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventStartConditional extends Base
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
