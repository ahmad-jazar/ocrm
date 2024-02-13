<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Core\Exceptions\NotFound;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\Error;
use Espo\Modules\Advanced\Core\Bpmn\BpmnManager;
use Espo\Modules\Advanced\Core\Bpmn\Elements\Activity;
use Espo\Modules\Advanced\Entities\BpmnFlowNode as BpmnFlowNodeEntity;
use Espo\Modules\Advanced\Entities\BpmnProcess as BpmnProcessEntity;
use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\Services\Record;
use LogicException;

class BpmnProcess extends Record
{
    protected $readOnlyAttributeList = [
        'flowchartData',
        'createdEntitiesData',
        'flowchartElementsDataHash',
        'status',
        'variables',
        'endedAt',
        'parentProcessId',
        'workflowId',
    ];

    protected $linkParams = [
        'flowNodes' => [
            'skipAcl' => true,
        ],
    ];

    public function beforeUpdateEntity(Entity $entity, $data)
    {
        $entity->clear('flowchartId');
        $entity->clear('targetId');
        $entity->clear('targetType');
        $entity->clear('startElementId');
    }

    private function createBpmnManager(): BpmnManager
    {
        return $this->injectableFactory->create(BpmnManager::class);
    }

    public function reactivateProcess(string $id): void
    {
        /** @var ?BpmnProcessEntity $process */
        $process = $this->entityManager->getEntityById(BpmnProcessEntity::ENTITY_TYPE, $id);

        if (!$process) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($process)) {
            throw new Forbidden();
        }

        if (
            !in_array($process->getStatus(), [
                BpmnProcessEntity::STATUS_ENDED,
                BpmnProcessEntity::STATUS_STOPPED,
                BpmnProcessEntity::STATUS_INTERRUPTED,
            ])
        ) {
            throw new Error("BPM: Can't reactivate not ended process.");
        }

        $targetType = $process->getTargetType();
        $targetId = $process->getTargetId();

        if (!$targetType || !$targetId) {
            throw new Error("BPM: Can't reactivate process, no target.");
        }

        $target = $this->entityManager->getEntityById($targetType, $targetId);

        if (!$target) {
            throw new Error("BPM: Can't reactivate process, target not found.");
        }

        if (!$process->isSubProcess()) {
            $anotherProcess = $this->entityManager
                ->getRDBRepository(BpmnProcessEntity::ENTITY_TYPE)
                ->where([
                    'targetId' => $target->getId(),
                    'targetType' => $target->getEntityType(),
                    'status' => [
                        BpmnProcessEntity::STATUS_STARTED,
                        BpmnProcessEntity::STATUS_PAUSED,
                    ],
                    'flowchartId' => $process->getFlowchartId(),
                ])
                ->findOne();

            if ($anotherProcess) {
                throw new Error(
                    "Process for flowchart " . $process->getFlowchartId() .
                    " can't be run because process is already running.");
            }
        }

        $manager = $this->createBpmnManager();

        if ($process->hasParentProcess()) {
            /** @var string $parentProcessId */
            $parentProcessId = $process->getParentProcessId();
            /** @var string $parentProcessFlowNodeId */
            $parentProcessFlowNodeId = $process->getParentProcessFlowNodeId();

            /** @var ?BpmnProcessEntity $parentProcess */
            $parentProcess = $this->entityManager
                ->getEntityById(BpmnProcessEntity::ENTITY_TYPE, $parentProcessId);

            /** @var ?BpmnFlowNodeEntity $parentProcessFlowNode */
            $parentProcessFlowNode = $this->entityManager
                ->getEntityById(BpmnFlowNodeEntity::ENTITY_TYPE, $parentProcessFlowNodeId);

            if (!$parentProcess) {
                throw new Error("BPM: Can't reactivate sub-process, parent process not found.");
            }

            if (!$parentProcessFlowNode) {
                throw new Error("BPM: Can't reactivate sub-process, parent process flow node not found.");
            }

            if ($parentProcess->getStatus() !== BpmnProcessEntity::STATUS_STARTED) {
                throw new Error(
                    "BPM: Can't reactivate sub-process, parent process is not started. Reactivate parent process.");
            }

            $parentTargetType = $parentProcess->getTargetType();
            $parentTargetId = $parentProcess->getTargetId();

            if (!$parentTargetType || !$parentTargetId) {
                throw new Error("BPM: Can't reactivate sub-process, no parent target.");
            }

            $parentTarget = $this->entityManager->getEntityById($parentTargetType, $parentTargetId);

            if (!$parentTarget) {
                throw new Error("BPM: Can't reactivate sub-process, parent target not found.");
            }

            $parentElement = $manager->getFlowNodeImplementation($parentTarget, $parentProcessFlowNode, $parentProcess);

            if (!$parentElement instanceof Activity) {
                throw new LogicException("BPM: Can't reactivate sub-process, bad element.");
            }

            $parentProcessFlowNode->set('status', BpmnFlowNodeEntity::STATUS_IN_PROCESS);

            $this->entityManager->saveEntity($parentProcessFlowNode);

            $parentElement->prepareBoundary();
        }

