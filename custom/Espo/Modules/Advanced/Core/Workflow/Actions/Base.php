<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\InjectableFactory;
use Espo\Core\ServiceFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Modules\Advanced\Core\Workflow\EntityHelper;
use Espo\Modules\Advanced\Core\Workflow\Helper;
use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\Modules\Advanced\Entities\BpmnProcess;
use Espo\ORM\Entity;
use Espo\Core\Container;
use Espo\ORM\EntityManager;

use stdClass;

abstract class Base
{
    private Container $container;
    protected EntityManager $entityManager;
    protected InjectableFactory $injectableFactory;

    private ?string $workflowId = null;
    protected ?Entity $entity = null;
    protected ?stdClass $action = null;
    protected ?stdClass $createdEntitiesData = null;
    protected bool $createdEntitiesDataIsChanged = false;
    protected ?stdClass $variables = null;
    protected ?stdClass $preparedVariables = null;
    protected ?BpmnProcess $bpmnProcess = null;

    public function __construct(Container $container)
    {
        $this->container = $container;

        /** @var EntityManager $entityManager */
        $entityManager = $container->get('entityManager');
        /** @var InjectableFactory $injectableFactory */
        $injectableFactory = $container->get('injectableFactory');

        $this->entityManager = $entityManager;
        $this->injectableFactory = $injectableFactory;
    }

    abstract protected function run(Entity $entity, stdClass $actionData): bool;

