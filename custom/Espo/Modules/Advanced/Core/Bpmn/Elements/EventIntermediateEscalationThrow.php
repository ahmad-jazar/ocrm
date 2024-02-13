<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventIntermediateEscalationThrow extends Event
{
    public function process(): void
    {
        $this->getManager()->escalate($this->getProcess(), $this->getAttributeValue('escalationCode'));

        $this->refreshProcess();
        $this->refreshTarget();

        $this->processNextElement();
    }
}
