<?php

namespace Espo\Modules\Advanced\Hooks\Common;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\EmailAddress;
use Espo\Entities\LeadCapture;
use Espo\Entities\Note;
use Espo\Entities\Notification;
use Espo\Entities\PhoneNumber;
use Espo\Modules\Advanced\Core\SignalManager;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\ORM\Entity;

class Signal
{
    public static $order = 100;

    private $ignoreEntityTypeList = [
        Notification::ENTITY_TYPE,
        EmailAddress::ENTITY_TYPE,
        PhoneNumber::ENTITY_TYPE,
    ];

    private $ignoreRegularEntityTypeList = [
        Note::ENTITY_TYPE,
    ];

    private Metadata $metadata;
    private Config $config;
    private SignalManager $signalManager;

    public function __construct(
        Metadata $metadata,
        Config $config,
        SignalManager $signalManager
    ) {
        $this->metadata = $metadata;
        $this->config = $config;
        $this->signalManager = $signalManager;
    }

    public function afterSave(Entity $entity, array $options): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        if ($this->config->get('signalCrudHooksDisabled')) {
            return;
        }

        if (in_array($entity->getEntityType(), $this->ignoreEntityTypeList)) {
            return;
        }

        $ignoreRegular = in_array($entity->getEntityType(), $this->ignoreRegularEntityTypeList);

        $signalManager = $this->signalManager;

        if ($entity->isNew()) {
            $signalManager->trigger('@create', $entity, $options);

            if (!$ignoreRegular) {
                $signalManager->trigger(['create', $entity->getEntityType()]);
            }

            if (
                $entity->getEntityType() === Note::ENTITY_TYPE &&
                $entity->get('type') === Note::TYPE_POST
            ) {
                $parentId = $entity->get('parentId');
                $parentType = $entity->get('parentType');

                if ($parentType && $parentId) {
                    $signalManager->trigger(['streamPost', $parentType, $parentId]);
                }
            }
        }
        else {
            $signalManager->trigger('@update', $entity, $options);

            if (!$ignoreRegular) {
                $signalManager->trigger(['update', $entity->getEntityType(), $entity->getId()]);
            }
        }

        if ($ignoreRegular) {
            return;
        }

