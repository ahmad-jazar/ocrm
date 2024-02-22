<?php

namespace Espo\Custom\Repositories;

use Espo\ORM\Entity;
use Espo\Repositories\User as UserRepository;


class User extends UserRepository
{
    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        if ($entity->isAttributeChanged('password') && $entity->get('changePasswordFlag')) {
            $entity->set('changePasswordFlag', false);
        }
    }
}
