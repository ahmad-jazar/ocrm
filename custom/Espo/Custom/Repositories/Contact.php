<?php

namespace Espo\Custom\Repositories;

use Espo\Core\ApplicationState;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\HookManager;
use Espo\Core\ORM\EntityFactory;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Repositories\Database;
use Espo\Core\Utils\Id\RecordIdGenerator;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\SystemUser;
use Espo\Entities\User as UserEntity;
use Espo\Modules\Crm\Entities\Account as AccountEntity;
use Espo\ORM\Entity;

class Contact extends Database
{
    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        $startDate = '15-02-2024';
        $endDate = '16-02-2024';

        $firstName = $entity->get('firstName');
        $lastName = $entity->get('lastName');
        $email = $entity->get('email');

        $url = 'https://evds2.tcmb.gov.tr/service/evds/series=TP.DK.USD.A-TP.DK.EUR.A&startDate=' . $startDate . '&endDate=' . $endDate . '&type=json&key=LYhCggxPJW';

        $params = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email
        ];

        $query = http_build_query($params);

        $url = $url . '?' . $query;

        $response = json_decode(file_get_contents($url), true);
        $entity->set('description', json_encode($response));


//        if ($entity->isNew()) {
//            $entity->set('description', 'New contact created');
//
//        }else{
//            $entity->set('description', 'Contact updated');
//
//        }
    }
}
