<?php

namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Entities\Attachment;
use Espo\Modules\Advanced\Core\Workflow\Utils;
use Espo\ORM\Entity;
use Espo\Repositories\Attachment as AttachmentRepository;
use stdClass;

abstract class BaseEntity extends Base
{
    /**
     * Get value of a field by $fieldName.
     *
     * @return mixed
     * @throws Error
     */
    protected function getValue(string $fieldName, ?Entity $filledEntity = null)
    {
        $actionData = $this->getActionData();
        $entity = $this->getEntity();

        if (!isset($actionData->fields->$fieldName)) {
            return null;
        }

        $fieldParams = $actionData->fields->$fieldName;

        $fieldValue = null;

        switch ($fieldParams->subjectType) {
            case 'value':
                if (isset($fieldParams->attributes) && is_object($fieldParams->attributes)) {
                    $fieldValue = $fieldParams->attributes;
                }

                break;

            case 'field':
                $filledEntity = $filledEntity ?? $entity;

                $fieldValue = $this->getEntityHelper()
                    ->getFieldValues($entity, $filledEntity, $fieldParams->field, $fieldName);

                if (isset($fieldParams->shiftDays)) {
                    $shiftUnit = 'days';

                    if (!empty($fieldParams->shiftUnit)) {
                        $shiftUnit = $fieldParams->shiftUnit;
                    }

                    if (!in_array($shiftUnit, ['hours', 'minutes', 'days', 'months'])) {
                        $shiftUnit = 'days';
                    }

                    foreach (get_object_vars($fieldValue) as $attribute => $value) {
                        $fieldValue->$attribute = Utils::shiftDays(
                            $fieldParams->shiftDays,
                            $value,
                            $filledEntity->getAttributeType($attribute),
                            $shiftUnit,
                            null,
                            $this->getConfig()->get('timeZone')
                        );
                    }
                }

                break;

            case 'today':
                $filledFieldType = null;

                if (isset($filledEntity)) {
                    $filledFieldType = Utils::getAttributeType($filledEntity, $fieldName);
                }

                $shiftUnit = 'days';

                if (!empty($fieldParams->shiftUnit)) {
                    $shiftUnit = $fieldParams->shiftUnit;
                }

                if (!in_array($shiftUnit, ['hours', 'minutes', 'days', 'months'])) {
                    $shiftUnit = 'days';
                }

                return Utils::shiftDays(
                    $fieldParams->shiftDays,
                    null,
                    $filledFieldType,
                    $shiftUnit,
                    null,
                    $this->getConfig()->get('timeZone')
                );

            default:
                throw new Error(
                    'Workflow['.$this->getWorkflowId().']: Unknown fieldName for a field [' . $fieldName . ']'
                );
        }

        return $fieldValue;
    }

    /**
     * Get data to fill.
     *
     * @param array<string, mixed>|stdClass|null $fields
     * @return array<string, mixed>
     */
    protected function getDataToFill(Entity $entity, $fields): array
    {
        $data = [];

        if (empty($fields)) {
            return $data;
        }

        $metadataFields = $this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'fields']);
        $metadataFieldList = array_keys($metadataFields);

        foreach ($fields as $fieldName => $fieldParams) {
            if ($entity->hasLinkMultipleField($fieldName)) {
                $copiedIdList = [];
                $idListFieldName = $fieldName . 'Ids';

                if (
                    $this->getMetadata()
                        ->get(['entityDefs', $entity->getEntityType(), 'fields', $fieldName, 'type']) ===
                        'attachmentMultiple'
                ) {
                    $attachmentData = $this->getValue($fieldName, $entity);

                    if (!empty($attachmentData) && is_array($attachmentData->$idListFieldName)) {
                        foreach ($attachmentData->$idListFieldName as $attachmentId) {
                            /** @var ?Attachment $attachment */
                            $attachment = $this->getEntityManager()
                                ->getEntityById(Attachment::ENTITY_TYPE, $attachmentId);

                            if ($attachment) {
                                /** @var AttachmentRepository $attachmentRepo */
                                $attachmentRepo = $this->getEntityManager()->getRepository(Attachment::ENTITY_TYPE);

                                $attachment = $attachmentRepo->getCopiedAttachment($attachment);
                                $attachment->set('field', $fieldName);

                                $this->getEntityManager()->saveEntity($attachment);

                                $copiedIdList[] = $attachment->getId();
                            }
                        }
                    }

                    $attachmentData->$idListFieldName = $copiedIdList;
                    $data = array_merge($data, get_object_vars($attachmentData));

                    continue;
                }
            }

            if (
                $entity->hasRelation($fieldName) ||
                $entity->hasAttribute($fieldName) ||
                in_array($fieldName, $metadataFieldList)
            ) {
                $fieldValue = $this->getValue($fieldName, $entity);

                if (is_object($fieldValue)) {
                    $data = array_merge($data, get_object_vars($fieldValue));
                }
                else {
                    $data[$fieldName] = $fieldValue;
                }
            }
        }

        return $data;
    }
}
