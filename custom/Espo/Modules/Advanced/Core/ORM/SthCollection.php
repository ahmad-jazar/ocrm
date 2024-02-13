<?php

namespace Espo\Modules\Advanced\Core\ORM;

use Espo\ORM\Collection;
use Espo\ORM\EntityManager;

use PDOStatement;
use PDO;
use IteratorAggregate;

class SthCollection implements IteratorAggregate, Collection
{
    /** @var PDOStatement */
    private $sth;
    /** @var string */
    private $entityType;
    private $fieldDefs;
    private $linkMultipleFieldList;
    private $foreignLinkFieldDataList;
    /** @var CustomEntityFactory */
    private $customEntityFactory;
    /** @var EntityManager */
    private $entityManager;

    public function __construct(
        PDOStatement $sth,
        string $entityType,
        EntityManager $entityManager,
        $fieldDefs,
        $linkMultipleFieldList,
        $foreignLinkFieldDataList,
        CustomEntityFactory $customEntityFactory
    ) {
        $this->sth = $sth;
        $this->entityType = $entityType;
        $this->entityManager = $entityManager;
        $this->fieldDefs = $fieldDefs;
        $this->linkMultipleFieldList = $linkMultipleFieldList;
        $this->foreignLinkFieldDataList = $foreignLinkFieldDataList;
        $this->customEntityFactory = $customEntityFactory;
    }

    public function getIterator()
    {
        return (function () {
            while ($row = $this->sth->fetch(PDO::FETCH_ASSOC)) {

                $rowData = [];

                foreach ($row as $attr => $value) {
                    $attribute = str_replace('.', '_', $attr);

                    $rowData[$attribute] = $value;
                }

                $entity = $this->customEntityFactory->create($this->entityType, $this->fieldDefs);

                $entity->set($rowData);
                $entity->setAsFetched();

                foreach ($this->linkMultipleFieldList as $field) {
                    $entity->loadLinkMultipleField($field);
                }

                foreach ($this->foreignLinkFieldDataList as $item) {
                    $foreignId = $entity->get($item->name . 'Id');

                    if ($foreignId) {
                        $foreignEntity = $this->entityManager
                            ->getRDBRepository($item->entityType)
                            ->where(['id' => $foreignId])
                            ->select(['name'])
                            ->findOne();

                        if ($foreignEntity) {
                            $entity->set($item->name . 'Name', $foreignEntity->get('name'));
                        }
                    }
                }

                yield $entity;
            }
        })();
    }

    public function getValueMapList(): array
    {
        $list = [];

        foreach ($this as $entity) {
            $list[] = $entity->getValueMap();
        }

        return $list;
    }
}
