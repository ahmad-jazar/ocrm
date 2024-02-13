<?php

namespace Espo\Modules\Advanced\Hooks\BpmnProcess;

use Espo\Core\InjectableFactory;
use Espo\Modules\Advanced\Core\Bpmn\BpmnManager;
use Espo\Modules\Advanced\Entities\BpmnProcess;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class StopProcess
{
    private InjectableFactory $injectableFactory;
    private EntityManager $entityManager;

    public function __construct(
        InjectableFactory $injectableFactory,
        EntityManager $entityManager
    ) {
        $this->injectableFactory = $injectableFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param BpmnProcess $entity
     */
    public function afterSave(Entity $entity, array $options): void
    {
        if (!empty($options['skipStopProcess'])) {
            return;
        }

        if ($entity->isNew()) {
            return;
        }

        if (!$entity->isAttributeChanged('status')) {
            return;
        }

        if ($entity->getStatus() !== BpmnProcess::STATUS_STOPPED) {
            return;
        }

        $manager = $this->injectableFactory->create(BpmnManager::class);

        $manager->stopProcess($entity);

        $subProcessList = $this->entityManager
            ->getRDBRepository(BpmnProcess::ENTITY_TYPE)
            ->where(['parentProcessId' => $entity->getId()])
            ->find();

        foreach ($subProcessList as $e) {
            $manager->stopProcess($e);
        }
    }
}
