<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventEndEscalation extends Base
{
    public function process(): void
    {
        $this->setProcessed();

        $this->getManager()->escalate($this->getProcess(), $this->getAttributeValue('escalationCode'));

        $this->refreshProcess();
        $this->refreshTarget();

        $this->endProcessFlow();
    }
}
