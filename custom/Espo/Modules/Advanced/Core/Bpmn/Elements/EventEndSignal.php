<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventEndSignal extends EventSignal
{
    public function process(): void
    {
        $this->setProcessed();

        $signal = $this->getSignal();

        if ($signal && is_string($signal)) {
            if (mb_substr($signal, 0, 1) !== '@') {
                $this->getManager()->broadcastSignal($signal);
            }
        } else {
            $GLOBALS['log']->warning("BPM: eventEndSignal, no signal");
        }

        $this->refreshProcess();
        $this->refreshTarget();

        $this->endProcessFlow();

        if ($signal && is_string($signal)) {
            if (mb_substr($signal, 0, 1) !== '@') {
                $this->getSignalManager()->trigger($signal);
            }
            else {
                $this->getSignalManager()->trigger($signal, $this->getTarget());
            }
        }
    }
}
