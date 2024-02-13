<?php

namespace Espo\Modules\Advanced\Core;

use Espo\Core\Utils\DateTime;
use Espo\Modules\Advanced\Entities\BpmnSignalListener;
use Espo\ORM\EntityManager;
use Espo\ORM\Entity;
use Espo\Core\Utils\Config;

class SignalManager
{
    private EntityManager $entityManager;
    private WorkflowManager $workflowManager;
    private Config $config;

    public function __construct(
        EntityManager $entityManager,
        WorkflowManager $workflowManager,
        Config $config
    ) {
        $this->entityManager = $entityManager;
        $this->workflowManager = $workflowManager;
        $this->config = $config;
    }

    public function trigger($signal, ?Entity $entity = null, array $options = []): void
    {
        if (is_array($signal)) {
            $signal = implode('.', $signal);
        }

        if ($this->config->get('signalsDisabled')) {
            return;
        }

        if ($entity) {
            $this->workflowManager->process($entity, '$' . $signal, $options);

            return;
        }

        if ($this->config->get('signalsRegularDisabled')) {
            return;
        }

        $listenerList = $this->entityManager
            ->getRDBRepository(BpmnSignalListener::ENTITY_TYPE)
            ->select(['id'])
            ->order('number')
            ->where([
                'name' => $signal,
                'isTriggered' => false,
            ])
            ->find();

        foreach ($listenerList as $item) {
            $item->set('isTriggered', true);
            $item->set('triggeredAt', date(DateTime::SYSTEM_DATE_TIME_FORMAT));

            $this->entityManager->saveEntity($item);
        }
    }

    public function subscribe(string $signal, string $flowNodeId): ?string
    {
        if ($this->config->get('signalsDisabled')) {
            return null;
        }

        if ($this->config->get('signalsRegularDisabled')) {
            return null;
        }

        $item = $this->entityManager->createEntity(BpmnSignalListener::ENTITY_TYPE, [
            'name' => $signal,
            'flowNodeId' => $flowNodeId,
        ]);

        return $item->getId();
    }

    public function unsubscribe(string $id): void
    {
        $this->entityManager
            ->getRepository(BpmnSignalListener::ENTITY_TYPE)
            ->deleteFromDb($id);
    }
}
