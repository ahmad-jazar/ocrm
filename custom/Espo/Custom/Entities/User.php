<?php

namespace Espo\Custom\Entities;

use Espo\Entities\User as BaseUser;

use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Modules\Crm\Entities\Contact;
use RuntimeException;

class User extends BaseUser
{
    public const TYPE_Manger = 'manger';
    public function isAdmin(): bool
    {
        return $this->getType() === self::TYPE_ADMIN ||
            $this->isManger() ||
            $this->isSystem() ||
            $this->isSuperAdmin();
    }
    public function isManger(): bool
    {
        return $this->getType() === self::TYPE_Manger;
    }

}
