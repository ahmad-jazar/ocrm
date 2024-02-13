<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Job\QueueName;
use Espo\Entities\Email;
use Espo\Entities\Job;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\Modules\Advanced\Tools\Workflow\Jobs\SendEmail as SendEmailJob;
use Espo\Modules\Advanced\Tools\Workflow\SendEmailService;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Entity;
use Espo\Repositories\Email as EmailRepository;
use Espo\Tools\Stream\Service as StreamService;

use RuntimeException;
use stdClass;

class SendEmail extends Base
{
    protected function run(Entity $entity, stdClass $actionData): bool
    {
        $jobData = [
            'workflowId' => $this->getWorkflowId(),
            'entityId' => $this->getEntity()->getId(),
            'entityType' => $this->getEntity()->getEntityType(),
            'from' => $this->getEmailAddressData('from'),
            'to' => $this->getEmailAddressData('to'),
            'replyTo' => $this->getEmailAddressData('replyTo'),
            'emailTemplateId' => $actionData->emailTemplateId ?? null,
            'doNotStore' => $actionData->doNotStore ?? false,
            'optOutLink' => $actionData->optOutLink ?? false,
        ];

        if ($this->bpmnProcess) {
            $jobData['processId'] = $this->bpmnProcess->getId();
        }

        if (is_null($jobData['to'])) {
            return true;
        }

        if (!empty($actionData->processImmediately)) {
            $storeSentEmailData = !!$this->createdEntitiesData && !$jobData['doNotStore'];

            if ($storeSentEmailData) {
                $jobData['returnEmailId'] = true;
            }

            if ($this->hasVariables()) {
                $jobData['variables'] = $this->getVariables();
            }

            $service = $this->injectableFactory->create(SendEmailService::class);

            $jobData = json_decode(json_encode($jobData));

            $emailId = $service->send($jobData);

            if (
                $storeSentEmailData &&
                $emailId &&
                isset($actionData->elementId)
            ) {
                $alias = $actionData->elementId;

                $this->createdEntitiesData->$alias = (object) [
                    'entityType' => Email::ENTITY_TYPE,
                    'entityId' => $emailId,
                ];
            }

            return true;
        }

        $job = $this->getEntityManager()->getNewEntity(Job::ENTITY_TYPE);

        $job->set([
            'name' => SendEmailJob::class,
            'className' => SendEmailJob::class,
            'data' => $jobData,
            'executeTime' => $this->getExecuteTime($actionData),
            'queue' => QueueName::E0,
        ]);

        $this->getEntityManager()->saveEntity($job);

        return true;
    }

