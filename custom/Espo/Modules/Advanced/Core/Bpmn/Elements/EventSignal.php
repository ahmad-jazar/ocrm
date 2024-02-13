<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Core\Bpmn\Utils\Helper;
use Espo\Modules\Advanced\Core\SignalManager;

abstract class EventSignal extends Event
{
    protected function getSignalManager(): SignalManager
    {
        /** @var SignalManager */
        return $this->getContainer()->get('signalManager');
    }

    protected function getSignal(): ?string
    {
        $name = $this->getAttributeValue('signal');

        if (!$name) {
            return $name;
        }

        return Helper::applyPlaceholders($name, $this->getTarget(), $this->getVariables());
    }
}
