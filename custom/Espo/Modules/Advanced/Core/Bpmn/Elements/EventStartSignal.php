<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventStartSignal extends Base
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