    public function process(
        Entity $entity,
        stdClass $actionData,
        ?stdClass $createdEntitiesData = null,
        ?stdClass $variables = null,
        ?BpmnProcess $bpmnProcess = null
    ) {
        $this->entity = $entity;
        $this->action = $actionData;
        $this->createdEntitiesData = $createdEntitiesData;
        $this->variables = $variables;
        $this->bpmnProcess = $bpmnProcess;

        if (!property_exists($actionData, 'cid')) {
            $actionData->cid = 0;
        }

        $GLOBALS['log']->debug(
            'Workflow\Actions: Start ['.$actionData->type.'] with cid ['.$actionData->cid.'] for entity ['.
            $entity->getEntityType().', '.$entity->get('id').'].'
        );

        $result = $this->run($entity, $actionData);

        $GLOBALS['log']->debug(
            'Workflow\Actions: End ['.$actionData->type.'] with cid ['.$actionData->cid.'] for entity ['.
            $entity->getEntityType().', '.$entity->get('id').'].'
        );

        if (!$result) {
            $GLOBALS['log']->debug(
                'Workflow['.$this->getWorkflowId().']: Action failed [' . $actionData->type .
                '] with cid [' . $actionData->cid . '].'
            );
        }
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    public function isCreatedEntitiesDataChanged(): bool
    {
        return $this->createdEntitiesDataIsChanged;
    }

    public function getCreatedEntitiesData(): stdClass
    {
        return $this->createdEntitiesData;
    }

    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    protected function getWorkflowId(): ?string
    {
        return $this->workflowId;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getServiceFactory(): ServiceFactory
    {
        /** @var ServiceFactory */
        return $this->container->get('serviceFactory');
    }

    protected function getMetadata(): Metadata
    {
        /** @var Metadata */
        return $this->container->get('metadata');
    }

    protected function getConfig(): Config
    {
        /** @var Config */
        return $this->container->get('config');
    }

    protected function getFormulaManager(): FormulaManager
    {
        /** @var FormulaManager */
        return $this->container->get('formulaManager');
    }

    protected function getUser(): User
    {
        /** @var User */
        return $this->container->get('user');
    }

    protected function getEntity(): Entity
    {
        return $this->entity;
    }

    protected function getActionData(): stdClass
    {
        return $this->action;
    }

    protected function getHelper(): Helper
    {
        /** @var Helper */
        return $this->container->get('workflowHelper');
    }

    protected function getEntityHelper(): EntityHelper
    {
        /** @var EntityHelper */
        return $this->getHelper()->getEntityHelper();
    }

    protected function clearSystemVariables(stdClass $variables): void
    {
        unset($variables->__targetEntity);
        unset($variables->__processEntity);
        unset($variables->__createdEntitiesData);
    }

    /**
     * Return variables. Can be changed after action is processed.
     */
    public function getVariablesBack(): stdClass
    {
        $variables = clone $this->variables;

        $this->clearSystemVariables($variables);

        return $variables;
    }

    /**
     * Get variables for usage within an action.
     */
    public function getVariables(): stdClass
    {
        $variables = clone $this->getFormulaVariables();

        $this->clearSystemVariables($variables);

        return $variables;
    }

    protected function hasVariables(): bool
    {
        return !!$this->variables;
    }

    protected function updateVariables(stdClass $variables): void
    {
        if (!$this->hasVariables()) {
            return;
        }

        $variables = clone $variables;

        $this->clearSystemVariables($variables);

        foreach (get_object_vars($variables) as $k => $v) {
            $this->variables->$k = $v;
        }
    }

    protected function getFormulaVariables(): stdClass
    {
        if (!$this->preparedVariables) {
            $o = (object) [];

            $o->__targetEntity = $this->getEntity();

            if ($this->bpmnProcess) {
                $o->__processEntity = $this->bpmnProcess;
            }

            if ($this->createdEntitiesData) {
                $o->__createdEntitiesData = $this->createdEntitiesData;
            }

            if ($this->variables) {
                foreach ($this->variables as $k => $v) {
                    $o->$k = $v;
                }
            }

            $this->preparedVariables = $o;
        }

        return $this->preparedVariables;
    }



    /**
     * Get execute time defined in workflow
     */
    protected function getExecuteTime($data): string
    {
        $executeTime = date('Y-m-d H:i:s');

        if (!property_exists($data, 'execution')) {
            return $executeTime;
        }

        $execution = $data->execution;

        switch ($execution->type) {
            case 'immediately':
                return $executeTime;

            case 'later':
                if (!empty($execution->field)) {
                   $executeTime =  Utils::getFieldValue($this->getEntity(), $execution->field);
                }

                if (!empty($execution->shiftDays)) {
                    $shiftUnit = 'days';

                    if (!empty($execution->shiftUnit)) {
                        $shiftUnit = $execution->shiftUnit;
                    }

                    if (!in_array($shiftUnit, ['hours', 'minutes', 'days', 'months'])) {
                        $shiftUnit = 'days';
                    }

                    $executeTime = Utils::shiftDays($execution->shiftDays, $executeTime, 'datetime', $shiftUnit);
                }
                break;

            default:
                throw new Error('Workflow['.$this->getWorkflowId().
                    ']: Unknown execution type [' . $execution->type . ']');
        }

        return $executeTime;
    }

    protected function getCreatedEntity(string $target): ?Entity
    {
        if (strpos($target, 'created:') === 0) {
            $alias = substr($target, 8);
        } else {
            $alias = $target;
        }

        if (!$this->createdEntitiesData) {
            return null;
        }

        if (!property_exists($this->createdEntitiesData, $alias)) {
            return null;
        }

        if (
            empty($this->createdEntitiesData->$alias->entityId) ||
            empty($this->createdEntitiesData->$alias->entityType)
        ) {
            return null;
        }

        $entityType = $this->createdEntitiesData->$alias->entityType;
        $entityId = $this->createdEntitiesData->$alias->entityId;

        return $this->getEntityManager()->getEntityById($entityType, $entityId);
    }

    protected function getTargetEntityFromTargetItem(Entity $entity, string $target): ?Entity
    {
        if (!$target || $target === 'targetEntity') {
            return $this->getEntityManager()->getEntityById($entity->getEntityType(), $entity->get('id'));
        }

        if (strpos($target, 'created:') === 0) {
            return $this->getCreatedEntity($target);
        }

        if (strpos($target, 'link:') === 0) {
            $link = substr($target, 5);
            $linkList = explode('.', $link);

            $pointerEntity = $entity;
            $notFound = false;

            foreach ($linkList as $link) {
                $type = $this->getMetadata()
                    ->get(['entityDefs', $pointerEntity->getEntityType(), 'links', $link, 'type']);

                if (empty($type)) {
                    $notFound = true;

                    break;
                }

                $pointerEntity = $this->getEntityManager()
                    ->getRDBRepository($pointerEntity->getEntityType())
                    ->getRelation($pointerEntity, $link)
                    ->findOne();

                if (!$pointerEntity instanceof Entity) {
                    $notFound = true;

                    break;
                }
            }

            if ($notFound) {
                return null;
            }

            return $pointerEntity;
        }

        if ($target == 'currentUser') {
            return $this->getUser();
        }

        return null;
    }
}
