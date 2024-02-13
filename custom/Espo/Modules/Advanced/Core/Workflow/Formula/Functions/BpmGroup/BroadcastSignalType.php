<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\BpmGroup;

use Espo\Core\Di\EntityManagerAware;
use Espo\Core\Di\EntityManagerSetter;
use Espo\Core\Di\InjectableFactoryAware;
use Espo\Core\Di\InjectableFactorySetter;
use Espo\Core\Exceptions\Error;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Modules\Advanced\Core\SignalManager;

class BroadcastSignalType extends BaseFunction implements InjectableFactoryAware, EntityManagerAware
{
    use InjectableFactorySetter;
    use EntityManagerSetter;

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        $signal = $args[0] ?? null;
        $entityType = $args[1] ?? null;
        $id = $args[2] ?? null;

        if (!$signal) {
            throw new Error("Formula: bpm\\broadcastSignal: No signal name.");
        }

        if (!is_string($signal)) {
            throw new Error("Formula: bpm\\broadcastSignal: Bad signal name.");
        }

        $entity = null;

        if ($entityType && $id) {
            $entity = $this->entityManager->getEntityById($entityType, $id);

            if (!$entity) {
                throw new Error("Formula: bpm\\broadcastSignal: The entity does not exist.");
            }
        }

        $this->getSignalManager()->trigger($signal, $entity);
    }

    private function getSignalManager(): SignalManager
    {
        return $this->injectableFactory->create(SignalManager::class);
    }
}
