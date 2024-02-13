<?php

namespace Espo\Modules\Advanced\Core\Workflow\Formula\Functions\BpmGroup\CreatedEntityGroup;

use Espo\Core\Di\EntityManagerAware;
use Espo\Core\Di\EntityManagerSetter;
use Espo\Core\Di\InjectableFactoryAware;
use Espo\Core\Di\InjectableFactorySetter;
use Espo\Core\Exceptions\Error;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\AttributeFetcher;
use Espo\Core\Formula\Functions\BaseFunction;

use RuntimeException;

class AttributeType extends BaseFunction implements EntityManagerAware, InjectableFactoryAware
{
    use EntityManagerSetter;
    use InjectableFactorySetter;

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 2) {
            throw new RuntimeException("Formula bpm\createdEntity\\attribute: Too few arguments.");
        }

        $aliasId = $args[0];
        $attribute = $args[1];

        if (!is_string($aliasId) || !is_string($attribute)) {
            throw new Error("Formula bpm\createdEntity\\attribute: Bad argument.");
        }

        $variables = $this->getVariables();

        if (!isset($variables->__createdEntitiesData)) {
            throw new Error("Formula bpm\createdEntity\\attribute: Can't be used out of BPM process.");
        }

        if (!isset($variables->__createdEntitiesData->$aliasId)) {
            throw new Error("Formula bpm\createdEntity\\attribute: Unknown aliasId.");
        }

        $entityType = $variables->__createdEntitiesData->$aliasId->entityType;
        $entityId = $variables->__createdEntitiesData->$aliasId->entityId;

        $entity = $this->entityManager->getEntityById($entityType, $entityId);

        $attributeFetched = $this->injectableFactory->create(AttributeFetcher::class);

        return $attributeFetched->fetch($entity, $attribute);
    }
}
