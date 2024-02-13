<?php

namespace Espo\Modules\Advanced\Core;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Entities\User;
use Espo\Modules\Advanced\Core\Workflow\ConditionManager;
use Espo\Modules\Advanced\Core\Workflow\ActionManager;
use Espo\Modules\Advanced\Entities\Workflow;
use Espo\ORM\Entity;
use Espo\Core\Container;
use Espo\ORM\EntityManager;

use Exception;

class WorkflowManager
{
    private Container $container;
    private ConditionManager $conditionManager;
    private ActionManager $actionManager;
    private ?array $data = null;

    private string $cacheFile = 'data/cache/advanced/workflows.php';

    private array $cacheFields = [
        'conditionsAll',
        'conditionsAny',
        'conditionsFormula',
        'actions',
    ];

    public const AFTER_RECORD_SAVED = 'afterRecordSaved';
    public const AFTER_RECORD_CREATED = 'afterRecordCreated';
    public const AFTER_RECORD_UPDATED = 'afterRecordUpdated';

    /** @var string[] */
    private array $entityListToIgnore;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->conditionManager = new ConditionManager($this->container);
        $this->actionManager = new ActionManager($this->container);

        $this->entityListToIgnore = $this->container
            ->get('metadata')
            ->get('entityDefs.Workflow.entityListToIgnore') ?? [];
    }

    private function getConfig(): Config
    {
        /** @var Config */
        return $this->container->get('config');
    }

    private function getUser(): User
    {
        /** @var User */
        return $this->container->get('user');
    }

    private function getEntityManager(): EntityManager
    {
        /** @var EntityManager */
        return $this->container->get('entityManager');
    }

    private function getFileManager(): FileManager
    {
        /** @var FileManager */
        return $this->container->get('fileManager');
    }

    private function getData(string $entityType, string $trigger) : ?array
    {
        if (!isset($this->data)) {
            $this->loadWorkflows();
        }

        if (!isset($this->data[$trigger])) {
            return null;
        }

        if (!isset($this->data[$trigger][$entityType])) {
            return null;
        }

        $result = $this->data[$trigger][$entityType];

        if ($result && !is_array($result)) {
            $GLOBALS['log']->error("WorkflowManager: Bad data for workflow.");

            return null;
        }

        return $result;
    }

    public function process(Entity $entity, string $trigger, array $options = [])
    {
        $entityType = $entity->getEntityType();

        if (in_array($entityType, $this->entityListToIgnore)) return;

        $data = $this->getData($entityType, $trigger);

        if (!$data) {
            return;
        }

        $GLOBALS['log']
            ->debug('WorkflowManager: Start workflow ['.$trigger.'] for entity ['.$entityType.', '.
                $entity->getId().'].');

        $conditionManager = $this->conditionManager;
        $actionManager = $this->actionManager;

        foreach ($data as $workflowId => $workflowData) {
            $GLOBALS['log']->debug('WorkflowManager: Start workflow rule ['.$workflowId.'].');

            if ($workflowData['portalOnly']) {
                if (!$this->getUser()->getPortalId()) {
                    continue;
                }

                if (!empty($workflowData['portalId'])) {
                    if ($this->getUser()->get('portalId') !== $workflowData['portalId']) {
                        continue;
                    }
                }
            }

            if (!empty($options['workflowId']) && $options['workflowId'] === $workflowId) {
                continue;
            }

            $conditionManager->setInitData($workflowId, $entity);

            $result = true;

            if (isset($workflowData['conditionsAll'])) {
                $result &= $conditionManager->checkConditionsAll($workflowData['conditionsAll']);
            }

            if (isset($workflowData['conditionsAny'])) {
                $result &= $conditionManager->checkConditionsAny($workflowData['conditionsAny']);
            }

            if (isset($workflowData['conditionsFormula']) && !empty($workflowData['conditionsFormula'])) {
                $result &= $conditionManager->checkConditionsFormula($workflowData['conditionsFormula']);
            }

            $GLOBALS['log']->debug(
                'WorkflowManager: Condition result ['.(bool) $result.'] for workflow rule ['.$workflowId.'].'
            );

            if ($result) {
                $workflowLogRecord = $this->getEntityManager()->getEntity('WorkflowLogRecord');

                $workflowLogRecord->set([
                    'workflowId' => $workflowId,
                    'targetId' => $entity->getId(),
                    'targetType' => $entity->getEntityType(),
                ]);

                $this->getEntityManager()->saveEntity($workflowLogRecord);
            }

            if ($result && isset($workflowData['actions'])) {
                $GLOBALS['log']->debug('WorkflowManager: Start running Actions for workflow rule ['.$workflowId.'].');

                $actionManager->setInitData($workflowId, $entity);

                try {
                    $actionManager->runActions($workflowData['actions']);
                }
                catch (Exception $e) {
                    $GLOBALS['log']->notice(
                        'Workflow: failed action execution for workflow [' . $workflowId . ']. Details: '.
                        $e->getMessage());
                }

                $GLOBALS['log']->debug('WorkflowManager: End running Actions for workflow rule ['.$workflowId.'].');
            }

            $GLOBALS['log']->debug('WorkflowManager: End workflow rule ['.$workflowId.'].');
        }

        $GLOBALS['log']
            ->debug('WorkflowManager: End workflow ['.$trigger.'] for Entity ['.$entityType.', ' .
                $entity->get('id').'].');
    }

    public function checkConditions(Workflow $workflow, Entity $entity): bool
    {
        $result = true;

        $conditionsAll = $workflow->get('conditionsAll');
        $conditionsAny = $workflow->get('conditionsAny');
        $conditionsFormula = $workflow->get('conditionsFormula');

        $conditionManager = $this->conditionManager;

        $conditionManager->setInitData($workflow->getId(), $entity);

        if (isset($conditionsAll)) {
            $result &= $conditionManager->checkConditionsAll($conditionsAll);
        }
        if (isset($conditionsAny)) {
            $result &= $conditionManager->checkConditionsAny($conditionsAny);
        }

        if ($conditionsFormula && $conditionsFormula !== '') {
            $result &= $conditionManager->checkConditionsFormula($conditionsFormula);
        }

        return $result;
    }

    public function runActions(Workflow $workflow, Entity $entity): void
    {
        $actions = $workflow->get('actions');

        $actionManager = $this->actionManager;

        $actionManager->setInitData($workflow->getId(), $entity);
        $actionManager->runActions($actions);
    }

    public function loadWorkflows(bool $reload = false): void
    {
        if (!$reload && $this->getConfig()->get('useCache') && file_exists($this->cacheFile)) {
            $this->data = $this->getFileManager()->getPhpContents($this->cacheFile);

            return;
        }

        $this->data = $this->getWorkflowData();

        if ($this->getConfig()->get('useCache')) {
            $this->getFileManager()->putPhpContents($this->cacheFile, $this->data, true);
        }
    }

    /**
     * Get all workflows from database and save into cache.
     *
     * @return array<string, mixed>
     */
    private function getWorkflowData(): array
    {
        $data = [];

        $workflowList = $this->getEntityManager()
            ->getRDBRepository(Workflow::ENTITY_TYPE)
            ->where(['isActive' => true])
            ->find();

        foreach ($workflowList as $workflow) {
            $rowData = [];

            foreach ($this->cacheFields as $fieldName) {
                if ($workflow->get($fieldName) === null) {
                    continue;
                }

                $fieldValue = $workflow->get($fieldName);

                if (!empty($fieldValue)) {
                    $rowData[$fieldName] = $fieldValue;
                }
            }

            $rowData['portalOnly'] = (bool) $workflow->get('portalOnly');

            if ($rowData['portalOnly']) {
                $rowData['portalId'] = $workflow->get('portalId');
            }

            $entityType = $workflow->getTargetEntityType();

            $id = $workflow->getId();

            if ($workflow->getType() === 'signal') {
                $trigger = '$' . $workflow->get('signalName');
            }
            else {
                $trigger = $workflow->getType();
            }

            $data[$trigger][$entityType][$id] = $rowData;
        }

        return $data;
    }
}
