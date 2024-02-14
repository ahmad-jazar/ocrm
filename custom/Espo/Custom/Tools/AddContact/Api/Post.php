<?php

namespace Espo\Custom\Tools\AddContact\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\ORM\EntityManager;
use Espo\Modules\Crm\Entities\Contact;

class Post implements Action
{

    public function __construct(
        EntityManager $entityManager,
    )
    {
        $this->entityManager = $entityManager;
    }

    public function process(Request $request): Response
    {
        $data = $request->getQueryParams();

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];
        $phone = $data['phone'];
        $seminarId = $data['seminarId'];

        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($seminarId)) {
            return ResponseComposer::json([
                'error' => 'Invalid data'
            ]);
        }

        $contact = $this->entityManager->getRepository(Contact::ENTITY_TYPE)->where([
            'vEmail' => $email,
        ])->findOne();

        if (empty($contact)) {
            $contact = $this->entityManager->createEntity(Contact::ENTITY_TYPE, [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'vEmail' => $email,
                'vMobile' => $phone
            ]);
            $massage = 'Contact created';

        } else {
            $contact->set('firstName', $firstName);
            $contact->set('lastName', $lastName);
            $contact->set('vMobile', $phone);
            $this->entityManager->saveEntity($contact);
            $massage = 'Contact already exists';
        }

        $seminarRegistration = $this->entityManager->getRepository('SeminarRegistration')->where([
            'seminarId' => $seminarId
        ])->findOne();

        if (empty($seminarRegistration)) {
            $this->entityManager->createEntity('SeminarRegistration', [
                'contactId' => $contact->getId(),
                'seminarId' => $seminarId
            ]);
        }

        return ResponseComposer::json([
            $massage
        ]);
    }
}
