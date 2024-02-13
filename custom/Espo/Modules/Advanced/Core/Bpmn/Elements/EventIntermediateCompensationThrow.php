<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventIntermediateCompensationThrow extends EventEndCompensation
{
    protected function processAfterProcessed(): void
    {
        $this->processNextElement();
    }
}
