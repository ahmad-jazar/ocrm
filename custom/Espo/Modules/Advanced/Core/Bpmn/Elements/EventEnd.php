<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventEnd extends Base
{
    public function process(): void
    {
        $this->setProcessed();
        $this->endProcessFlow();
    }
}
