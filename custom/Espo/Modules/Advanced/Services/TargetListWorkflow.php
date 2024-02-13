<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Entities\EmailAddress;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Repositories\EmailAddress as EmailAddressRepository;

use stdClass;

class TargetListWorkflow
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function optOut(?string $workflowId, Entity $entity, stdClass $data)
    {
        $targetListId = $data->targetListId ?? null;

        if ($targetListId) {
            $this->entityManager
                ->getRDBRepository($entity->getEntityType())
                ->getRelation($entity, 'targetLists')
                ->updateColumnsById($targetListId, ['optedOut' => true]);

            return;
        }

        $emailAddress = $entity->get('emailAddress');

        if ($emailAddress) {
            /** @var EmailAddressRepository $emailAddressRepository */
            $emailAddressRepository = $this->entityManager->getRepository(EmailAddress::ENTITY_TYPE);

            $addressEntity = $emailAddressRepository->getByAddress($emailAddress);

            if ($addressEntity) {
                $addressEntity->set('optOut', true);
                $this->entityManager->saveEntity($addressEntity);
            }
        }
    }
}
