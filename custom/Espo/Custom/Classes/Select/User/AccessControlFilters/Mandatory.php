<?php


namespace Espo\Custom\Classes\Select\User\AccessControlFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\AccessControl\Filter;
use Espo\Entities\User;

class Mandatory implements Filter
{
    public function __construct(
        private User $user,
    )
    {
    }

    public function apply(SelectBuilder $queryBuilder): void
    {
        if ($this->user->isManger()) {
            $queryBuilder->where([
                'type!=' => User::TYPE_ADMIN,
            ]);
        }
    }
}
