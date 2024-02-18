<?php

namespace Espo\Custom\Services;

use Espo\Core\Authentication\Logins\Hmac;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Util;
use Espo\Custom\Entities\User as UserEntity;

use Espo\ORM\Entity;
use Espo\Services\User as BaseService;

class User extends  BaseService

{
    private $allowedUserTypeList = [
        UserEntity::TYPE_REGULAR,
        UserEntity::TYPE_ADMIN,
        UserEntity::TYPE_PORTAL,
        UserEntity::TYPE_API,
        UserEntity::TYPE_Manger,
    ];

    protected function beforeUpdateEntity(Entity $entity, $data)
    {
        $userLimit = $this->config->get('userLimit');

        if (
            $userLimit &&
            !$this->user->isSuperAdmin() &&
            (
                (
                    $entity->isActive() &&
                    $entity->isAttributeChanged('isActive') &&
                    !$entity->isPortal() &&
                    !$entity->isApi()
                ) ||
                (
                    !$entity->isPortal() &&
                    !$entity->isApi() &&
                    $entity->isAttributeChanged('type') &&
                    (
                        $entity->isRegular() ||
                        $entity->isAdmin()
                    ) &&
                    (
                        $entity->getFetched('type') == \Espo\Entities\User::TYPE_PORTAL ||
                        $entity->getFetched('type') == UserEntity::TYPE_API
                    )
                )
            )
        ) {
            $userCount = $this->getInternalUserCount();

            if ($userCount >= $userLimit) {
                throw new Forbidden("User limit {$userLimit} is reached.");
            }
        }

        $portalUserLimit = $this->config->get('portalUserLimit');

        if (
            $portalUserLimit &&
            !$this->user->isSuperAdmin() &&
            (
                (
                    $entity->isActive() &&
                    $entity->isAttributeChanged('isActive') &&
                    $entity->isPortal()
                ) ||
                (
                    $entity->isPortal() &&
                    $entity->isAttributeChanged('type')
                )
            )
        ) {
            $portalUserCount = $this->getPortalUserCount();

            if ($portalUserCount >= $portalUserLimit) {
                throw new Forbidden("Portal user limit {$portalUserLimit} is reached.");
            }
        }

        if ($entity->isAttributeChanged('userName')) {
            $this->processUserExistsChecking($entity);
        }

        if (
            $entity->isApi() &&
            $entity->isAttributeChanged('authMethod') &&
            $entity->getAuthMethod() === Hmac::NAME
        ) {
            $secretKey = Util::generateSecretKey();

            $entity->set('secretKey', $secretKey);
        }

        if (
            !$entity->isSuperAdmin() &&
            $entity->isAttributeChanged('type') &&
            $entity->getType() &&
            !in_array($entity->getType(), $this->allowedUserTypeList)
        ) {
            throw new Forbidden("Can't change type.");
        }
    }

    protected function beforeCreateEntity(Entity $entity, $data)
    {
        $userLimit = $this->config->get('userLimit');

        if (
            $userLimit &&
            !$this->user->isSuperAdmin() &&
            !$entity->isPortal() && !$entity->isApi()
        ) {
            $userCount = $this->getInternalUserCount();

            if ($userCount >= $userLimit) {
                throw new Forbidden("User limit {$userLimit} is reached.");
            }
        }

        $portalUserLimit = $this->config->get('portalUserLimit');

        if (
            $portalUserLimit &&
            !$this->user->isSuperAdmin() &&
            $entity->isPortal()
        ) {
            $portalUserCount = $this->getPortalUserCount();

            if ($portalUserCount >= $portalUserLimit) {
                throw new Forbidden("Portal user limit {$portalUserLimit} is reached.");
            }
        }

        $this->processUserExistsChecking($entity);

        if ($entity->isApi()) {
            $entity->set('apiKey', Util::generateApiKey());

            if ($entity->getAuthMethod() === Hmac::NAME) {
                $secretKey = Util::generateSecretKey();

                $entity->set('secretKey', $secretKey);
            }
        }

        if (
            !$entity->isSuperAdmin() &&
            $entity->getType() &&
            !in_array($entity->getType(), $this->allowedUserTypeList)
        ) {
            throw new Forbidden();
        }
    }

    private function processUserExistsChecking(UserEntity $user): void
    {
        $existing = $this->getRepository()
            ->select('id')
            ->where(['userName' => $user->getUserName()])
            ->findOne();

        if ($existing) {
            throw new Conflict('userNameExists');
        }
    }
}
