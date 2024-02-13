<?php

namespace Espo\Modules\Advanced\Core\ORM;

use Espo\Core\InjectableFactory;
use Espo\Core\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * Creates entities with custom defs. Need for supporting foreign fields like `linkName.attribute`.
 */
class CustomEntityFactory
{
    /** @var InjectableFactory */
    private $injectableFactory;
    /** @var EntityManager */
    private $entityManager;

    public function __construct(
        InjectableFactory $injectableFactory,
        EntityManager $entityManager
    ) {
        $this->injectableFactory = $injectableFactory;
        $this->entityManager = $entityManager;
    }

    public function create(string $entityType, array $fieldDefs): Entity
    {
        return $this->createImplementation($entityType, $fieldDefs);
    }

    private function createImplementation(string $entityType, array $fieldDefs): Entity
    {
        $seed = $this->entityManager->getEntityFactory()->create($entityType);

        $className = get_class($seed);

        $defs = $this->entityManager->getMetadata()->get($entityType);

        if (array_key_exists('attributes', $defs)) {
            $defs['attributes'] = array_merge($defs['attributes'], $fieldDefs);
        }
        else {
            $defs['fields'] = array_merge($defs['fields'], $fieldDefs);
        }

        return $this->injectableFactory->createWith($className, [
            'entityType' => $entityType,
            'defs' => $defs,
            'valueAccessorFactory' => null,
        ]);
    }
}
