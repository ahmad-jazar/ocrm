<?php

namespace Espo\Modules\Advanced\Core\Workflow;

use Espo\Core\Exceptions\Error;

use Espo\Core\Container;
use Espo\Core\Utils\Log;
use Espo\ORM\Entity;

abstract class BaseManager
{
    protected string $dirName = 'Dummy';

    private Container $container;
    private ?string $processId;
    /** @var ?array<string, Entity> */
    private ?array $entityMap;
    /** @var array<string, string> */
    private ?array $workflowIdList;
    /** @var array<string, class-string> */
    private array $actionClassNameMap = [];
    protected array $requiredOptions = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    public function setInitData($workflowId, Entity $entity): void
    {
        $this->processId = $workflowId . '-'. $entity->getId();

        $this->workflowIdList[$this->processId] = $workflowId;
        $this->entityMap[$this->processId] = $entity;
    }

    protected function getProcessId(): ?string
    {
        if (empty($this->processId)) {
            throw new Error('Workflow['.__CLASS__.'], getProcessId(): Empty processId.');
        }

        return $this->processId;
    }

    protected function getWorkflowId(?string $processId = null): string
    {
        if (!isset($processId)) {
            $processId = $this->getProcessId();
        }

        if (empty($this->workflowIdList[$processId])) {
            throw new Error('Workflow['.__CLASS__.'], getWorkflowId(): Empty workflowId.');
        }

        return $this->workflowIdList[$processId];
    }

    protected function getEntity(?string $processId = null): Entity
    {
        if (!isset($processId)) {
            $processId = $this->getProcessId();
        }

        if (empty($this->entityMap[$processId])) {
            throw new Error('Workflow[' . __CLASS__ . '], getEntity(): Empty Entity object.');
        }

        return $this->entityMap[$processId];
    }

    private function getClassName(string $name): string
    {
        if (!isset($this->actionClassNameMap[$name])) {
            $className = 'Espo\Custom\Modules\Advanced\Core\Workflow\\' . ucfirst($this->dirName) . '\\' . $name;

            if (!class_exists($className)) {
                $className .=  'Type';

                if (!class_exists($className)) {
                    $className = 'Espo\Modules\Advanced\Core\Workflow\\' . ucfirst($this->dirName) . '\\' . $name;

                    if (!class_exists($className)) {
                        $className .=  'Type';

                        if (!class_exists($className)) {
                            throw new Error('Class ['.$className.'] does not exist.');
                        }
                    }
                }
            }

            $this->actionClassNameMap[$name] = $className;
        }

        return $this->actionClassNameMap[$name];
    }

    /**
     * @return Actions\Base|Conditions\Base
     */
    protected function getImpl(string $name, ?string $processId = null): object
    {
        $name = ucfirst($name);

        $name = str_replace("\\", "", $name);

        if (!isset($processId)) {
            $processId = $this->getProcessId();
        }

        $workflowId = $this->getWorkflowId($processId);

        $className = $this->getClassName($name);

        /** @var Actions\Base|Conditions\Base $obj */
        $obj = new $className($this->getContainer());

        $obj->setWorkflowId($workflowId);

        return $obj;
    }

    protected function validate(object $options): bool
    {
        foreach ($this->requiredOptions as $optionName) {
            if (!property_exists($options, $optionName)) {
                return false;
            }
        }

        return true;
    }

    protected function getLog(): Log
    {
        /** @var Log */
        return $this->getContainer()->get('log');
    }
}
