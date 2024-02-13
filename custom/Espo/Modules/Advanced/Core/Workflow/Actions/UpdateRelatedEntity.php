<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use Espo\ORM\EntityCollection;
use Exception;
use stdClass;

/** @noinspection PhpUnused */
class UpdateRelatedEntity extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        $link = $actionData->link;

        $relatedEntities = $this->getRelatedEntities($entity, $link);

        foreach ($relatedEntities as $relatedEntity) {
            if (!($relatedEntity instanceof Entity)) {
                continue;
            }

            $update = true;

            if (
                $entity->hasRelation($link) &&
                $entity->getRelationType($link) === 'belongsToParent' &&
                !empty($actionData->parentEntityType)
            ) {
                if ($actionData->parentEntityType !== $relatedEntity->getEntityType()) {
                    $update = false;
                }
            }

            if (!$update) {
                continue;
            }

            $data = $this->getDataToFill($relatedEntity, $actionData->fields);

            $relatedEntity->set($data);

            if (!empty($actionData->formula)) {
                $clonedVariables = clone $this->getFormulaVariables();
                $this->getFormulaManager()->run($actionData->formula, $relatedEntity, $clonedVariables);
            }

            if (!$relatedEntity->has('modifiedById')) {
                $relatedEntity->set('modifiedByName', 'System');
            }

            $this->getEntityManager()->saveEntity($relatedEntity, [
                'modifiedById' => 'system',
                'workflowId' => $this->getWorkflowId(),
            ]);
        }

        return true;
    }

    /**
     * Get Related Entity,
     *
     * @return EntityCollection|iterable<Entity>
     */
    protected function getRelatedEntities(Entity $entity, string $link)
    {
        if (empty($link) || !$entity->hasRelation($link)) {
            return [];
        }

        switch ($entity->getRelationType($link)) {
            case Entity::BELONGS_TO_PARENT:
                $parentType = $entity->get($link . 'Type');
                $parentId = $entity->get($link . 'Id');

                if (!$parentType || !$parentId) {
                    return [];
                }

                try {
                    $relatedEntity = $this->getEntityManager()->getEntityById($parentType, $parentId);
                }
                catch (Exception $e) {
                    $GLOBALS['log']->info(
                        'Workflow[UpdateRelatedEntity]: Cannot getRelatedEntities(), error: '. $e->getMessage());

                    return [];
                }

                $fetched = $this->getEntityManager()
                    ->getCollectionFactory()
                    ->create($entity->getEntityType(), [$relatedEntity]);

                break;

            case Entity::HAS_MANY:
            case Entity::HAS_CHILDREN:
                $fetched = $this->getEntityManager()
                    ->getRDBRepository($entity->getEntityType())
                    ->getRelation($entity, $link)
                    ->find();

                break;

            default:
                try {
                    $fetched = $this->getEntityManager()
                        ->getRDBRepository($entity->getEntityType())
                        ->getRelation($entity, $link)
                        ->findOne();
                }
                catch (Exception $e) {
                    $GLOBALS['log']->info(
                        'Workflow[UpdateRelatedEntity]: Cannot getRelatedEntities(), error: '. $e->getMessage());

                    return [];
                }

                break;
        }

        if ($fetched instanceof Entity) {
            return $this->getEntityManager()
                ->getCollectionFactory()
                ->create($entity->getEntityType(), [$fetched]);
        }

        return $fetched ?? [];
    }
}
