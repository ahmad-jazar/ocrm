<?php

namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use Espo\Core\Exceptions\Error;
use Espo\Core\Container;
use Espo\Core\Utils\Config;
use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use stdClass;

abstract class Base
{
    private ?string $workflowId = null;
    protected ?Entity $entity = null;
    protected ?stdClass $condition = null;
    protected ?stdClass $createdEntitiesData = null;

    protected Container $container;
    private EntityManager $entityManager;

    public function __construct(Container $container)
    {
        $this->container = $container;

        /** @var EntityManager $entityManager */
        $entityManager = $container->get('entityManager');;

        $this->entityManager = $entityManager;
    }

    protected function compareComplex(Entity $entity, stdClass $condition): bool
    {
        return false;
    }

    /**
     * @param mixed $fieldValue
     */
    abstract protected function compare($fieldValue): bool;

    protected function getContainer(): Container
    {
        return $this->container;
    }

    protected function getConfig(): Config
    {
        /** @var Config */
        return $this->container->get('config');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function setWorkflowId(?string $workflowId): void
    {
        $this->workflowId = $workflowId;
    }

    protected function getWorkflowId(): ?string
    {
        return $this->workflowId;
    }

    protected function getEntity(): Entity
    {
        return $this->entity;
    }

    protected function getCondition(): stdClass
    {
        return $this->condition;
    }

    public function process(Entity $entity, stdClass $condition, ?stdClass $createdEntitiesData = null): bool
    {
        $this->entity = $entity;
        $this->condition = $condition;
        $this->createdEntitiesData = $createdEntitiesData;

        if (!empty($condition->fieldValueMap)) {
            return $this->compareComplex($entity, $condition);
        }
        else {
            $fieldName = $this->getFieldName();

            if (isset($fieldName)) {
                return $this->compare($this->getFieldValue());
            }
        }

        return false;
    }

    /**
     * Get field name based on fieldToCompare value.
     *
     * @return string
     */
    protected function getFieldName()
    {
        $condition = $this->getCondition();

        if (isset($condition->fieldToCompare)) {
            $entity = $this->getEntity();
            $fieldName = $condition->fieldToCompare;

            $normalizeFieldName = Utils::normalizeFieldName($entity, $fieldName);
            if (is_array($normalizeFieldName)) { //if field is parent
                return reset($normalizeFieldName);
            }

            return $normalizeFieldName;
        }

        return null;
    }

    /**
     * @return ?string
     */
    protected function getAttributeName()
    {
        return $this->getFieldName();
    }

    /**
     * @return mixed
     */
    protected function getAttributeValue()
    {
        return $this->getFieldValue();
    }

    /**
     * Get value of fieldToCompare field.
     *
     * @todo Pass ReadLoadProcessor to load additional fields if asked for not set field.
     *   Only for BPM.
     *
     * @return mixed
     */
    protected function getFieldValue()
    {
        $entity = $this->getEntity();
        $condition = $this->getCondition();

        $fieldValue = Utils::getFieldValue(
            $entity,
            $condition->fieldToCompare,
            false,
            $this->getEntityManager(),
            $this->createdEntitiesData
        );

        if (!is_array($fieldValue)) {
            return $fieldValue;
        }

        return $fieldValue;
    }

    /**
     * Get value of subject field.
     *
     * @return mixed
     * @throws Error
     */
    protected function getSubjectValue()
    {
        $entity = $this->getEntity();
        $condition = $this->getCondition();

        switch ($condition->subjectType) {
            case 'value':
                $subjectValue = $condition->value;

                break;

            case 'field':
                $subjectValue = Utils::getFieldValue($entity, $condition->field);

                if (isset($condition->shiftDays)) {
                    $shiftUnits = $condition->shiftUnits ?? 'days';

                    $timezone = $this->getConfig()->get('timeZone');

                    return Utils::shiftDays($condition->shiftDays, $subjectValue, 'date', $shiftUnits, $timezone);
                }

                break;

            case 'today':
                $shiftUnits = $condition->shiftUnits ?? 'days';

                $timezone = $this->getConfig()->get('timeZone');

                return Utils::shiftDays($condition->shiftDays, null, 'date', $shiftUnits, $timezone);

            default:
                throw new Error(
                    'Workflow['.$this->getWorkflowId().']: Unknown object type [' . $condition->subjectType . '].');
        }

        return $subjectValue;
    }
}