    /**
     * @param string $type
     * @return ?array{
     *     email?: string,
     *     type: string,
     *     entityType?: string,
     *     entityId?: string,
     * }
     */
    private function getEmailAddressData(string $type): ?array
    {
        $data = $this->getActionData();

        $fieldValue = $data->$type ?? null;

        switch ($fieldValue) {
            case 'specifiedEmailAddress':
                $address = $data->{$type . 'Email'};

                if ($address && strpos($address, '{$$') !== false && $this->hasVariables()) {
                    $variables = $this->getVariables() ?? (object) [];

                    foreach (get_object_vars($variables) as $key => $v) {
                        if ($v && is_string($v)) {
                            $address = str_replace('{$$'.$key.'}', $v, $address);
                        }
                    }
                }

                return [
                    'email' => $address,
                    'type' => $fieldValue,
                ];

            case 'processAssignedUser':
                if (!$this->bpmnProcess) {
                    return null;
                }

                if (!$this->bpmnProcess->get('assignedUserId')) {
                    return null;
                }

                return [
                    'entityType' => User::ENTITY_TYPE,
                    'entityId' => $this->bpmnProcess->get('assignedUserId'),
                    'type' => $fieldValue,
                ];

            case 'targetEntity':
            case 'teamUsers':
            case 'followers':
            case 'followersExcludingAssignedUser':
                $entity = $this->getEntity();

                return [
                    'entityType' => $entity->getEntityType(),
                    'entityId' => $entity->getId(),
                    'type' => $fieldValue,
                ];

            case 'specifiedTeams':
            case 'specifiedUsers':
            case 'specifiedContacts':
                $specifiedEntityType = null;

                if ($fieldValue === 'specifiedTeams') {
                    $specifiedEntityType = Team::ENTITY_TYPE;
                }

                if ($fieldValue === 'specifiedUsers') {
                    $specifiedEntityType = User::ENTITY_TYPE;
                }

                if ($fieldValue === 'specifiedContacts') {
                    $specifiedEntityType = Contact::ENTITY_TYPE;
                }

                return [
                    'type' => $fieldValue,
                    'entityIds' => $data->{$type . 'SpecifiedEntityIds'},
                    'entityType' => $specifiedEntityType
                ];

            case 'currentUser':
                return [
                    'entityType' => User::ENTITY_TYPE,
                    'entityId' => $this->getUser()->getId(),
                    'type' => $fieldValue,
                ];

            case 'system':
                return [
                    'type' => $fieldValue,
                ];

            case 'fromOrReplyTo':
                $entity = $this->getEntity();
                $emailAddress = null;

                /** @var EmailRepository $repo */
                $repo = $this->getEntityManager()->getRepository(Email::ENTITY_TYPE);

                if (!$entity instanceof Email) {
                    throw new RuntimeException("Workflow send-email fromOrReplyTo did not receive email.");
                }

                $repo->loadFromField($entity);

                if ($entity->has('replyToString') && $entity->get('replyToString')) {
                    $replyTo = $entity->get('replyToString');

                    $arr = explode(';', $replyTo);
                    $emailAddress = $arr[0];

                    preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $emailAddress, $matches);

                    if (empty($matches[0])) {
                        return null;
                    }

                    $emailAddress = $matches[0][0];
                }
                else if ($entity->has('from') && $entity->get('from')) {
                    $emailAddress = $entity->get('from');
                }

                if (!$emailAddress) {
                    return null;
                }

                return [
                    'type' => $fieldValue,
                    'email' => $emailAddress,
                ];

            default:
                if (strpos($fieldValue, 'link:') === 0) {
                    $fieldValue = substr($fieldValue, 5);
                }

                $link = $fieldValue;

                if (empty($fieldValue)) {
                    return null;
                }

                $entity = $this->getEntity();
                $targetEntity = $entity;

                if (strpos($link, '.')) {
                    [$firstLink, $link] = explode('.', $link);

                    if (
                        !$entity->hasRelation($firstLink) &&
                        (
                            $entity->getRelationType($firstLink) === Entity::BELONGS_TO ||
                            $entity->getRelationType($firstLink) === Entity::BELONGS_TO_PARENT
                        )
                    ) {
                        return null;
                    }

                    $targetEntity = $this->entityManager
                        ->getRDBRepository($entity->getEntityType())
                        ->getRelation($entity, $firstLink)
                        ->findOne();

                    if (!$targetEntity) {
                        return null;
                    }
                }

                if ($link === 'followers') {
                    if (!class_exists("Espo\\Tools\\Stream\\Service")) {
                        $idList = $this->getServiceFactory()
                            ->create('Stream')
                            ->getEntityFolowerIdList($targetEntity);
                    }
                    else {
                        /** @var StreamService $streamService */
                        $streamService = $this->injectableFactory->create("Espo\\Tools\\Stream\\Service");

                        $idList = $streamService->getEntityFollowerIdList($targetEntity);
                    }

                    return [
                        'entityType' => User::ENTITY_TYPE,
                        'entityIds' => $idList,
                        'type' => 'link:' . $fieldValue,
                    ];
                }

                if (
                    $targetEntity->hasRelation($link) &&
                    (
                        $targetEntity->getRelationType($link) === Entity::HAS_MANY ||
                        $targetEntity->getRelationType($link) === Entity::MANY_MANY
                    ) &&
                    $targetEntity->hasLinkMultipleField($link) &&
                    $targetEntity->getRelationParam($link, 'entity')
                ) {
                    $idList = $targetEntity->getLinkMultipleIdList($link);

                    if (empty($idList)) {
                        return null;
                    }

                    return [
                        'entityType' => $targetEntity->getRelationParam($link, 'entity'),
                        'entityIds' => $idList,
                        'type' => 'link:' . $fieldValue
                    ];
                }

                $fieldEntity = Utils::getFieldValue($targetEntity, $link, true, $this->getEntityManager());

                if (!$fieldEntity instanceof Entity) {
                    return null;
                }

                if (
                    $fieldEntity->hasAttribute('emailAddress') &&
                    (
                        $fieldEntity->getAttributeType('emailAddress') === 'email' ||
                        $fieldEntity->getAttributeParam('emailAddress', 'fieldType') === 'email'
                    )
                ) {
                    return [
                        'entityType' => $fieldEntity->getEntityType(),
                        'entityId' => $fieldEntity->getId(),
                        'type' => 'link:' . $fieldValue,
                    ];
                }

                return null;
        }
    }
}
