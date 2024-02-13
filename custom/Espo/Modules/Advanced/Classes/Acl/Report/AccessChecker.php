<?php

namespace Espo\Modules\Advanced\Classes\Acl\Report;

use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Core\AclManager;
use Espo\Entities\User;
use Espo\Modules\Advanced\Entities\Report;
use Espo\ORM\Entity;

/**
 * @implements AccessEntityCREDChecker<Report>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    use DefaultAccessCheckerDependency;

    private AclManager $aclManager;

    public function __construct(
        DefaultAccessChecker $defaultAccessChecker,
        AclManager $aclManager
    ) {
        $this->defaultAccessChecker = $defaultAccessChecker;
        $this->aclManager = $aclManager;
    }

    /**
     * @param Report $entity
     */
    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        if (
            $entity->getTargetEntityType() &&
            !$this->aclManager->checkScope($user, $entity->getTargetEntityType())
        ) {
            return false;
        }

        return $this->defaultAccessChecker->checkEntityRead($user, $entity, $data);
    }
}
