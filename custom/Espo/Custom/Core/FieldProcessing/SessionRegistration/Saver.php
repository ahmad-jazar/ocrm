<?php

namespace Espo\Custom\Core\FieldProcessing\SessionRegistration;

use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Entity;

class Saver implements SaverInterface
{
    public function __construct(
        private EntityManager $entityManager
    )
    {
    }

    public function process(Entity $entity, Params $params): void
    {

        $contactId = $entity->get('contactId');

        if (!$contactId) {
            return;
        }

        $contact = $this->entityManager->getEntityById(Contact::ENTITY_TYPE, $contactId);

        if ($contact && !$entity->isNew()) {
            $contact->set('salutationName', $entity->get('salutationName'));
            $contact->set('firstName', $entity->get('firstName'));
            $contact->set('lastName', $entity->get('lastName'));
            $contact->set('phoneNumber', $entity->get('phoneNumber'));
//            $contact->set('emailAddressData', $entity->get('emailAddressData'));
            $contact->set('emailAddress', $entity->get('emailAddress'));
            $this->entityManager->saveEntity($contact);
        }
    }
}
