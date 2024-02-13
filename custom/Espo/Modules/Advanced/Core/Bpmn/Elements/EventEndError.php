<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventEndError extends Base
{
    public function process(): void
    {
        $this->setProcessed();
        $this->getManager()->endProcessWithError($this->getProcess(), $this->getAttributeValue('errorCode'));
    }
}