        $process->set('status', BpmnProcessEntity::STATUS_STARTED);
        $process->set('endedAt', null);

        $this->entityManager->saveEntity($process);

        $manager->prepareEventSubProcesses($target, $process);
    }

    public function stopProcess(string $id): void
    {
        /** @var ?BpmnProcessEntity $process */
        $process = $this->entityManager->getEntityById(BpmnProcessEntity::ENTITY_TYPE, $id);

        if (!$process) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($process)) {
            throw new Forbidden();
        }

        if (
            !in_array(
                $process->getStatus(),
                [
                    BpmnProcessEntity::STATUS_STARTED,
                    BpmnProcessEntity::STATUS_PAUSED,
                ])
        ) {
            throw new Error("BPM: Can't stop not started process.");
        }

        $process->set('status', BpmnProcessEntity::STATUS_STOPPED);

        $this->entityManager->saveEntity($process);
    }

    public function startFlowFromElement(string $processId, string $elementId): void
    {
        /** @var ?BpmnProcessEntity $process */
        $process = $this->entityManager->getEntityById(BpmnProcessEntity::ENTITY_TYPE, $processId);

        if (!$process) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($process)) {
            throw new Forbidden();
        }

        if ($process->getStatus() !== BpmnProcessEntity::STATUS_STARTED) {
            throw new Error("BPM: Can't start flow for not started process.");
        }

        $target = $this->entityManager->getEntityById($process->getTargetType(), $process->getTargetId());

        if (!$target) {
            throw new Error("BPM: No target for process to start flow node.");
        }

        $manager = $this->createBpmnManager();

        $manager->processFlow($target, $process, $elementId);
    }

    public function rejectFlowNode(string $flowNodeId): void
    {
        /** @var ?BpmnFlowNodeEntity $flowNode */
        $flowNode = $this->entityManager->getEntityById(BpmnFlowNodeEntity::ENTITY_TYPE, $flowNodeId);

        if (!$flowNode) {
            throw new NotFound();
        }

        $processId = $flowNode->getProcessId();
        $process = $this->entityManager->getEntityById(BpmnProcessEntity::ENTITY_TYPE, $processId);

        if (!$process) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($process)) {
            throw new Forbidden();
        }

        $status = $flowNode->getStatus();

        if (
            in_array($status, [
                BpmnFlowNodeEntity::STATUS_PROCESSED,
                BpmnFlowNodeEntity::STATUS_INTERRUPTED,
                BpmnFlowNodeEntity::STATUS_REJECTED,
                BpmnFlowNodeEntity::STATUS_FAILED,
            ])
        ) {
            throw new Forbidden();
        }

        $manager = $this->createBpmnManager();

        if ($flowNode->getStatus() === BpmnFlowNodeEntity::STATUS_IN_PROCESS) {
            $flowNode->set('status', BpmnFlowNodeEntity::STATUS_INTERRUPTED);

            $this->entityManager->saveEntity($flowNode);

            if (
                in_array($flowNode->getElementType(),
                    [
                        'subProcess',
                        'eventSubProcess',
                        'callActivity'
                    ])
            ) {
                $subProcess = $this->entityManager
                    ->getRDBRepository(BpmnProcessEntity::ENTITY_TYPE)
                    ->where([
                        'parentProcessFlowNodeId' => $flowNode->getId(),
                    ])
                    ->findOne();

                if ($subProcess) {
                    $manager->interruptProcess($subProcess);
                }
            }
        }
        else {
            $flowNode->set('status', BpmnFlowNodeEntity::STATUS_REJECTED);

            $this->entityManager->saveEntity($flowNode);
        }
    }

    public function cleanup(string $id): void
    {
        $flowNodeList = $this->entityManager
            ->getRDBRepository(BpmnFlowNodeEntity::ENTITY_TYPE)
            ->where(['processId' => $id])
            ->find();

        foreach ($flowNodeList as $flowNode) {
            $this->entityManager->removeEntity($flowNode);

            $this->entityManager
                ->getRDBRepository(BpmnFlowNodeEntity::ENTITY_TYPE)
                ->deleteFromDb($flowNode->getId());
        }
    }

    public function loadAdditionalFields(Entity $entity)
    {
        parent::loadAdditionalFields($entity);

        $flowchartData = $entity->get('flowchartData') ?? (object) [];

        $list = $flowchartData->list ?? [];

        $flowNodeList = $this->entityManager
            ->getRDBRepository(BpmnFlowNodeEntity::ENTITY_TYPE)
            ->where(['processId' => $entity->getId()])
            ->order('number', true)
            ->limit(0, 400)
            ->find();

        foreach ($list as $item) {
            $this->loadOutlineData($item, $flowNodeList);
        }

        $entity->set('flowchartData', $flowchartData);
    }

    /**
     * @param object $item
     * @param Collection<BpmnFlowNodeEntity>$flowNodeList
     */
    private function loadOutlineData($item, $flowNodeList)
    {
        $type = $item->type ?? null;
        $id = $item->id ?? null;

        if (!$type || !$id) {
            return;
        }

        if ($type === 'flow') {
            return;
        }

        if ($type === 'eventSubProcess' || $type === 'subProcess') {
            $list = $item->dataList ?? [];

            foreach ($flowNodeList as $flowNode) {
                if ($flowNode->get('elementId') == $id) {
                    $subProcessId = $flowNode->getDataItemValue('subProcessId');

                    $spFlowNodeList = $this->entityManager
                        ->getRDBRepository(BpmnFlowNodeEntity::ENTITY_TYPE)
                        ->where(['processId' => $subProcessId])
                        ->order('number', true)
                        ->limit(0, 400)
                        ->find();

                    foreach ($list as $spItem) {
                        $this->loadOutlineData($spItem, $spFlowNodeList);
                    }

                    break;
                }
            }
        }

        foreach ($flowNodeList as $flowNode) {
            $status = $flowNode->get('status');

            if ($flowNode->get('elementId') == $id) {
                if ($status === BpmnFlowNodeEntity::STATUS_PROCESSED) {
                    $item->outline = 3;

                    return;
                }
            }
        }

        foreach ($flowNodeList as $flowNode) {
            $status = $flowNode->get('status');

            if ($flowNode->get('elementId') == $id) {
                if ($status === BpmnFlowNodeEntity::STATUS_IN_PROCESS) {
                    $item->outline = 1;

                    return;
                }
            }
        }

        foreach ($flowNodeList as $flowNode) {
            $status = $flowNode->get('status');

            if ($flowNode->get('elementId') == $id) {
                if ($status === BpmnFlowNodeEntity::STATUS_PENDING) {
                    $item->outline = 2;

                    return;
                }
            }
        }

        foreach ($flowNodeList as $flowNode) {
            $status = $flowNode->get('status');

            if ($flowNode->get('elementId') == $id) {
                if (
                    $status === BpmnFlowNodeEntity::STATUS_FAILED ||
                    $status === BpmnFlowNodeEntity::STATUS_INTERRUPTED
                ) {
                    $item->outline = 4;

                    return;
                }
            }
        }
    }
}
