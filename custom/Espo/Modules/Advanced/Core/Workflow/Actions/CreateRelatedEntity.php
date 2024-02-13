<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use Espo\Core\Utils\Util;
use stdClass;

class CreateRelatedEntity extends CreateEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        $entityManager = $this->getEntityManager();

        $linkEntityType = $this->getLinkEntityType($entity, $actionData->link);

        if (!isset($linkEntityType)) {
            $GLOBALS['log']->error(
                'Workflow\Actions\\'.$actionData->type.': ' .
                'Cannot find an entity type of the link ['.$actionData->link.'] in the entity ' .
                '[' . $entity->getEntityType() . '].');

            return false;
        }

        $newEntity = $entityManager->getEntity($linkEntityType);

        $data = $this->getDataToFill($newEntity, $actionData->fields);

        $newEntity->set($data);

        $link = $actionData->link;

        $linkType = $entity->getRelationParam($link, 'type');

        $isRelated = false;

        if ($foreignLink = $entity->getRelationParam($link, 'foreign')) {
            $foreignRelationType = $newEntity->getRelationType($foreignLink);

            if (in_array($foreignRelationType, [Entity::BELONGS_TO, Entity::BELONGS_TO_PARENT])) {
                if ($foreignRelationType === Entity::BELONGS_TO) {
                    $newEntity->set($foreignLink. 'Id', $entity->getId());

                    $isRelated = true;
                }
                else if ($foreignRelationType === 'belongsToParent') {
                    $newEntity->set($foreignLink. 'Id', $entity->getId());
                    $newEntity->set($foreignLink. 'Type', $entity->getEntityType());

                    $isRelated = true;
                }
            }
        }

        $newEntity->set('id', $this->generateId());

        if (!empty($actionData->formula)) {
            $this->getFormulaManager()->run($actionData->formula, $newEntity, $this->getFormulaVariables());
        }

        $entityManager->saveEntity($newEntity, [
            'workflowId' => $this->getWorkflowId(),
            'createdById' => $newEntity->get('createdById') ?? 'system',
        ]);

        $newEntityId = $newEntity->getId();

        if (!empty($newEntityId)) {
            $newEntity = $entityManager->getEntityById($newEntity->getEntityType(), $newEntityId);

            if (!$isRelated && $linkType === Entity::BELONGS_TO) {
                $entity->set($link . 'Id', $newEntity->getId());
                $entity->set($link . 'Name', $newEntity->get('name'));

                $this->getEntityManager()->saveEntity($entity, [
                    'skipWorkflow' => true,
                    'noStream' => true,
                    'noNotifications' => true,
                    'skipModifiedBy' => true,
                    'skipCreatedBy' => true,
                    'skipHooks' => true,
                    'silent' => true,
                ]);

                $isRelated = true;
            }

            if (!$isRelated) {
                $entityManager
                    ->getRDBRepository($entity->getEntityType())
                    ->getRelation($entity, $actionData->link)
                    ->relate($newEntity);
            }

            if ($this->createdEntitiesData && !empty($actionData->elementId) && !empty($actionData->id)) {
                $this->createdEntitiesDataIsChanged = true;

                $alias = $actionData->elementId . '_' . $actionData->id;

                $this->createdEntitiesData->$alias = (object) [
                    'entityType' => $newEntity->getEntityType(),
                    'entityId' => $newEntity->get('id')
                ];
            }
        }

        return !empty($newEntityId);
    }

    private function generateId(): string
    {
        if (
            interface_exists('Espo\\Core\\Utils\\Id\\RecordIdGenerator') &&
            method_exists($this->injectableFactory, 'createResolved')
        ) {
            return $this->injectableFactory->createResolved('Espo\\Core\\Utils\\Id\\RecordIdGenerator')
                ->generate();
        }

        return Util::generateId();
    }

    /**
     * Get an Entity name of a link.
     *
     * @return ?string
     */
    protected function getLinkEntityType(Entity $entity, string $linkName)
    {
        if ($entity->hasRelation($linkName)) {
            return $entity->getRelationParam($linkName, 'entity');
        }

        return null;
    }
}
