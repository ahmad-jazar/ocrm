<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventEndTerminate extends Base
{
    public function process(): void
    {
        $this->setProcessed();
        $this->getManager()->endProcess($this->getProcess(), true);
    }
}
