<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventStartTimer extends Base
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
