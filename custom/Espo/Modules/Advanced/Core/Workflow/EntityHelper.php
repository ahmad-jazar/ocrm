<?php

namespace Espo\Modules\Advanced\Core\Workflow;

use Espo\Core\Container;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use Exception;
use stdClass;

class EntityHelper
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    private function getEntityManager(): EntityManager
    {
        /** @var EntityManager */
        return $this->container->get('entityManager');
    }

    private function getRecordServiceContainer(): ServiceContainer
    {
        /** @var ServiceContainer */
        return $this->container->get('recordServiceContainer');
    }

    private function getMetadata(): Metadata
    {
        /** @var Metadata */
        return $this->container->get('metadata');
    }

    private function getFieldUtil(): FieldUtil
    {
        /** @var FieldUtil */
        return $this->container->get('fieldUtil');
    }

    private function normalizeRelatedFieldName(Entity $entity, $fieldName)
    {
        if ($entity->hasRelation($fieldName)) {
            $type = $entity->getRelationType($fieldName);

            $key = $entity->getRelationParam($fieldName, 'key');
            $foreignKey = $entity->getRelationParam($fieldName, 'foreignKey');

            switch ($type) {
                case Entity::HAS_CHILDREN:
                    if ($foreignKey) {
                        $fieldName = $foreignKey;
                    }

                    break;

                case Entity::BELONGS_TO:
                    if ($key) {
                        $fieldName = $key;
                    }

                    break;

                case Entity::HAS_MANY:
                case Entity::MANY_MANY:
                    $fieldName .= 'Ids';

                    break;
            }
        }

        return $fieldName;
    }

    /**
     * Get list of field based on its type.
     *
     * @param Entity $entity
     * @param string $fieldName
     * @return string[]
     */
    public function getActualFields(Entity $entity, string $fieldName): array
    {
        $entityType = $entity->getEntityType();

        $fieldUtil = $this->getFieldUtil();

        $list = [];
        $actualList = $fieldUtil->getActualAttributeList($entityType, $fieldName);
        $additionalList = [];

        if (method_exists($fieldUtil, 'getAdditionalActualAttributeList')) {
            $additionalList = $fieldUtil->getAdditionalActualAttributeList($entityType, $fieldName);
        }

        foreach ($actualList as $item) {
            if (!in_array($item, $additionalList)) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * Get field value for a field/related field. If this field has a relation, get value from the relation.
     */
    public function getFieldValues(
        Entity $fromEntity,
        Entity $toEntity,
        string $fromField,
        string $toField
    ): stdClass {

        $entity = $fromEntity;
        $fieldName = $fromField;

        $values = (object) [];

        if (strstr($fieldName, '.')) {
            [$relation, $foreignField] = explode('.', $fieldName);

            $relatedEntity = null;

            if (method_exists($this->getEntityManager(), 'getRDBRepository')) {
                $this->getEntityManager()
                    ->getRDBRepository($entity->getEntityType())
                    ->getRelation($entity, $relation)
                    ->findOne();
            }
            else {
                $relatedEntity = $entity->get($relation);
            }

            // If $entity is just created and doesn't have added relations.
            if (!isset($relatedEntity) && $entity->hasRelation($relation)) {
                $foreignEntityType = $entity->getRelationParam($relation, 'entity');

                $normalizedEntityFieldName = $this->normalizeRelatedFieldName($entity, $relation);

                if (
                    $foreignEntityType &&
                    $entity->hasAttribute($normalizedEntityFieldName) &&
                    $entity->get($normalizedEntityFieldName)
                ) {
                    $relatedEntity = $this->getEntityManager()
                        ->getEntityById($foreignEntityType, $entity->get($normalizedEntityFieldName));
                }
            }

            if ($relatedEntity instanceof Entity) {
                $entity = $relatedEntity;
                $fieldName = $foreignField;
            }
            else {
                $GLOBALS['log']->debug(
                    'Workflow [EntityHelper:getFieldValues]: The related field ['.$fieldName.'] of entity ['.
                    $entity->getEntityType().'] has unsupported or empty entity ['.
                    (isset($relatedEntity) ? get_class($relatedEntity) : var_export($relatedEntity, true)).'].');

                return (object) [];
            }
        }

        if ($entity->hasRelation($fieldName)) {
            if (!$entity->isNew()) {
                switch ($entity->getRelationType($fieldName)) { // ORM types
                    case Entity::MANY_MANY:
                    case Entity::HAS_CHILDREN:
                        try {
                            $entity->loadLinkMultipleField($fieldName);
                        }
                        catch (Exception $e) {}

                        break;

                    case Entity::BELONGS_TO:
                    case Entity::HAS_ONE:
                        try {
                            $entity->loadLinkField($fieldName);
                        }
                        catch (Exception $e) {}

                        break;
                }
            }
        }

        $fieldMap = $this->getRelevantAttributeMap($entity, $toEntity, $fieldName, $toField);

        $service = $this->getRecordServiceContainer()->get($entity->getEntityType());

        $toFieldName = null;

        foreach ($fieldMap as $fromFieldName => $toFieldName) {
            $getCopiedMethodName = 'getCopied' . ucfirst($fromFieldName);

            if (method_exists($entity, $getCopiedMethodName)) {
                $values->$toFieldName = $entity->$getCopiedMethodName();

                continue;
            }

            if ($service) {
                $getCopiedMethodName = 'getCopiedEntityAttribute' . ucfirst($fromFieldName);

                if (method_exists($service, $getCopiedMethodName)) {
                    $values->$toFieldName = $service->$getCopiedMethodName($entity);

                    continue;
                }
            }

            $values->$toFieldName = $entity->get($fromFieldName);
        }

        $toFieldType = $this->getMetadata()
            ->get(['entityDefs', $toEntity->getEntityType(), 'fields', $toField, 'type']);

        if (
            $toFieldType === 'personName' &&
            $toFieldName &&
            !empty($values->$toFieldName)
        ) {
            $fullNameValue = trim($values->$toFieldName);

            $firstNameAttribute = 'first' . ucfirst($toField);
            $lastNameAttribute = 'last' . ucfirst($toField);

            if (strpos($fullNameValue, ' ') === false) {
                $lastNameValue = $fullNameValue;
                $firstNameValue = null;
            }
            else {
                $index = strrpos($fullNameValue, ' ');
                $firstNameValue = substr($fullNameValue, 0, $index);
                $lastNameValue = substr($fullNameValue, $index + 1);
            }

            $values->$firstNameAttribute = $firstNameValue;
            $values->$lastNameAttribute = $lastNameValue;
        }

        /* correct field types. E.g. set teamsIds from defaultTeamId */
        if ($toEntity->hasRelation($toField)) {
            $normalizedFieldName = $this->normalizeRelatedFieldName($toEntity, $toField);

            if ($toEntity->getRelationType($toField) === Entity::MANY_MANY) {
                if (isset($values->$normalizedFieldName) && !is_array($values->$normalizedFieldName)) {
                    $values->$normalizedFieldName = (array)$values->$normalizedFieldName;
                }
            }
        }

        return $values;
    }

    /**
     * @todo: Rewrite.
     * @return array<string, mixed>
     */
    private function getRelevantAttributeMap(Entity $entity1, Entity $entity2, string $field1, string $field2): array
    {
        $attributeList1 = $this->getActualFields($entity1, $field1);
        $attributeList2 = $this->getActualFields($entity2, $field2);

        $fieldType1 = $this->getMetadata()->get(['entityDefs', $entity1->getEntityType(), 'fields', $field1, 'type']);
        $fieldType2 = $this->getMetadata()->get(['entityDefs', $entity2->getEntityType(), 'fields', $field2, 'type']);

        $ignoreActualAttributesOnValueCopyFieldList = $this->getMetadata()
            ->get(['entityDefs', 'Workflow', 'ignoreActualAttributesOnValueCopyFieldList'], []);

        if (in_array($fieldType1, $ignoreActualAttributesOnValueCopyFieldList)) {
            $attributeList1 = [$field1];
        }

        if (in_array($fieldType2, $ignoreActualAttributesOnValueCopyFieldList)) {
            $attributeList2 = [$field2];
        }

        $attributeMap = [];

        if (count($attributeList1) == count($attributeList2)) {
            if ($fieldType1 === 'datetimeOptional' && $fieldType2 === 'datetimeOptional') {
                if ($entity1->get($attributeList1[1])) {
                    $attributeMap[$attributeList1[1]] = $attributeList2[1];
                } else {
                    $attributeMap[$attributeList1[0]] = $attributeList2[0];
                }
            } else {
                foreach ($attributeList1 as $key => $name) {
                    $attributeMap[$name] = $attributeList2[$key];
                }
            }
        }
        else {
            if ($fieldType1 === 'datetimeOptional' || $fieldType2 === 'datetimeOptional') {
                if (count($attributeList2) > count($attributeList1)) {
                    if ($fieldType1 === 'date') {
                        $attributeMap[$attributeList1[0]] = $attributeList2[1];
                    } else {
                        $attributeMap[$attributeList1[0]] = $attributeList2[0];
                    }
                } else {
                    if ($fieldType2 === 'date') {
                        if ($entity1->get($attributeList1[1])) {
                            $attributeMap[$attributeList1[1]] = $attributeList2[0];
                        } else {
                            $attributeMap[$attributeList1[0]] = $attributeList2[0];
                        }
                    } else {
                        $attributeMap[$attributeList1[0]] = $attributeList2[0];
                    }
                }
            }
        }

        return $attributeMap;
    }
}
