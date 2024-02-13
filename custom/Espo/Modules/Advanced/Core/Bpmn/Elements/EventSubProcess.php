<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Entities\BpmnProcess;

class EventSubProcess extends SubProcess
{
    protected function getSubProcessStartElementId(): ?string
    {
        $eventStartData = $this->getAttributeValue('eventStartData') ?? (object) [];

        return $eventStartData->id ?? null;
    }

    public function complete(): void
    {
        $this->setProcessed();

        if ($this->getProcess()->getStatus() === BpmnProcess::STATUS_STARTED) {
            $this->endProcessFlow();
        }
    }

    protected function isMultiInstance(): bool
    {
        return false;
    }
}
