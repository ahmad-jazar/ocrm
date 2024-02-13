<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;
use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\Tools\Stream\Service as StreamService;
use stdClass;

class MakeFollowed extends BaseEntity
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        if (empty($actionData->whatToFollow)) {
            $actionData->whatToFollow = 'targetEntity';
        }

        $target = $actionData->whatToFollow;

        $targetEntity = null;

        if ($target === 'targetEntity') {
            $targetEntity = $entity;
        }
        else if (strpos($target, 'created:') === 0) {
            $targetEntity = $this->getCreatedEntity($target);
        }
        else {
            $link = $target;

            if (strpos($target, 'link:') === 0) {
                $link = substr($target, 5);
            }

            $type = $this->getMetadata()
                ->get('entityDefs.' . $entity->getEntityType() . '.links.' . $link . '.type');

            if (empty($type)) {
                return false;
            }

            $idField = $link . 'Id';

            if ($type == Entity::BELONGS_TO) {
                if (!$entity->get($idField)) {
                    return false;
                }

                $foreignEntityType = $this->getMetadata()
                    ->get('entityDefs.' . $entity->getEntityType() . '.links.' . $link . '.entity');

                if (empty($foreignEntityType)) {
                    return false;
                }

                $targetEntity = $this->getEntityManager()
                    ->getEntityById($foreignEntityType, $entity->get($idField));
            }
            else if ($type === Entity::BELONGS_TO_PARENT) {
                $typeField = $link . 'Type';

                if (!$entity->get($idField)) {
                    return false;
                }

                if (!$entity->get($typeField)) {
                    return false;
                }

                $targetEntity = $this->getEntityManager()
                    ->getEntityById($entity->get($typeField), $entity->get($idField));
            }
        }

        if (!$targetEntity) {
            return false;
        }

        $userIdList = $this->getUserIdList($actionData);

        if (class_exists("Espo\\Tools\\Stream\\Service")) {
            /** @var StreamService $streamService */
            $streamService = $this->injectableFactory
                ->create("Espo\\Tools\\Stream\\Service");

            $streamService->followEntityMass($targetEntity, $userIdList);

            return true;
        }

        $streamService = $this->getServiceFactory()->create('Stream');
        $streamService->followEntityMass($targetEntity, $userIdList);

        return true;
    }

    /**
     * @return string[]
     */
    protected function getUserIdList(stdClass $actionData): array
    {
        $entity = $this->getEntity();

        if (!empty($actionData->recipient)) {
            $recipient = $actionData->recipient;
        }
        else {
            $recipient = 'specifiedUsers';
        }

        $userIdList = [];

        if (isset($actionData->userIdList) && is_array($actionData->userIdList)) {
            $userIdList = $actionData->userIdList;
        }

        $teamIdList = [];

        if (isset($actionData->specifiedTeamsIds) && is_array($actionData->specifiedTeamsIds)) {
            $teamIdList = $actionData->specifiedTeamsIds;
        }

        switch ($recipient) {
            case 'specifiedUsers':
                return $userIdList;

            case 'specifiedTeams':
                return $this->getHelper()->getUserIdsByTeamIds($teamIdList);

            case 'currentUser':
                return [$this->getUser()->get('id')];

            case 'teamUsers':
                return $this->getHelper()->getUserIdsByTeamIds($entity->getLinkMultipleIdList('teams'));

            case 'followers':
                return $this->getHelper()->getFollowerUserIds($entity);

            default:
                return $this->getRecipientUserIdList($recipient);
        }
    }

    /**
     * @return string[]
     */
    protected function getRecipientUserIdList(string $recipient): array
    {
        $link = $recipient;

        $entity = $this->getEntity();

        $e = $entity;

        if (strpos($link, 'link:') === 0) {
            $link = substr($link, 5);
        }

        if (strpos($link, '.')) {
            list ($firstLink, $link) = explode('.', $link);

            if (
                !$entity->hasRelation($firstLink) &&
                (
                    $entity->getRelationType($firstLink) === Entity::BELONGS_TO ||
                    $entity->getRelationType($firstLink) === Entity::BELONGS_TO_PARENT
                )
            ) {
                return [];
            }

            $e = $entity->get($firstLink);

            if (!$e) {
                return [];
            }
        }

        if ($link === 'followers') {
            if (!class_exists("Espo\\Tools\\Stream\\Service")) {
                return $this->getServiceFactory()->create('Stream')->getEntityFolowerIdList($e);
            }

            /** @var StreamService $streamService */
            $streamService = $this->injectableFactory->create("Espo\\Tools\\Stream\\Service");

            return $streamService->getEntityFollowerIdList($e);
        }

        if (
            $e->hasRelation($link) &&
            (
                $e->getRelationType($link) === Entity::HAS_MANY ||
                $e->getRelationType($link) === Entity::MANY_MANY
            ) &&
            $e->hasLinkMultipleField($link) &&
            $e->getRelationParam($link, 'entity')
        ) {
            return $e->getLinkMultipleIdList($link);
        }


        $fieldEntity = Utils::getFieldValue($e, $link, true, $this->getEntityManager());

        if ($fieldEntity instanceof Entity) {
            return [$fieldEntity->getId()];
        }

        return [];
    }
}
