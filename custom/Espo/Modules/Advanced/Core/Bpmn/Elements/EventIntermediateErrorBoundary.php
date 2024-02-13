<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

class EventIntermediateErrorBoundary extends EventIntermediateBoundary
{
    public function process(): void
    {
        $this->storeErrorVariables();
        $this->processNextElement();
    }

    private function storeErrorVariables(): void
    {
        $variables = $this->getVariables();

        $variables->__caughtErrorCode = $this->getFlowNode()->getDataItemValue('code');
        $variables->__caughtErrorMessage = $this->getFlowNode()->getDataItemValue('message');

        $this->getProcess()->setVariables($variables);
        $this->saveProcess();
    }
}
