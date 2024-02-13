<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventIntermediateEscalationBoundary extends Event
{
    public function process(): void
    {
        if ($this->getAttributeValue('cancelActivity')) {
            $this->getManager()->cancelActivityByBoundaryEvent($this->getFlowNode());
        }

        $this->setProcessed();

        $this->processNextElement();
    }
}
