<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Entities\User;
use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\ORM\Entity;
use stdClass;

class CreateNotification extends Base
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        if (empty($actionData->recipient)) {
            return false;
        }

        if (empty($actionData->messageTemplate)) {
            return false;
        }

        $userList = [];

        switch ($actionData->recipient) {
            case 'specifiedUsers':
                if (empty($actionData->userIdList) || !is_array($actionData->userIdList)) {
                    return false;
                }

                $userIds = $actionData->userIdList;

                break;

            case 'specifiedTeams':
                $userIds = $this->getHelper()->getUserIdsByTeamIds($actionData->specifiedTeamsIds);

                break;

            case 'teamUsers':
                $entity->loadLinkMultipleField('teams');
                $userIds = $this->getHelper()->getUserIdsByTeamIds($entity->get('teamsIds'));

                break;

            case 'followers':
                $userIds = $this->getHelper()->getFollowerUserIds($entity);

                break;

            case 'followersExcludingAssignedUser':
                $userIds = $this->getHelper()->getFollowerUserIdsExcludingAssignedUser($entity);
                break;

            case 'currentUser':
                $userIds = [$this->getUser()->getId()];

                break;

            default:
                $userIds = $this->getRecipientUserIdList($actionData->recipient);

                break;
        }

        if (isset($userIds)) {
            foreach ($userIds as $userId) {
                $user = $this->getEntityManager()->getEntityById(User::ENTITY_TYPE, $userId);

                $userList[] = $user;
            }
        }

        $message = $actionData->messageTemplate ?? '';

        $variables = $this->getVariables() ?? (object) [];

        if ($variables) {
            foreach (get_object_vars($variables) as $key => $value) {
                if (is_string($value) || is_int($value) || is_float($value)) {
                    if (is_int($value) || is_float($value)) {
                        $value = strval($value);
                    } else {
                        if (!$value) {
                            continue;
                        }
                    }

                    $message = str_replace('{$$' . $key . '}', $value, $message);
                }
            }
        }

        foreach ($userList as $user) {
            $notification = $this->getEntityManager()->getEntity('Notification');
            $notification->set([
                'type' => 'message',
                'data' => [
                    'entityId' => $entity->get('id'),
                    'entityType' => $entity->getEntityType(),
                    'entityName' => $entity->get('name'),
                    'userId' => $this->getUser()->get('id'),
                    'userName' => $this->getUser()->get('name')
                ],
                'userId' => $user->get('id'),
                'message' => $message,
                'relatedId' => $entity->get('id'),
                'relatedType' => $entity->getEntityType(),
            ]);

            $this->getEntityManager()->saveEntity($notification);
        }

        return true;
    }

    /**
     * Get email address defined in workflowю
     *
     * @param string $type
     * @return array
     */
    protected function getRecipientUserIdList($recipient)
    {
        $data = $this->getActionData();

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
                    $entity->getRelationType($firstLink) === 'belongsTo' ||
                    $entity->getRelationType($firstLink) === 'belongsToParent'
                )
            ) {
                return [];
            }

            $e = $entity->get($firstLink);
            if (!$e) return [];
        }

        if ($link === 'followers') {
            if (!class_exists("Espo\\Tools\\Stream\\Service")) {
                return $this->getServiceFactory()->create('Stream')->getEntityFolowerIdList($e);
            }

            /** @var \Espo\Tools\Stream\Service $streamService */
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
            $idList = $e->getLinkMultipleIdList($link);

            if (!empty($idList)) {
                return $idList;
            }
        }

        $fieldEntity = Utils::getFieldValue($e, $link, true, $this->getEntityManager());

        if ($fieldEntity instanceof Entity) {
            return [$fieldEntity->getId()];
        }

        return [];
    }
}
