<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventIntermediateSignalThrow extends EventSignal
{
    public function process(): void
    {
        $nextFlowNode = $this->prepareNextFlowNode();

        $this->setProcessed();

        $signal = $this->getSignal();

        if ($signal && is_string($signal)) {
            if (mb_substr($signal, 0, 1) !== '@') {
                $this->getManager()->broadcastSignal($signal);
            }
        } else {
            $GLOBALS['log']->warning("BPM: eventIntermediateSignalThrow, no signal");
        }

        $this->refreshProcess();
        $this->refreshTarget();

        if ($nextFlowNode) {
            $this->processPreparedNextFlowNode($nextFlowNode);
        }

        if ($signal && is_string($signal)) {
            if (mb_substr($signal, 0, 1) !== '@') {
                $this->getSignalManager()->trigger($signal);
            } else {
                $this->getSignalManager()->trigger($signal, $this->getTarget());
            }
        }
    }
}
