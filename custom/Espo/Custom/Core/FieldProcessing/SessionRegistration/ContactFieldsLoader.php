<?php

namespace Espo\Custom\Core\FieldProcessing\SessionRegistration;

use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Entity;

class ContactFieldsLoader implements Loader
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

        if ($contact) {
            $entity->set('salutationName', $contact->get('salutationName'));
            $entity->set('firstName', $contact->get('firstName'));
            $entity->set('lastName', $contact->get('lastName'));
            $entity->set('vMobile', $contact->get('phoneNumber'));
            $entity->set('vEmail', $contact->get('emailAddress'));
            $entity->set('contactOwnerName', $contact->get('assignedUserName'));
            $entity->set('contactOwnerId', $contact->get('assignedUserId'));
            $entity->set('contactStatus', $contact->get('contactStatus'));
            $entity->set('contactStatusSecondary', $contact->get('contactStatusSecondary'));
        }
    }
}