        foreach ($entity->getRelationList() as $relation) {
            $type = $entity->getRelationType($relation);

            if ($type === Entity::BELONGS_TO_PARENT && $entity->isNew()) {
                $parentId = $entity->get($relation . 'Id');
                $parentType = $entity->get($relation . 'Type');

                if (!$parentType || !$parentId) {
                    continue;
                }

                if (!$this->metadata->get(['scopes', $parentType, 'object'])) {
                    continue;
                }

                $signalManager->trigger(['createChild', $parentType, $parentId, $entity->getEntityType()]);

                continue;
            }

            if ($type === 'belongsTo') {
                $idAttribute = $relation . 'Id';
                $idValue = $entity->get($idAttribute);

                //$relate = true;
                //$unrelate = false;

                //$prevIdValue = null;

                if (!$entity->isNew()) {
                    if (!$entity->isAttributeChanged($idAttribute)) {
                        continue;
                    }

                    /*if (!$idValue) {
                        $relate = false;
                    }*/

                    //$prevIdValue = $entity->getFetched($idAttribute);

                    /*if ($prevIdValue) {
                        $unrelate = true;
                    }*/
                }
                else {
                    if (!$idValue) {
                        continue;
                    }
                }

                $foreignEntityType = $entity->getRelationParam($relation, 'entity');
                $foreign = $entity->getRelationParam($relation, 'foreign');

                if (!$foreignEntityType) {
                    continue;
                }

                if (!$foreign) {
                    continue;
                }

                if (in_array($foreignEntityType, ['User', 'Team'])) {
                    continue;
                }

                if (!$this->metadata->get(['scopes', $foreignEntityType, 'object'])) {
                    continue;
                }

                if ($entity->isNew()) {
                    $signalManager->trigger(['createRelated', $foreignEntityType, $idValue, $foreign]);
                }

                //continue; // skip

                /*if ($relate) {
                    if (!$entity->isNew()) {
                        $signalManager->trigger(['relate', $foreignEntityType, $idValue, $foreign, $entity->getId()]);
                        $signalManager->trigger(['relate', $foreignEntityType, $idValue, $foreign]);
                    }
                }

                if ($unrelate) {
                    $signalManager->trigger(['unrelate', $foreignEntityType, $prevIdValue, $foreign, $entity->getId()]);
                    $signalManager->trigger(['unrelate', $foreignEntityType, $prevIdValue, $foreign]);
                }

                continue;*/
            }
        }
    }

    public function afterRemove(Entity $entity, array $options): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        if ($this->config->get('signalCrudHooksDisabled')) {
            return;
        }

        if (in_array($entity->getEntityType(), $this->ignoreEntityTypeList)) {
            return;
        }

        $ignoreRegular = in_array($entity->getEntityType(), $this->ignoreRegularEntityTypeList);

        $signalManager = $this->signalManager;

        $signalManager->trigger('@delete', $entity, $options);

        if (!$ignoreRegular) {
            $signalManager->trigger(['delete', $entity->getEntityType(), $entity->getId()]);
        }
    }

    public function afterRelate(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        if ($this->config->get('signalCrudHooksDisabled')) {
            return;
        }

        if (in_array($entity->getEntityType(), $this->ignoreEntityTypeList)) {
            return;
        }

        $ignoreRegular = in_array($entity->getEntityType(), $this->ignoreRegularEntityTypeList);

        if ($entity->isNew()) {
            return;
        }

        $signalManager = $this->signalManager;

        $foreign = $hookData['foreignEntity'] ?? null;
        $link = $hookData['relationName'] ?? null;

        if (!$foreign || !$link) {
            return;
        }

        $foreignId = $foreign->getId();

        $relationType = $entity->getRelationParam($link, 'type');

        if ($relationType !== Entity::MANY_MANY) {
            $ignoreRegular = true;
        }

        $signalManager->trigger(['@relate', $link, $foreignId], $entity, $options);
        $signalManager->trigger(['@relate', $link], $entity, $options);

        if (!$ignoreRegular) {
            $signalManager->trigger(['relate', $entity->getEntityType(), $entity->getId(), $link, $foreignId]);
            $signalManager->trigger(['relate', $entity->getEntityType(), $entity->getId(), $link]);
        }

        $foreignLink = $entity->getRelationParam($link, 'foreign');

        if ($foreignLink) {
            $signalManager->trigger(['@relate', $foreignLink, $entity->getId()], $foreign);
            $signalManager->trigger(['@relate', $foreignLink], $foreign);

            if (!$ignoreRegular) {
                $signalManager
                    ->trigger(['relate', $foreign->getEntityType(), $foreign->getId(), $foreignLink, $entity->getId()]);
                $signalManager
                    ->trigger(['relate', $foreign->getEntityType(), $foreign->getId(), $foreignLink]);
            }
        }
    }

    public function afterUnrelate(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        if ($this->config->get('signalCrudHooksDisabled')) {
            return;
        }

        if (in_array($entity->getEntityType(), $this->ignoreEntityTypeList)) {
            return;
        }

        $ignoreRegular = in_array($entity->getEntityType(), $this->ignoreRegularEntityTypeList);

        if ($entity->isNew()) {
            return;
        }

        $signalManager = $this->signalManager;

        $foreign = $hookData['foreignEntity'] ?? null;
        $link = $hookData['relationName'] ?? null;

        if (!$foreign || !$link) {
            return;
        }

        $foreignId = $foreign->getId();

        $relationType = $entity->getRelationParam($link, 'type');

        if ($relationType !== Entity::MANY_MANY) {
            $ignoreRegular = true;
        }

        $signalManager->trigger(['@unrelate', $link, $foreignId], $entity, $options);
        $signalManager->trigger(['@unrelate', $link], $entity, $options);

        if (!$ignoreRegular) {
            $signalManager->trigger(['unrelate', $entity->getEntityType(), $entity->getId(), $link, $foreignId]);
            $signalManager->trigger(['unrelate', $entity->getEntityType(), $entity->getId(), $link]);
        }

        $foreignLink = $entity->getRelationParam($link, 'foreign');

        if (!$foreignLink) {
            return;
        }

        $signalManager->trigger(['@unrelate', $foreignLink, $entity->getId()], $foreign);
        $signalManager->trigger(['@unrelate', $foreignLink], $foreign);

        $signalManager
            ->trigger(['unrelate', $foreign->getEntityType(), $foreign->getId(), $foreignLink, $entity->getId()]);
        $signalManager
            ->trigger(['unrelate', $foreign->getEntityType(), $foreign->getId(), $foreignLink]);
    }

    public function afterMassRelate(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        if ($this->config->get('signalCrudHooksDisabled')) {
            return;
        }

        if (in_array($entity->getEntityType(), $this->ignoreEntityTypeList)) {
            return;
        }

        $link = $hookData['relationName'] ?? null;

        if (!$link) {
            return;
        }

        $signalManager = $this->signalManager;

        $signalManager->trigger(['@relate', $link], $entity, $options);
        $signalManager->trigger(['relate', $entity->getEntityType(), $entity->getId(), $link]);
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $hookData
     * @noinspection PhpUnused
     */
    public function afterLeadCapture(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        if ($entity->getEntityType() === LeadCapture::ENTITY_TYPE) {
            return;
        }

        $id = $hookData['leadCaptureId'];

        $signalManager = $this->signalManager;

        $signalManager->trigger(['@leadCapture', $id], $entity);
        $signalManager->trigger(['@leadCapture'], $entity);

        $signalManager->trigger(['leadCapture', $entity->getEntityType(), $entity->getId(), $id]);
        $signalManager->trigger(['leadCapture', $entity->getEntityType(), $entity->getId()]);
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $hookData
     * @noinspection PhpUnused
     */
    public function afterConfirmation(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        $eventEntityType = $entity->getEntityType();
        $eventId = $entity->getId();
        $status = $hookData['status'];
        $entityType = $hookData['inviteeType'];
        $id = $hookData['inviteeId'];

        $signalManager = $this->signalManager;

        if ($status === Meeting::ATTENDEE_STATUS_ACCEPTED) {
            $signalManager->trigger(['@eventAccepted', $entityType], $entity);

            $signalManager->trigger(['eventAccepted', $entityType, $id, $eventEntityType, $eventId]);
            $signalManager->trigger(['eventAccepted', $entityType, $id, $eventEntityType]);
        }

        if ($status === Meeting::ATTENDEE_STATUS_TENTATIVE) {
            $signalManager->trigger(['@eventTentative', $entityType], $entity);

            $signalManager->trigger(['eventTentative', $entityType, $id, $eventEntityType, $eventId]);
            $signalManager->trigger(['eventTentative', $entityType, $id, $eventEntityType]);
        }

        if ($status === Meeting::ATTENDEE_STATUS_DECLINED) {
            $signalManager->trigger(['@eventDeclined', $entityType], $entity);

            $signalManager->trigger(['eventDeclined', $entityType, $id, $eventEntityType, $eventId]);
            $signalManager->trigger(['eventDeclined', $entityType, $id, $eventEntityType]);
        }

        if (
            $status === Meeting::ATTENDEE_STATUS_ACCEPTED ||
            $status === Meeting::ATTENDEE_STATUS_TENTATIVE
        ) {
            $signalManager->trigger(['@eventAcceptedTentative', $entityType], $entity);

            $signalManager->trigger(['eventAcceptedTentative', $entityType, $id, $eventEntityType, $eventId]);
            $signalManager->trigger(['eventAcceptedTentative', $entityType, $id, $eventEntityType]);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $hookData
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterOptOut(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        $signalManager = $this->signalManager;

        $signalManager->trigger(implode('.', ['@optOut']), $entity);
        $signalManager->trigger(implode('.', ['optOut', $entity->getEntityType(), $entity->getId()]));
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $hookData
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterCancelOptOut(Entity $entity, array $options, array $hookData): void
    {
        if (
            !empty($options['skipWorkflow']) ||
            !empty($options['skipSignal']) ||
            !empty($options['silent'])
        ) {
            return;
        }

        $signalManager = $this->signalManager;

        $signalManager->trigger(implode('.', ['@cancelOptOut']), $entity);
        $signalManager->trigger(implode('.', ['cancelOptOut', $entity->getEntityType(), $entity->getId()]));
    }
}
