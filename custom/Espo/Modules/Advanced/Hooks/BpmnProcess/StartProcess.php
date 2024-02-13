<?php

namespace Espo\Modules\Advanced\Hooks\BpmnProcess;

use Espo\Core\InjectableFactory;
use Espo\Modules\Advanced\Core\Bpmn\BpmnManager;
use Espo\Modules\Advanced\Entities\BpmnProcess;
use Espo\ORM\Entity;

class StartProcess
{
    private InjectableFactory $injectableFactory;

    public function __construct(
        InjectableFactory $injectableFactory
    ) {
        $this->injectableFactory = $injectableFactory;
    }

    /**
     * @param BpmnProcess $entity
     */
    public function afterSave(Entity $entity, array $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        if (!empty($options['skipStartProcessFlow'])) {
            return;
        }

        $manager = $this->injectableFactory->create(BpmnManager::class);

        $manager->startCreatedProcess($entity);
    }
}
