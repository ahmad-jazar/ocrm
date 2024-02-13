<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventIntermediateBoundary extends Event
{
    public function process(): void
    {
        $this->processNextElement();
    }
}
