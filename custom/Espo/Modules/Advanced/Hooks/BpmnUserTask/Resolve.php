<?php

namespace Espo\Modules\Advanced\Hooks\BpmnUserTask;

use Espo\Core\InjectableFactory;
use Espo\Modules\Advanced\Core\Bpmn\BpmnManager;
use Espo\Modules\Advanced\Entities\BpmnFlowNode;
use Espo\Modules\Advanced\Entities\BpmnUserTask;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class Resolve
{
    private EntityManager $entityManager;
    private InjectableFactory $injectableFactory;

    public function __construct(
        EntityManager $entityManager,
        InjectableFactory $injectableFactory
    ) {
        $this->entityManager = $entityManager;
        $this->injectableFactory = $injectableFactory;
    }

    /**
     * @param BpmnUserTask $entity
     */
    public function afterSave(Entity $entity): void
    {
        $flowNodeId = $entity->get('flowNodeId');

        if (!$flowNodeId) {
            return;
        }

        if (!$entity->getFetched('isResolved') && $entity->get('isResolved')) {
            /** @var ?BpmnFlowNode $flowNode */
            $flowNode = $this->entityManager->getEntityById(BpmnFlowNode::ENTITY_TYPE, $flowNodeId);

            if (!$flowNode) {
                return;
            }

            if ($flowNode->getStatus() !== BpmnFlowNode::STATUS_IN_PROCESS) {
                return;
            }

            $manager = $this->injectableFactory->create(BpmnManager::class);

            $manager->completeFlow($flowNode);
        }
    }
}
