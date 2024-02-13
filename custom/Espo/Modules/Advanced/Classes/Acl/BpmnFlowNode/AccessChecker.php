<?php

namespace Espo\Modules\Advanced\Classes\Acl\BpmnFlowNode;

use Espo\Core\Acl\AccessChecker as AccessCheckerInterface;
use Espo\Core\Acl\AccessReadChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\AclManager;
use Espo\Entities\User;
use Espo\Modules\Advanced\Entities\BpmnProcess;

class AccessChecker implements AccessCheckerInterface, AccessReadChecker
{
    private AclManager $aclManager;

    public function __construct(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    public function check(User $user, ScopeData $data): bool
    {
        return $this->aclManager->checkScope($user, BpmnProcess::ENTITY_TYPE);
    }

    public function checkRead(User $user, ScopeData $data): bool
    {
        return false;
    }
}
