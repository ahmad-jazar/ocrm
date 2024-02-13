<?php

namespace Espo\Modules\Advanced\Classes\Select\Report\AccessControlFilters;

use Espo\Core\Acl\Table;
use Espo\Core\AclManager;
use Espo\Core\Select\AccessControl\Filter;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class Mandatory implements Filter
{
    private User $user;
    private Metadata $metadata;
    private AclManager $aclManager;

    public function __construct(
        User $user,
        Metadata $metadata,
        AclManager $aclManager
    ) {
        $this->user = $user;
        $this->metadata = $metadata;
        $this->aclManager = $aclManager;
    }

    public function apply(QueryBuilder $queryBuilder): void
    {
        if (!$this->user->getPortalId()) {
            $forbiddenEntityTypeList = [];

            $scopes = $this->metadata->get('scopes', []);

            foreach ($scopes as $scope => $defs) {
                if (empty($defs['entity'])) {
                    continue;
                }

                if ($this->aclManager->checkScope($this->user, $scope, Table::ACTION_READ)) {
                    continue;
                }

                $forbiddenEntityTypeList[] = $scope;
            }

            if ($forbiddenEntityTypeList !== []) {
                $queryBuilder->where(['entityType!=' => $forbiddenEntityTypeList]);
            }

            return;
        }

        if ($this->user->getPortalId()) {
            $queryBuilder
                ->distinct()
                ->leftJoin('portals', 'portalsAccess')
                ->where(['portalsAccess.id' => $this->user->getPortalId()]);
        }
    }
}
