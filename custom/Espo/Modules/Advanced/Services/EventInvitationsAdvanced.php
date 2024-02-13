<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Core\InjectableFactory;
use Espo\Entities\User;
use Espo\Modules\Crm\Business\Event\Invitations;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class EventInvitationsAdvanced
{
    private InjectableFactory $injectableFactory;
    private EntityManager $entityManager;
    private User $user;

    public function __construct(
        InjectableFactory $injectableFactory,
        EntityManager $entityManager,
        User $user
    ) {
        $this->injectableFactory = $injectableFactory;
        $this->entityManager = $entityManager;
        $this->user = $user;
    }

    private function getInvitationManager(): Invitations
    {
        return $this->injectableFactory->create(Invitations::class);
    }

    public function sendInvitationsAction(?string $workflowId, Entity $entity): void
    {
        $invitationManager = $this->getInvitationManager();
        $emailHash = [];
        $sentCount = 0;

        $users = $this->entityManager
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, 'users')
            ->find();

        foreach ($users as $user) {
            if ($user->getId() === $this->user->getId()) {
                if (
                    $entity->getLinkMultipleColumn('users', 'status', $user->getId()) ===
                    'Accepted'
                ) {
                    continue;
                }
            }
            if ($user->get('emailAddress') && !array_key_exists($user->get('emailAddress'), $emailHash)) {
                $invitationManager->sendInvitation($entity, $user, 'users');
                $emailHash[$user->get('emailAddress')] = true;

                $sentCount++;
            }
        }

        $contacts = $this->entityManager
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, 'contacts')
            ->find();

        foreach ($contacts as $contact) {
            if ($contact->get('emailAddress') && !array_key_exists($contact->get('emailAddress'), $emailHash)) {
                $invitationManager->sendInvitation($entity, $contact, 'contacts');
                $emailHash[$contact->get('emailAddress')] = true;

                $sentCount++;
            }
        }

        $leads = $this->entityManager
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, 'leads')
            ->find();

        foreach ($leads as $lead) {
            if ($lead->get('emailAddress') && !array_key_exists($lead->get('emailAddress'), $emailHash)) {
                $invitationManager->sendInvitation($entity, $lead, 'leads');
                $emailHash[$lead->get('emailAddress')] = true;

                $sentCount++;
            }
        }
    }
}
